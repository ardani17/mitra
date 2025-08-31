# Arsitektur Sistem Dokumen Proyek

## 1. Overview
Sistem manajemen dokumen proyek yang terintegrasi dengan struktur folder terorganisir dan sinkronisasi cloud menggunakan rclone.

## 2. Struktur Folder Storage

### 2.1 Base Path
```
/storage/proyek/[project-name-slug]/
```

### 2.2 Folder Structure
```
/storage/proyek/
└── [project-name-slug]/
    ├── dokumen/
    │   ├── kontrak/
    │   ├── teknis/
    │   ├── keuangan/
    │   ├── laporan/
    │   └── lainnya/
    ├── gambar/
    └── video/
```

### 2.3 File Naming Convention
- **Format**: Original filename preserved (no timestamp prefix)
- **Duplicate Prevention**: System validates and prevents duplicate filenames in same folder
- **Example**: `kontrak-pembangunan.pdf` (not `20250101_120000_kontrak-pembangunan.pdf`)

## 3. Database Schema

### 3.1 Updated project_documents Table
```sql
ALTER TABLE project_documents ADD COLUMN storage_path VARCHAR(500) AFTER file_path;
ALTER TABLE project_documents ADD COLUMN rclone_path VARCHAR(500) AFTER storage_path;
ALTER TABLE project_documents ADD COLUMN sync_status ENUM('pending', 'syncing', 'synced', 'failed', 'out_of_sync') DEFAULT 'pending';
ALTER TABLE project_documents ADD COLUMN sync_error TEXT NULL;
ALTER TABLE project_documents ADD COLUMN last_sync_at TIMESTAMP NULL;
ALTER TABLE project_documents ADD COLUMN checksum VARCHAR(64) NULL;
ALTER TABLE project_documents ADD COLUMN folder_structure JSON NULL;

-- Add indexes
CREATE INDEX idx_sync_status ON project_documents(sync_status);
CREATE INDEX idx_last_sync ON project_documents(last_sync_at);
```

### 3.2 New project_folders Table
```sql
CREATE TABLE project_folders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    folder_name VARCHAR(255) NOT NULL,
    folder_path VARCHAR(500) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    folder_type ENUM('root', 'dokumen', 'gambar', 'video', 'custom') DEFAULT 'custom',
    sync_status ENUM('pending', 'synced', 'failed', 'out_of_sync') DEFAULT 'pending',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES project_folders(id) ON DELETE CASCADE,
    INDEX idx_project_folder (project_id, folder_path),
    INDEX idx_parent (parent_id)
);
```

### 3.3 New sync_logs Table
```sql
CREATE TABLE sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    syncable_type VARCHAR(50) NOT NULL,
    syncable_id BIGINT UNSIGNED NOT NULL,
    action ENUM('upload', 'download', 'delete', 'check') NOT NULL,
    status ENUM('success', 'failed', 'skipped') NOT NULL,
    source_path VARCHAR(500),
    destination_path VARCHAR(500),
    file_size BIGINT UNSIGNED,
    duration_ms INT UNSIGNED,
    error_message TEXT,
    rclone_output TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_syncable (syncable_type, syncable_id),
    INDEX idx_created (created_at),
    INDEX idx_status (status)
);
```

## 4. Service Layer

