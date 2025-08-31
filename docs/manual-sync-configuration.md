# Manual Cloud Sync Configuration

## Overview
Sistem sinkronisasi dengan cloud storage (Google Drive via rclone) dirancang untuk dijalankan **secara manual** oleh user, bukan otomatis. Ini memberikan kontrol penuh kepada user kapan data mereka di-sync ke cloud.

## Manual Sync Methods

### 1. Via UI Button
User dapat melakukan sync melalui tombol di File Explorer interface:

```blade
<!-- Di File Explorer Component -->
<button @click="syncWithCloud" 
        :disabled="syncing" 
        class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded text-sm disabled:opacity-50">
    <svg class="w-4 h-4 inline mr-1" :class="{'animate-spin': syncing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
    </svg>
    <span x-text="syncing ? 'Syncing...' : 'Manual Sync'"></span>
</button>
```

JavaScript handler dengan konfirmasi:
```javascript
async syncWithCloud() {
    // Konfirmasi dari user
    if (!confirm('Apakah Anda ingin melakukan sinkronisasi ke cloud storage sekarang?')) {
        return;
    }
    
    this.syncing = true;
    try {
        const response = await fetch(`/api/projects/${this.projectId}/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            this.showNotification('Sinkronisasi dimulai. Proses ini mungkin memakan waktu beberapa menit.', 'info');
            // Check status setelah 5 detik
            setTimeout(() => this.checkSyncStatus(), 5000);
        } else {
            this.showNotification('Gagal memulai sinkronisasi', 'error');
        }
    } catch (error) {
        console.error('Error starting sync:', error);
        this.showNotification('Error saat memulai sinkronisasi', 'error');
    } finally {
        setTimeout(() => {
            this.syncing = false;
        }, 2000);
    }
}
```

### 2. Via Artisan Command

#### Sync Single Project
```bash
# Sync project dengan ID 34
php artisan project:sync 34

# Dry run untuk preview tanpa sync
php artisan project:sync 34 --dry-run
```

#### Check Sync Status
```bash
# Check status sync untuk project
php artisan project:sync-status 34

# Detailed view dengan status per file
php artisan project:sync-status 34 --detailed
```

### 3. Via API Endpoint

#### Trigger Manual Sync
```http
POST /api/projects/{project}/sync
Authorization: Bearer {token}
```

Response:
```json
{
    "success": true,
    "message": "Sync started",
    "job_id": "uuid-here"
}
```

#### Check Sync Status
```http
GET /api/projects/{project}/sync-status
Authorization: Bearer {token}
```

Response:
```json
{
    "stats": {
        "total": 25,
        "synced": 20,
        "pending": 3,
        "failed": 2,
        "percentage": 80
    },
    "last_sync": "2025-01-29T10:30:00Z",
    "last_sync_status": "completed"
}
```

## Artisan Commands Implementation

### SyncProjectToCloud Command
```php
<?php
// app/Console/Commands/SyncProjectToCloud.php

namespace App\Console\Commands;

use App\Models\Project;
use App\Jobs\SyncProjectJob;
use Illuminate\Console\Command;

class SyncProjectToCloud extends Command
{
    protected $signature = 'project:sync 
                            {project : Project ID to sync}
                            {--dry-run : Show what would be synced without actually syncing}
                            {--force : Force sync even if recently synced}';
    
    protected $description = 'Manually sync project documents to cloud storage';
    
    public function handle()
    {
        $projectId = $this->argument('project');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $project = Project::with('documents')->find($projectId);
        
        if (!$project) {
            $this->error("Project with ID {$projectId} not found.");
            return 1;
        }
        
        // Check last sync time (prevent too frequent syncs unless forced)
        if (!$force && !$dryRun) {
            $lastSync = $project->syncLogs()
                ->where('action', 'upload')
                ->where('status', 'success')
                ->latest()
                ->first();
                
            if ($lastSync && $lastSync->created_at->diffInMinutes(now()) < 5) {
                $this->warn("Project was synced {$lastSync->created_at->diffForHumans()}.");
                if (!$this->confirm('Do you want to sync again?')) {
                    return 0;
                }
            }
        }
        
        $this->info("===========================================");
        $this->info("Project: {$project->name} (ID: {$project->id})");
        $this->info("Code: {$project->code}");
        $this->info("Total Documents: {$project->documents->count()}");
        $this->info("===========================================");
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No actual sync will be performed");
            $this->newLine();
            $this->showSyncPreview($project);
            return 0;
        }
        
        // Ask for confirmation
        if (!$this->confirm('Do you want to start the sync process?')) {
            $this->info('Sync cancelled.');
            return 0;
        }
        
        // Dispatch sync job
        $job = new SyncProjectJob($project);
        dispatch($job);
        
        $this->info("✅ Sync job queued successfully!");
        $this->info("Check sync status with: php artisan project:sync-status {$projectId}");
        
        // Optionally wait and show progress
        if ($this->confirm('Do you want to wait and monitor the sync progress?')) {
            $this->monitorSync($project);
        }
        
