# Migration Script untuk Proyek Existing

## Command untuk Generate Folder Storage untuk Proyek yang Sudah Ada

### 1. Artisan Command
```php
<?php
// app/Console/Commands/GenerateProjectFolders.php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateProjectFolders extends Command
{
    protected $signature = 'projects:generate-folders 
                            {--project= : Generate folder for specific project ID}
                            {--force : Force regenerate even if folder exists}';
    
    protected $description = 'Generate storage folder structure for existing projects';
    
    private $storageService;
    
    public function __construct(StorageService $storageService)
    {
        parent::__construct();
        $this->storageService = $storageService;
    }
    
    public function handle()
    {
        $projectId = $this->option('project');
        $force = $this->option('force');
        
        if ($projectId) {
            // Generate for specific project
            $project = Project::find($projectId);
            
            if (!$project) {
                $this->error("Project with ID {$projectId} not found.");
                return 1;
            }
            
            $this->generateFolderForProject($project, $force);
        } else {
            // Generate for all projects
            $this->info('Generating folders for all existing projects...');
            
            $projects = Project::all();
            $progressBar = $this->output->createProgressBar($projects->count());
            $progressBar->start();
            
            foreach ($projects as $project) {
                $this->generateFolderForProject($project, $force);
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            $this->info('Folder generation completed!');
        }
        
        return 0;
    }
    
    private function generateFolderForProject(Project $project, bool $force = false)
    {
        $projectSlug = Str::slug($project->name);
        $projectPath = storage_path("app/proyek/{$projectSlug}");
        
        // Check if folder already exists
        if (file_exists($projectPath) && !$force) {
            $this->warn("Folder already exists for project: {$project->name} (ID: {$project->id})");
            return;
        }
        
        try {
            // Create folder structure
            $folderPath = $this->storageService->createProjectFolder($project);
            
            // Create database records
            $this->createFolderRecords($project, $folderPath);
            
            $this->info("✓ Created folders for project: {$project->name} (ID: {$project->id})");
            
            // Log the action
            activity()
                ->performedOn($project)
                ->causedBy(auth()->user() ?? null)
                ->withProperties([
                    'action' => 'folder_structure_created',
                    'path' => $folderPath
                ])
                ->log('Project folder structure created via migration command');
                
        } catch (\Exception $e) {
            $this->error("✗ Failed to create folders for project: {$project->name} - {$e->getMessage()}");
        }
    }
    
    private function createFolderRecords(Project $project, string $basePath): void
    {
        // Check if root folder record already exists
        $existingRoot = $project->folders()
            ->where('folder_type', 'root')
            ->first();
            
        if ($existingRoot) {
            $this->warn("Folder records already exist for project: {$project->name}");
            return;
        }
        
        $rootFolder = $project->folders()->create([
            'folder_name' => Str::slug($project->name),
            'folder_path' => $basePath,
            'folder_type' => 'root',
            'metadata' => [
                'created_at' => now()->toIso8601String(),
                'created_by' => 'migration_command'
            ]
        ]);
        
        // Create subfolder records
        $subfolders = [
            'dokumen' => ['kontrak', 'teknis', 'keuangan', 'laporan', 'lainnya'],
            'gambar' => [],
            'video' => []
        ];
        
        foreach ($subfolders as $main => $subs) {
            $mainFolder = $project->folders()->create([
                'folder_name' => $main,
                'folder_path' => "{$basePath}/{$main}",
                'parent_id' => $rootFolder->id,
                'folder_type' => $main
            ]);
            
            foreach ($subs as $sub) {
                $project->folders()->create([
                    'folder_name' => $sub,
                    'folder_path' => "{$basePath}/{$main}/{$sub}",
                    'parent_id' => $mainFolder->id,
                    'folder_type' => 'custom'
                ]);
            }
        }
    }
}
```

### 2. Migration untuk Existing Documents
```php
<?php
// database/migrations/2025_XX_XX_migrate_existing_documents.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing documents to new folder structure
        $documents = ProjectDocument::all();
        
        foreach ($documents as $document) {
            $project = $document->project;
            if (!$project) continue;
            
            $projectSlug = Str::slug($project->name);
            
            // Determine category based on document_type
            $categoryPath = $this->getCategoryPath($document->document_type);
            
            // Update storage_path
            $newStoragePath = storage_path("app/proyek/{$projectSlug}/{$categoryPath}/" . basename($document->file_path));
            
            // Move physical file if exists
            $oldPath = storage_path("app/{$document->file_path}");
            if (file_exists($oldPath)) {
                // Create directory if not exists
                $newDir = dirname($newStoragePath);
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0755, true);
                }
                
                // Move file
                rename($oldPath, $newStoragePath);
                
                // Update database
                $document->update([
                    'storage_path' => $newStoragePath,
                    'file_path' => "proyek/{$projectSlug}/{$categoryPath}/" . basename($document->file_path),
                    'folder_structure' => [
                        'category' => $document->document_type,
                        'migrated_at' => now()->toIso8601String()
                    ]
                ]);
            }
        }
    }
    
    private function getCategoryPath(string $documentType): string
    {
        $mapping = [
            'contract' => 'dokumen/kontrak',
            'technical' => 'dokumen/teknis',
            'financial' => 'dokumen/keuangan',
            'report' => 'dokumen/laporan',
            'other' => 'dokumen/lainnya',
            'image' => 'gambar',
            'video' => 'video'
        ];
        
        return $mapping[$documentType] ?? 'dokumen/lainnya';
    }
    
    public function down(): void
    {
        // This migration is not reversible
        // Files have been physically moved
    }
};
```