### 4.1 StorageService
```php
namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class StorageService
{
    private $basePath;
    
    public function __construct()
    {
        $this->basePath = storage_path('app/proyek');
    }
    
    /**
     * Create project folder structure
     */
    public function createProjectFolder(Project $project): string
    {
        $projectSlug = Str::slug($project->name);
        $projectPath = "{$this->basePath}/{$projectSlug}";
        
        // Create main folders
        $folders = [
            'dokumen/kontrak',
            'dokumen/teknis',
            'dokumen/keuangan',
            'dokumen/laporan',
            'dokumen/lainnya',
            'gambar',
            'video'
        ];
        
        foreach ($folders as $folder) {
            $fullPath = "{$projectPath}/{$folder}";
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
        }
        
        return $projectPath;
    }
    
    /**
     * Store document with duplicate validation
     */
    public function storeDocument($file, Project $project, string $category = 'lainnya'): array
    {
        $projectPath = $this->getProjectPath($project);
        $categoryPath = $this->getCategoryPath($category);
        $fullPath = "{$projectPath}/{$categoryPath}";
        
        // Get original filename
        $originalName = $file->getClientOriginalName();
        $fileName = $originalName;
        
        // Check for duplicates
        if ($this->fileExists($fullPath, $fileName)) {
            throw new \Exception("File dengan nama '{$fileName}' sudah ada di folder ini.");
        }
        
        // Store file with original name
        $storedPath = $file->storeAs(
            "proyek/" . Str::slug($project->name) . "/{$categoryPath}",
            $fileName
        );
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $storedPath,
            'storage_path' => "{$fullPath}/{$fileName}",
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'checksum' => hash_file('sha256', $file->getRealPath())
        ];
    }
    
    /**
     * Check if file exists in folder
     */
    public function fileExists(string $folderPath, string $fileName): bool
    {
        return File::exists("{$folderPath}/{$fileName}");
    }
    
    /**
     * Get project path
     */
    public function getProjectPath(Project $project): string
    {
        return "{$this->basePath}/" . Str::slug($project->name);
    }
    
    /**
     * Get category path mapping
     */
    private function getCategoryPath(string $category): string
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
        
        return $mapping[$category] ?? 'dokumen/lainnya';
    }
    
    /**
     * Delete document file
     */
    public function deleteDocument(ProjectDocument $document): bool
    {
        if (Storage::exists($document->file_path)) {
            return Storage::delete($document->file_path);
        }
        
        return false;
    }
    
    /**
     * Move document to different folder
     */
    public function moveDocument(ProjectDocument $document, string $newCategory): bool
    {
        $oldPath = $document->file_path;
        $newCategoryPath = $this->getCategoryPath($newCategory);
        
        $projectSlug = Str::slug($document->project->name);
        $newPath = "proyek/{$projectSlug}/{$newCategoryPath}/" . basename($oldPath);
        
        // Check for duplicates in destination
        if (Storage::exists($newPath)) {
            throw new \Exception("File dengan nama yang sama sudah ada di folder tujuan.");
        }
        
        if (Storage::move($oldPath, $newPath)) {
            $document->update([
                'file_path' => $newPath,
                'storage_path' => storage_path("app/{$newPath}"),
                'document_type' => $newCategory
            ]);
            
            return true;
        }
        
        return false;
    }
}
```

### 4.2 RcloneService
```php
namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class RcloneService
{
    private $remoteName;
    private $remotePath;
    private $configPath;
    
    public function __construct()
    {
        $this->remoteName = config('rclone.remote_name', 'gdrive');
        $this->remotePath = config('rclone.remote_path', '/mitra-backup');
        $this->configPath = config('rclone.config_path');
    }
    
    /**
     * Sync local folder to remote
     */
    public function syncToRemote(string $localPath, string $remotePath = null): array
    {
        $remote = $remotePath ?? $this->remotePath;
        $fullRemotePath = "{$this->remoteName}:{$remote}";
        
        $command = [
            'rclone',
            'sync',
            $localPath,
            $fullRemotePath,
            '--config', $this->configPath,
            '--verbose',
            '--stats', '10s',
            '--transfers', '4',
            '--checkers', '8',
            '--contimeout', '60s',
            '--timeout', '300s',
            '--retries', '3',
            '--low-level-retries', '10',
            '--stats-file-name-length', '0'
        ];
        
        $process = new Process($command);
        $process->setTimeout(300);
        
        try {
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            return [
                'success' => true,
                'output' => $process->getOutput(),
                'error' => null
            ];
            
        } catch (ProcessFailedException $exception) {
            Log::error('Rclone sync failed', [
                'command' => $process->getCommandLine(),
                'error' => $exception->getMessage(),
                'output' => $process->getErrorOutput()
            ]);
            
            return [
                'success' => false,
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput()
            ];
        }
    }
    
    /**
     * Check if file exists in remote
     */
    public function checkRemoteFile(string $remotePath): bool
    {
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            'rclone',
            'lsf',
            $fullRemotePath,
            '--config', $this->configPath,
            '--max-depth', '1'
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            return !empty(trim($process->getOutput()));
        } catch (\Exception $e) {
            Log::error('Rclone check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get remote file info
     */
    public function getRemoteFileInfo(string $remotePath): ?array
    {
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            'rclone',
            'lsjson',
            $fullRemotePath,
            '--config', $this->configPath,
            '--no-modtime',
            '--no-mimetype'
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                return $output[0] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Rclone info failed', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Delete remote file
     */
    public function deleteRemoteFile(string $remotePath): bool
    {
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            'rclone',
            'delete',
            $fullRemotePath,
            '--config', $this->configPath
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Rclone delete failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
```

