<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncService
{
    private $rcloneService;
    private $storageService;
    
    public function __construct(RcloneService $rcloneService, StorageService $storageService)
    {
        $this->rcloneService = $rcloneService;
        $this->storageService = $storageService;
    }
    
    /**
     * Sync entire project to cloud (manual sync)
     */
    public function syncProject(Project $project): array
    {
        $startTime = microtime(true);
        $projectPath = $this->storageService->getProjectPath($project);
        $remotePath = "/projects/" . Str::slug($project->name);
        
        // Check if rclone is available
        if (!$this->rcloneService->isAvailable()) {
            Log::warning('Rclone is not available, skipping sync for project: ' . $project->id);
            return [
                'success' => false,
                'message' => 'Rclone is not configured or available',
                'duration' => 0,
                'log_id' => null
            ];
        }
        
        // Update sync status to syncing
        $project->documents()->update(['sync_status' => 'syncing']);
        $project->folders()->update(['sync_status' => 'syncing']);
        
        // Perform sync
        $result = $this->rcloneService->syncToRemote($projectPath, $remotePath);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Log sync operation
        $syncLog = SyncLog::create([
            'syncable_type' => 'App\Models\Project',
            'syncable_id' => $project->id,
            'action' => 'upload',
            'status' => $result['success'] ? 'success' : 'failed',
            'source_path' => $projectPath,
            'destination_path' => $remotePath,
            'duration_ms' => $duration,
            'error_message' => $result['error'],
            'rclone_output' => $result['output']
        ]);
        
        // Update document statuses based on result
        if ($result['success']) {
            $project->documents()->update([
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'sync_error' => null,
                'rclone_path' => DB::raw("CONCAT('{$remotePath}/', SUBSTRING_INDEX(file_path, '/', -1))")
            ]);
            
            // Update folder sync status
            $project->folders()->update([
                'sync_status' => 'synced'
            ]);
            
            Log::info('Project sync completed successfully', [
                'project_id' => $project->id,
                'duration_ms' => $duration
            ]);
        } else {
            $project->documents()->update([
                'sync_status' => 'failed',
                'sync_error' => $result['error']
            ]);
            
            $project->folders()->update([
                'sync_status' => 'failed'
            ]);
            
            Log::error('Project sync failed', [
                'project_id' => $project->id,
                'error' => $result['error']
            ]);
        }
        
        return [
            'success' => $result['success'],
            'message' => $result['success'] ? 'Sync completed successfully' : 'Sync failed',
            'duration' => $duration,
            'log_id' => $syncLog->id
        ];
    }
    
    /**
     * Sync single document
     */
    public function syncDocument(ProjectDocument $document): bool
    {
        $startTime = microtime(true);
        
        // Check if rclone is available
        if (!$this->rcloneService->isAvailable()) {
            Log::warning('Rclone is not available, skipping sync for document: ' . $document->id);
            return false;
        }
        
        $document->update(['sync_status' => 'syncing']);
        
        $localPath = storage_path("app/{$document->file_path}");
        $remotePath = "/projects/" . Str::slug($document->project->name) . "/" . 
                     str_replace('proyek/' . Str::slug($document->project->name) . '/', '', $document->file_path);
        
        $result = $this->rcloneService->syncToRemote($localPath, $remotePath);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Log sync
        SyncLog::create([
            'syncable_type' => 'App\Models\ProjectDocument',
            'syncable_id' => $document->id,
            'action' => 'upload',
            'status' => $result['success'] ? 'success' : 'failed',
            'source_path' => $localPath,
            'destination_path' => $remotePath,
            'duration_ms' => $duration,
            'file_size' => $document->file_size,
            'error_message' => $result['error'],
            'rclone_output' => $result['output']
        ]);
        
        // Update document
        if ($result['success']) {
            $document->update([
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'sync_error' => null,
                'rclone_path' => $remotePath
            ]);
            
            Log::info('Document sync completed', [
                'document_id' => $document->id,
                'duration_ms' => $duration
            ]);
        } else {
            $document->update([
                'sync_status' => 'failed',
                'sync_error' => $result['error']
            ]);
            
            Log::error('Document sync failed', [
                'document_id' => $document->id,
                'error' => $result['error']
            ]);
        }
        
        return $result['success'];
    }
    
    /**
     * Check sync status for project
     */
    public function checkSyncStatus(Project $project): array
    {
        $documents = $project->documents;
        
        $stats = [
            'total' => $documents->count(),
            'synced' => $documents->where('sync_status', 'synced')->count(),
            'pending' => $documents->where('sync_status', 'pending')->count(),
            'failed' => $documents->where('sync_status', 'failed')->count(),
            'syncing' => $documents->where('sync_status', 'syncing')->count(),
            'out_of_sync' => $documents->where('sync_status', 'out_of_sync')->count(),
        ];
        
        $stats['percentage'] = $stats['total'] > 0 
            ? round(($stats['synced'] / $stats['total']) * 100, 2)
            : 0;
        
        $lastSync = SyncLog::where('syncable_type', 'App\Models\Project')
            ->where('syncable_id', $project->id)
            ->latest('created_at')
            ->first();
        
        return [
            'stats' => $stats,
            'last_sync' => $lastSync ? $lastSync->created_at : null,
            'last_sync_status' => $lastSync ? $lastSync->status : null,
            'last_sync_duration' => $lastSync ? $lastSync->formatted_duration : null,
            'is_syncing' => $stats['syncing'] > 0,
            'needs_sync' => ($stats['pending'] + $stats['failed'] + $stats['out_of_sync']) > 0
        ];
    }
    
    /**
     * Mark documents as out of sync when modified
     */
    public function markAsOutOfSync(ProjectDocument $document): void
    {
        if ($document->sync_status === 'synced') {
            $document->update(['sync_status' => 'out_of_sync']);
            
            Log::info('Document marked as out of sync', [
                'document_id' => $document->id
            ]);
        }
    }
    
    /**
     * Verify sync integrity
     */
    public function verifySyncIntegrity(Project $project): array
    {
        $issues = [];
        $documents = $project->documents()->where('sync_status', 'synced')->get();
        
        foreach ($documents as $document) {
            if ($document->rclone_path) {
                // Check if remote file exists
                $exists = $this->rcloneService->checkRemoteFile($document->rclone_path);
                
                if (!$exists) {
                    $issues[] = [
                        'document_id' => $document->id,
                        'name' => $document->name,
                        'issue' => 'Remote file not found'
                    ];
                    
                    // Mark as out of sync
                    $document->update(['sync_status' => 'out_of_sync']);
                }
            }
            
            // Check if local file has been modified after last sync
            if ($document->storage_path && file_exists($document->storage_path)) {
                $localModTime = filemtime($document->storage_path);
                $lastSyncTime = $document->last_sync_at ? $document->last_sync_at->timestamp : 0;
                
                if ($localModTime > $lastSyncTime) {
                    $issues[] = [
                        'document_id' => $document->id,
                        'name' => $document->name,
                        'issue' => 'Local file modified after sync'
                    ];
                    
                    // Mark as out of sync
                    $document->update(['sync_status' => 'out_of_sync']);
                }
            }
        }
        
        return [
            'has_issues' => count($issues) > 0,
            'issue_count' => count($issues),
            'issues' => $issues
        ];
    }
    
    /**
     * Get sync statistics for dashboard
     */
    public function getSyncStatistics(): array
    {
        $totalProjects = Project::count();
        $syncedProjects = Project::whereHas('documents', function($query) {
            $query->where('sync_status', 'synced');
        })->count();
        
        $totalDocuments = ProjectDocument::count();
        $syncedDocuments = ProjectDocument::where('sync_status', 'synced')->count();
        $pendingDocuments = ProjectDocument::whereIn('sync_status', ['pending', 'out_of_sync'])->count();
        $failedDocuments = ProjectDocument::where('sync_status', 'failed')->count();
        
        $recentSyncs = SyncLog::with('syncable')
            ->latest('created_at')
            ->limit(10)
            ->get();
        
        $totalSyncSize = ProjectDocument::where('sync_status', 'synced')->sum('file_size');
        
        // Get remote storage info if available
        $remoteInfo = ['size' => 0, 'count' => 0];
        if ($this->rcloneService->isAvailable()) {
            $remoteInfo = $this->rcloneService->getRemoteSize();
        }
        
        return [
            'projects' => [
                'total' => $totalProjects,
                'synced' => $syncedProjects,
                'percentage' => $totalProjects > 0 ? round(($syncedProjects / $totalProjects) * 100, 2) : 0
            ],
            'documents' => [
                'total' => $totalDocuments,
                'synced' => $syncedDocuments,
                'pending' => $pendingDocuments,
                'failed' => $failedDocuments,
                'percentage' => $totalDocuments > 0 ? round(($syncedDocuments / $totalDocuments) * 100, 2) : 0
            ],
            'storage' => [
                'local_size' => $totalSyncSize,
                'local_size_formatted' => $this->formatBytes($totalSyncSize),
                'remote_size' => $remoteInfo['size'],
                'remote_size_formatted' => $this->formatBytes($remoteInfo['size']),
                'remote_file_count' => $remoteInfo['count']
            ],
            'recent_syncs' => $recentSyncs,
            'rclone_available' => $this->rcloneService->isAvailable()
        ];
    }
    
    /**
     * Retry failed syncs
     */
    public function retryFailedSyncs(Project $project = null): array
    {
        $query = ProjectDocument::where('sync_status', 'failed');
        
        if ($project) {
            $query->where('project_id', $project->id);
        }
        
        $failedDocuments = $query->get();
        $results = [
            'total' => $failedDocuments->count(),
            'success' => 0,
            'failed' => 0
        ];
        
        foreach ($failedDocuments as $document) {
            if ($this->syncDocument($document)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}