### 3. Seeder untuk Testing
```php
<?php
// database/seeders/ProjectFolderSeeder.php

namespace Database\Seeders;

use App\Models\Project;
use App\Services\StorageService;
use Illuminate\Database\Seeder;

class ProjectFolderSeeder extends Seeder
{
    public function run(): void
    {
        $storageService = app(StorageService::class);
        
        $projects = Project::all();
        
        foreach ($projects as $project) {
            $this->command->info("Creating folders for project: {$project->name}");
            
            try {
                // Create physical folders
                $folderPath = $storageService->createProjectFolder($project);
                
                // Create database records (handled by Observer if registered)
                // Or manually create if Observer not active
                if ($project->folders()->count() === 0) {
                    $this->createFolderRecords($project, $folderPath);
                }
                
                $this->command->info("✓ Folders created successfully");
            } catch (\Exception $e) {
                $this->command->error("✗ Failed: {$e->getMessage()}");
            }
        }
    }
    
    private function createFolderRecords($project, $basePath)
    {
        // Same implementation as in the command
        // ... (copy from GenerateProjectFolders command)
    }
}
```

## Cara Penggunaan

### 1. Generate Folders untuk Semua Proyek Existing
```bash
php artisan projects:generate-folders
```

### 2. Generate Folder untuk Proyek Tertentu
```bash
php artisan projects:generate-folders --project=34
```

### 3. Force Regenerate (Jika Folder Sudah Ada)
```bash
php artisan projects:generate-folders --force
```

### 4. Migrate Existing Documents
```bash
php artisan migrate --path=database/migrations/2025_XX_XX_migrate_existing_documents.php
```

### 5. Via Tinker (Manual)
```php
// Via tinker untuk proyek spesifik
$project = App\Models\Project::find(34);
$storageService = app(App\Services\StorageService::class);
$folderPath = $storageService->createProjectFolder($project);
```

## Checklist Migration

- [ ] Backup database dan files sebelum migration
- [ ] Test di environment development dulu
- [ ] Jalankan command generate folders
- [ ] Migrate existing documents ke struktur baru
- [ ] Verify semua file ter-migrate dengan benar
- [ ] Update permissions folder jika diperlukan
- [ ] Test upload file baru
- [ ] Test sync dengan rclone

## Monitoring Migration

### Check Migration Status
```php
// Artisan command untuk check status
php artisan projects:check-migration-status
```

### SQL Query untuk Monitoring
```sql
-- Check projects without folders
SELECT p.id, p.name, p.code 
FROM projects p
LEFT JOIN project_folders pf ON p.id = pf.project_id AND pf.folder_type = 'root'
WHERE pf.id IS NULL;

-- Check documents without storage_path
SELECT COUNT(*) as unmigrated_documents
FROM project_documents
WHERE storage_path IS NULL;

-- Check migration progress
SELECT 
    project_id,
    COUNT(*) as total_documents,
    SUM(CASE WHEN storage_path IS NOT NULL THEN 1 ELSE 0 END) as migrated,
    SUM(CASE WHEN storage_path IS NULL THEN 1 ELSE 0 END) as pending
FROM project_documents
GROUP BY project_id;
```

## Rollback Plan

Jika terjadi masalah saat migration:

1. **Restore dari Backup**
   - Restore database dari backup
   - Restore files dari backup

2. **Manual Rollback** (Jika partial migration)
   ```php
   // Rollback script
   $documents = ProjectDocument::whereNotNull('storage_path')->get();
   foreach ($documents as $doc) {
       // Move files back to original location
       // Update database records
   }
   ```

3. **Clean Up Folders**
   ```bash
   # Remove generated folders (CAREFUL!)
   rm -rf storage/app/proyek/*/
   ```

## Notes

- Command akan skip proyek yang sudah memiliki folder (kecuali menggunakan --force)
- Migration document akan memindahkan file fisik, pastikan backup tersedia
- Observer akan otomatis membuat folder untuk proyek baru setelah migration selesai
- Monitoring tools tersedia untuk track progress migration