### 4.3 SyncService
```php
namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Sync entire project to cloud
     */
    public function syncProject(Project $project): array
    {
        $startTime = microtime(true);
        $projectPath = $this->storageService->getProjectPath($project);
        $remotePath = "/projects/" . Str::slug($project->name);
        
        // Update sync status
        $project->documents()->update(['sync_status' => 'syncing']);
        
        // Perform sync
        $result = $this->rcloneService->syncToRemote($projectPath, $remotePath);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Log sync operation
        $syncLog = SyncLog::create([
            'syncable_type' => 'Project',
            'syncable_id' => $project->id,
            'action' => 'upload',
            'status' => $result['success'] ? 'success' : 'failed',
            'source_path' => $projectPath,
            'destination_path' => $remotePath,
            'duration_ms' => $duration,
            'error_message' => $result['error'],
            'rclone_output' => $result['output']
        ]);
        
        // Update document statuses
        if ($result['success']) {
            $project->documents()->update([
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'sync_error' => null
            ]);
            
            // Update folder sync status
            $project->folders()->update([
                'sync_status' => 'synced'
            ]);
        } else {
            $project->documents()->update([
                'sync_status' => 'failed',
                'sync_error' => $result['error']
            ]);
        }
        
        return [
            'success' => $result['success'],
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
        
        $document->update(['sync_status' => 'syncing']);
        
        $localPath = storage_path("app/{$document->file_path}");
        $remotePath = "/projects/" . Str::slug($document->project->name) . "/" . 
                     str_replace('proyek/' . Str::slug($document->project->name) . '/', '', $document->file_path);
        
        $result = $this->rcloneService->syncToRemote($localPath, $remotePath);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Log sync
        SyncLog::create([
            'syncable_type' => 'ProjectDocument',
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
        } else {
            $document->update([
                'sync_status' => 'failed',
                'sync_error' => $result['error']
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
        
        $lastSync = SyncLog::where('syncable_type', 'Project')
            ->where('syncable_id', $project->id)
            ->latest()
            ->first();
        
        return [
            'stats' => $stats,
            'last_sync' => $lastSync ? $lastSync->created_at : null,
            'last_sync_status' => $lastSync ? $lastSync->status : null
        ];
    }
}
```

## 5. API Controllers