        return 0;
    }
    
    private function showSyncPreview(Project $project)
    {
        $documents = $project->documents;
        
        $toSync = $documents->whereIn('sync_status', ['pending', 'failed', 'out_of_sync']);
        $synced = $documents->where('sync_status', 'synced');
        
        $this->info("Files to be synced:");
        $this->table(
            ['File Name', 'Size', 'Current Status', 'Last Sync'],
            $toSync->map(function ($doc) {
                return [
                    Str::limit($doc->original_name, 40),
                    $doc->formatted_file_size,
                    $doc->sync_status,
                    $doc->last_sync_at ? $doc->last_sync_at->diffForHumans() : 'Never'
                ];
            })
        );
        
        $this->newLine();
        $this->info("Summary:");
        $this->line("• Files to sync: {$toSync->count()}");
        $this->line("• Already synced: {$synced->count()}");
        $this->line("• Total size to upload: " . $this->formatBytes($toSync->sum('file_size')));
    }
    
    private function monitorSync(Project $project)
    {
        $this->info("Monitoring sync progress...");
        $bar = $this->output->createProgressBar($project->documents->count());
        $bar->start();
        
        while (true) {
            sleep(2);
            
            $project->refresh();
            $synced = $project->documents->where('sync_status', 'synced')->count();
            $bar->setProgress($synced);
            
            // Check if all done
            $pending = $project->documents->whereIn('sync_status', ['syncing', 'pending'])->count();
            if ($pending === 0) {
                break;
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Sync completed!");
    }
    
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

## Controller Implementation

```php
// app/Http/Controllers/Api/FileExplorerController.php

/**
 * Start manual sync for project
 */
public function syncProject(Request $request, Project $project)
{
    // Check if user has permission
    $this->authorize('update', $project);
    
    // Check last sync time to prevent abuse
    $lastSync = $project->syncLogs()
        ->where('action', 'upload')
        ->latest()
        ->first();
        
    if ($lastSync && $lastSync->created_at->diffInMinutes(now()) < 5) {
        return response()->json([
            'success' => false,
            'message' => 'Please wait at least 5 minutes between sync operations',
            'last_sync' => $lastSync->created_at->toIso8601String()
        ], 429); // Too Many Requests
    }
    
    // Queue sync job
    $job = new SyncProjectJob($project);
    $jobId = Str::uuid();
    
    dispatch($job)->onQueue('sync');
    
    // Log the manual sync request
    activity()
        ->performedOn($project)
        ->causedBy($request->user())
        ->withProperties([
            'action' => 'manual_sync_initiated',
            'job_id' => $jobId
        ])
        ->log('Manual cloud sync initiated');
    
    return response()->json([
        'success' => true,
        'message' => 'Sync started. This may take a few minutes depending on file sizes.',
        'job_id' => $jobId
    ]);
}
```

## User Permissions

Add permission checks for manual sync:

```php
// In ProjectPolicy

public function syncToCloud(User $user, Project $project): bool
{
    // Only project managers and above can sync
    return $user->hasAnyRole(['project_manager', 'finance_manager', 'direktur']) 
        && $project->isActive();
}
```

## UI Sync Status Indicators

```blade
<!-- Sync status badge in File Explorer -->
<div class="sync-status-badge">
    <span x-show="syncStatus === 'synced'" class="badge badge-success">
        <i class="fas fa-check-circle"></i> Synced
    </span>
    <span x-show="syncStatus === 'syncing'" class="badge badge-info">
        <i class="fas fa-sync fa-spin"></i> Syncing...
    </span>
    <span x-show="syncStatus === 'pending'" class="badge badge-warning">
        <i class="fas fa-clock"></i> Pending Sync
    </span>
    <span x-show="syncStatus === 'failed'" class="badge badge-danger">
        <i class="fas fa-exclamation-triangle"></i> Sync Failed
    </span>
</div>
```

## Notification After Sync

```php
// In SyncProjectJob

public function handle(SyncService $syncService)
{
    $result = $syncService->syncProject($this->project);
    
    if ($result['success']) {
        // Send notification to user who initiated sync
        $initiator = $this->project->activities()
            ->where('description', 'Manual cloud sync initiated')
            ->latest()
            ->first()
            ->causer;
            
        if ($initiator) {
            $initiator->notify(new SyncCompletedNotification($this->project, $result));
        }
    }
}
```

## Best Practices for Manual Sync

1. **Rate Limiting**: Prevent users from syncing too frequently (5 minute cooldown)
2. **Permission Control**: Only authorized users can trigger sync
3. **Progress Feedback**: Show clear progress indicators during sync
4. **Error Handling**: Display clear error messages if sync fails
5. **Confirmation**: Always ask for confirmation before starting sync
6. **Dry Run Option**: Allow users to preview what will be synced
7. **Activity Logging**: Log all manual sync operations for audit trail

## Monitoring & Logs

View sync logs:
```sql
-- Recent sync activities
SELECT * FROM sync_logs 
WHERE syncable_type = 'Project' 
AND syncable_id = 34
ORDER BY created_at DESC 
LIMIT 10;

-- Failed syncs
SELECT * FROM sync_logs 
WHERE status = 'failed' 
AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

## Troubleshooting

Common issues and solutions:

1. **Sync takes too long**
   - Check file sizes
   - Verify network connection
   - Consider chunking large files

2. **Sync fails repeatedly**
   - Check rclone configuration
   - Verify Google Drive quota
   - Check file permissions

3. **Files not syncing**
   - Verify file exists in storage
   - Check sync_status in database
   - Review error logs

## Summary

The sync system is designed to be **fully manual** with:
- User-initiated sync via UI button
- Command-line tools for administrators
- API endpoints for programmatic access
- No automatic/scheduled syncing
- Full user control over when data is uploaded to cloud