### 5.1 FileExplorerController
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\StorageService;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FileExplorerController extends Controller
{
    private $storageService;
    private $syncService;
    
    public function __construct(StorageService $storageService, SyncService $syncService)
    {
        $this->storageService = $storageService;
        $this->syncService = $syncService;
    }
    
    /**
     * Get folder structure for project
     */
    public function getFolders(Project $project)
    {
        $folders = $project->folders()
            ->with('children')
            ->whereNull('parent_id')
            ->get();
        
        return response()->json($folders);
    }
    
    /**
     * Get files in folder
     */
    public function getFiles(Project $project, Request $request)
    {
        $folderId = $request->get('folder_id');
        
        $query = $project->documents();
        
        if ($folderId) {
            $folder = $project->folders()->find($folderId);
            if ($folder) {
                $query->where('folder_structure->folder_id', $folderId);
            }
        }
        
        $files = $query->latest()->get();
        
        return response()->json($files);
    }
    
    /**
     * Upload multiple files
     */
    public function uploadFiles(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|max:102400', // 100MB max
            'folder_id' => 'nullable|exists:project_folders,id',
            'category' => 'required|in:contract,technical,financial,report,other'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $uploadedFiles = [];
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($request->file('files') as $file) {
                try {
                    // Store file with duplicate check
                    $fileData = $this->storageService->storeDocument(
                        $file,
                        $project,
                        $request->category
                    );
                    
                    // Create document record
                    $document = $project->documents()->create([
                        'name' => pathinfo($fileData['file_name'], PATHINFO_FILENAME),
                        'original_name' => $fileData['original_name'],
                        'file_path' => $fileData['file_path'],
                        'storage_path' => $fileData['storage_path'],
                        'file_size' => $fileData['file_size'],
                        'mime_type' => $fileData['mime_type'],
                        'document_type' => $request->category,
                        'uploaded_by' => auth()->id(),
                        'checksum' => $fileData['checksum'],
                        'folder_structure' => [
                            'folder_id' => $request->folder_id,
                            'category' => $request->category
                        ]
                    ]);
                    
                    $uploadedFiles[] = $document;
                    
                    // Queue sync job
                    dispatch(new \App\Jobs\SyncDocumentJob($document));
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'uploaded' => $uploadedFiles,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create folder
     */
    public function createFolder(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9-_\s]+$/',
            'parent_id' => 'nullable|exists:project_folders,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $parentPath = '';
        if ($request->parent_id) {
            $parent = $project->folders()->find($request->parent_id);
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent folder not found'
                ], 404);
            }
            $parentPath = $parent->folder_path;
        } else {
            $parentPath = $this->storageService->getProjectPath($project);
        }
        
        $folderPath = $parentPath . '/' . Str::slug($request->name);
        
        // Check if folder already exists
        if (File::exists($folderPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Folder already exists'
            ], 409);
        }
        
        // Create physical folder
        File::makeDirectory($folderPath, 0755, true);
        
        // Create database record
        $folder = $project->folders()->create([
            'folder_name' => $request->name,
            'folder_path' => $folderPath,
            'parent_id' => $request->parent_id,
            'folder_type' => 'custom'
        ]);
        
        return response()->json([
            'success' => true,
            'folder' => $folder
        ]);
    }
    
    /**
     * Delete file
     */
    public function deleteFile(ProjectDocument $document)
    {
        DB::beginTransaction();
        
        try {
            // Delete physical file
            $this->storageService->deleteDocument($document);
            
            // Delete from cloud if synced
            if ($document->rclone_path) {
                $this->rcloneService->deleteRemoteFile($document->rclone_path);
            }
            
            // Delete database record
            $document->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Rename file
     */
    public function renameFile(Request $request, ProjectDocument $document)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $newName = $request->name;
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $newFileName = $newName . '.' . $extension;
        
        // Check for duplicates
        $folderPath = dirname($document->storage_path);
        if ($this->storageService->fileExists($folderPath, $newFileName)) {
            return response()->json([
                'success' => false,
                'message' => 'File with this name already exists'
            ], 409);
        }
        
        // Rename physical file
        $oldPath = storage_path("app/{$document->file_path}");
        $newPath = dirname($oldPath) . '/' . $newFileName;
        
        if (File::move($oldPath, $newPath)) {
            // Update database
            $document->update([
                'name' => $newName,
                'original_name' => $newFileName,
                'file_path' => str_replace(basename($document->file_path), $newFileName, $document->file_path),
                'storage_path' => $newPath,
                'sync_status' => 'out_of_sync' // Mark for re-sync
            ]);
            
            // Queue re-sync
            dispatch(new \App\Jobs\SyncDocumentJob($document));
            
            return response()->json([
                'success' => true,
                'document' => $document
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to rename file'
        ], 500);
    }
    
    /**
     * Start sync for project
     */
    public function syncProject(Project $project)
    {
        // Queue sync job
        dispatch(new \App\Jobs\SyncProjectJob($project));
        
        return response()->json([
            'success' => true,
            'message' => 'Sync started'
        ]);
    }
    
    /**
     * Get sync status
     */
    public function getSyncStatus(Project $project)
    {
        $status = $this->syncService->checkSyncStatus($project);
        
        return response()->json($status);
    }
}
```

## 6. Queue Jobs

### 6.1 SyncProjectJob
```php
namespace App\Jobs;

use App\Models\Project;
use App\Services\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $project;
    
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
    
    public function handle(SyncService $syncService)
    {
        Log::info('Starting project sync', ['project_id' => $this->project->id]);
        
        $result = $syncService->syncProject($this->project);
        
        if ($result['success']) {
            Log::info('Project sync completed', [
                'project_id' => $this->project->id,
                'duration' => $result['duration']
            ]);
        } else {
            Log::error('Project sync failed', [
                'project_id' => $this->project->id
            ]);
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error('Project sync job failed', [
            'project_id' => $this->project->id,
            'error' => $exception->getMessage()
        ]);
        
        // Update project documents status
        $this->project->documents()->update([
            'sync_status' => 'failed',
            'sync_error' => $exception->getMessage()
        ]);
    }
}
```

### 6.2 SyncDocumentJob
```php
namespace App\Jobs;

use App\Models\ProjectDocument;
use App\Services\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Serializes