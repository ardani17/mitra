<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class StorageDatabaseSyncService
{
    private $disk = 'local';
    
    /**
     * Check synchronization status between storage and database
     */
    public function checkSyncStatus(Project $project): array
    {
        $issues = [
            'orphaned_files' => [],    // Files in storage but not in DB
            'missing_files' => [],      // Records in DB but files missing
            'modified_files' => [],     // Files with different size/checksum
            'orphaned_folders' => [],   // Folders in storage but not in DB
            'missing_folders' => []     // Folders in DB but not in storage
        ];
        
        $projectSlug = Str::slug($project->name);
        $storagePath = storage_path("app/proyek/{$projectSlug}");
        $relativePath = "proyek/{$projectSlug}";
        
        // Check if project folder exists
        if (!file_exists($storagePath)) {
            Log::warning("Project folder does not exist: {$storagePath}");
            return [
                'is_synced' => false,
                'issues' => ['error' => 'Project folder does not exist'],
                'stats' => [
                    'total_issues' => 1,
                    'orphaned_files' => 0,
                    'missing_files' => 0,
                    'modified_files' => 0,
                    'orphaned_folders' => 0,
                    'missing_folders' => 0
                ]
            ];
        }
        
        // 1. Scan all files in storage
        $storageFiles = $this->scanStorageFiles($storagePath, $relativePath);
        
        // 2. Scan all folders in storage
        $storageFolders = $this->scanStorageFolders($storagePath, $relativePath);
        
        // 3. Get all database records for this project
        $dbDocuments = ProjectDocument::where('project_id', $project->id)
            ->get()
            ->keyBy('file_path');
        
        $dbFolders = ProjectFolder::where('project_id', $project->id)
            ->get()
            ->keyBy('folder_path');
        
        // 4. Compare files: Find orphaned files (in storage but not in DB)
        foreach ($storageFiles as $filePath => $fileInfo) {
            if (!$dbDocuments->has($filePath)) {
                $issues['orphaned_files'][] = [
                    'path' => $filePath,
                    'name' => basename($filePath),
                    'size' => $fileInfo['size'],
                    'size_formatted' => $this->formatBytes($fileInfo['size']),
                    'modified' => date('Y-m-d H:i:s', $fileInfo['modified']),
                    'type' => $fileInfo['extension']
                ];
            } else {
                // Check if file has been modified (different size or checksum)
                $dbDoc = $dbDocuments[$filePath];
                
                // Check size difference
                if ($dbDoc->file_size != $fileInfo['size']) {
                    $issues['modified_files'][] = [
                        'id' => $dbDoc->id,
                        'path' => $filePath,
                        'name' => $dbDoc->name,
                        'db_size' => $dbDoc->file_size,
                        'db_size_formatted' => $this->formatBytes($dbDoc->file_size),
                        'storage_size' => $fileInfo['size'],
                        'storage_size_formatted' => $this->formatBytes($fileInfo['size']),
                        'difference' => $fileInfo['size'] - $dbDoc->file_size
                    ];
                }
                
                // Check checksum if available
                if ($dbDoc->checksum && isset($fileInfo['checksum']) && $dbDoc->checksum != $fileInfo['checksum']) {
                    // Add to modified files if not already added
                    $alreadyAdded = false;
                    foreach ($issues['modified_files'] as $modFile) {
                        if ($modFile['id'] == $dbDoc->id) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                    
                    if (!$alreadyAdded) {
                        $issues['modified_files'][] = [
                            'id' => $dbDoc->id,
                            'path' => $filePath,
                            'name' => $dbDoc->name,
                            'reason' => 'checksum_mismatch',
                            'db_checksum' => substr($dbDoc->checksum, 0, 8) . '...',
                            'storage_checksum' => substr($fileInfo['checksum'], 0, 8) . '...'
                        ];
                    }
                }
            }
        }
        
        // 5. Find missing files (in DB but not in storage)
        foreach ($dbDocuments as $doc) {
            if (!isset($storageFiles[$doc->file_path])) {
                $issues['missing_files'][] = [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'path' => $doc->file_path,
                    'size' => $doc->file_size,
                    'size_formatted' => $this->formatBytes($doc->file_size),
                    'type' => pathinfo($doc->name, PATHINFO_EXTENSION),
                    'uploaded_at' => $doc->created_at->format('Y-m-d H:i:s')
                ];
            }
        }
        
        // 6. Compare folders
        foreach ($storageFolders as $folderPath => $folderInfo) {
            if (!$dbFolders->has($folderPath)) {
                $issues['orphaned_folders'][] = [
                    'path' => $folderPath,
                    'name' => basename($folderPath),
                    'file_count' => $folderInfo['file_count']
                ];
            }
        }
        
        foreach ($dbFolders as $folder) {
            if (!isset($storageFolders[$folder->folder_path])) {
                $issues['missing_folders'][] = [
                    'id' => $folder->id,
                    'name' => $folder->folder_name,
                    'path' => $folder->folder_path
                ];
            }
        }
        
        // Calculate total issues
        $totalIssues = count($issues['orphaned_files']) + 
                      count($issues['missing_files']) + 
                      count($issues['modified_files']) +
                      count($issues['orphaned_folders']) +
                      count($issues['missing_folders']);
        
        return [
            'is_synced' => $totalIssues === 0,
            'issues' => $issues,
            'stats' => [
                'total_issues' => $totalIssues,
                'orphaned_files' => count($issues['orphaned_files']),
                'missing_files' => count($issues['missing_files']),
                'modified_files' => count($issues['modified_files']),
                'orphaned_folders' => count($issues['orphaned_folders']),
                'missing_folders' => count($issues['missing_folders'])
            ]
        ];
    }
    
    /**
     * Perform synchronization to fix issues
     */
    public function performSync(Project $project, array $options = []): array
    {
        $syncStatus = $this->checkSyncStatus($project);
        
        if ($syncStatus['is_synced']) {
            return [
                'success' => true,
                'message' => 'Already synchronized',
                'results' => [
                    'added_files' => 0,
                    'removed_files' => 0,
                    'updated_files' => 0,
                    'added_folders' => 0,
                    'removed_folders' => 0,
                    'errors' => []
                ]
            ];
        }
        
        $results = [
            'added_files' => 0,
            'removed_files' => 0,
            'updated_files' => 0,
            'added_folders' => 0,
            'removed_folders' => 0,
            'errors' => []
        ];
        
        $issues = $syncStatus['issues'];
        
        DB::beginTransaction();
        
        try {
            // 1. Handle orphaned folders (add to DB)
            foreach ($issues['orphaned_folders'] ?? [] as $folder) {
                try {
                    $parentPath = dirname($folder['path']);
                    $parentFolder = null;
                    
                    if ($parentPath !== '.' && $parentPath !== "proyek/" . Str::slug($project->name)) {
                        $parentFolder = ProjectFolder::where('project_id', $project->id)
                            ->where('folder_path', $parentPath)
                            ->first();
                    }
                    
                    ProjectFolder::create([
                        'project_id' => $project->id,
                        'folder_name' => $folder['name'],
                        'folder_path' => $folder['path'],
                        'parent_id' => $parentFolder ? $parentFolder->id : null,
                        'folder_type' => 'custom',
                        'sync_status' => 'synced',
                        'metadata' => json_encode([
                            'synced_at' => now()->toIso8601String(),
                            'synced_by' => auth()->id() ?? 'system'
                        ])
                    ]);
                    
                    $results['added_folders']++;
                    
                    Log::info('Added orphaned folder to database', [
                        'project_id' => $project->id,
                        'folder_path' => $folder['path']
                    ]);
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to add folder {$folder['path']}: " . $e->getMessage();
                    Log::error('Failed to add orphaned folder', [
                        'folder' => $folder,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 2. Handle orphaned files (add to DB)
            foreach ($issues['orphaned_files'] ?? [] as $file) {
                try {
                    // Determine file type and document type
                    $extension = strtolower($file['type']);
                    $fileType = $this->getFileType($extension);
                    $documentType = $this->getDocumentType($file['path']);
                    
                    ProjectDocument::create([
                        'project_id' => $project->id,
                        'name' => $file['name'],
                        'original_name' => $file['name'],
                        'file_path' => $file['path'],
                        'storage_path' => storage_path("app/{$file['path']}"),
                        'file_size' => $file['size'],
                        'file_type' => $fileType,
                        'document_type' => $documentType,
                        'sync_status' => 'synced',
                        'uploaded_by' => auth()->id() ?? 1,
                        'description' => 'Auto-synced from storage',
                        'folder_structure' => json_encode([
                            'category' => $documentType,
                            'synced_at' => now()->toIso8601String()
                        ])
                    ]);
                    
                    $results['added_files']++;
                    
                    Log::info('Added orphaned file to database', [
                        'project_id' => $project->id,
                        'file_path' => $file['path']
                    ]);
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to add file {$file['path']}: " . $e->getMessage();
                    Log::error('Failed to add orphaned file', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 3. Handle missing files (remove from DB or mark as deleted)
            foreach ($issues['missing_files'] ?? [] as $file) {
                try {
                    $document = ProjectDocument::find($file['id']);
                    
                    if ($document) {
                        if ($options['soft_delete'] ?? false) {
                            // Soft delete - just mark as deleted
                            $document->update([
                                'sync_status' => 'deleted',
                                'sync_error' => 'File not found in storage'
                            ]);
                        } else {
                            // Hard delete
                            $document->delete();
                        }
                        
                        $results['removed_files']++;
                        
                        Log::info('Removed missing file from database', [
                            'document_id' => $file['id'],
                            'file_path' => $file['path']
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to remove file record {$file['id']}: " . $e->getMessage();
                    Log::error('Failed to remove missing file', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 4. Handle modified files (update metadata)
            foreach ($issues['modified_files'] ?? [] as $file) {
                try {
                    $fullPath = storage_path("app/{$file['path']}");
                    $checksum = null;
                    
                    // Calculate new checksum if file exists
                    if (file_exists($fullPath)) {
                        $checksum = hash_file('sha256', $fullPath);
                    }
                    
                    ProjectDocument::where('id', $file['id'])
                        ->update([
                            'file_size' => $file['storage_size'] ?? $file['storage_size'] ?? 0,
                            'checksum' => $checksum,
                            'sync_status' => 'synced',
                            'last_sync_at' => now()
                        ]);
                    
                    $results['updated_files']++;
                    
                    Log::info('Updated modified file metadata', [
                        'document_id' => $file['id'],
                        'file_path' => $file['path']
                    ]);
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to update file {$file['id']}: " . $e->getMessage();
                    Log::error('Failed to update modified file', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 5. Handle missing folders (remove from DB)
            foreach ($issues['missing_folders'] ?? [] as $folder) {
                try {
                    $folderRecord = ProjectFolder::find($folder['id']);
                    if ($folderRecord) {
                        $folderRecord->delete();
                        $results['removed_folders']++;
                        
                        Log::info('Removed missing folder from database', [
                            'folder_id' => $folder['id'],
                            'folder_path' => $folder['path']
                        ]);
                    } else {
                        Log::warning('Folder record not found for deletion', [
                            'folder_id' => $folder['id']
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to remove folder {$folder['id']}: " . $e->getMessage();
                    Log::error('Failed to remove missing folder', [
                        'folder' => $folder,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            // Log sync summary
            Log::info('Storage-Database sync completed', [
                'project_id' => $project->id,
                'results' => $results
            ]);
            
            return [
                'success' => true,
                'message' => 'Synchronization completed successfully',
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Storage-Database sync failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Synchronization failed: ' . $e->getMessage(),
                'results' => $results
            ];
        }
    }
    
    /**
     * Scan storage files recursively
     */
    private function scanStorageFiles($path, $relativePath): array
    {
        $files = [];
        
        if (!is_dir($path)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = str_replace('\\', '/', $file->getPathname());
                $storageBasePath = str_replace('\\', '/', storage_path('app/'));
                $relativeFilePath = str_replace($storageBasePath, '', $filePath);
                
                $files[$relativeFilePath] = [
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'extension' => strtolower($file->getExtension())
                ];
                
                // Calculate checksum for small files (< 10MB)
                if ($file->getSize() < 10485760) {
                    $files[$relativeFilePath]['checksum'] = hash_file('sha256', $file->getPathname());
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Scan storage folders recursively
     */
    private function scanStorageFolders($path, $relativePath): array
    {
        $folders = [];
        
        if (!is_dir($path)) {
            return $folders;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $folderPath = str_replace('\\', '/', $item->getPathname());
                $storageBasePath = str_replace('\\', '/', storage_path('app/'));
                $relativeFolderPath = str_replace($storageBasePath, '', $folderPath);
                
                // Count files in this folder (direct children only)
                $fileCount = 0;
                $dirIterator = new \DirectoryIterator($item->getPathname());
                foreach ($dirIterator as $file) {
                    if ($file->isFile()) {
                        $fileCount++;
                    }
                }
                
                $folders[$relativeFolderPath] = [
                    'file_count' => $fileCount
                ];
            }
        }
        
        return $folders;
    }
    
    /**
     * Get file type based on extension
     */
    private function getFileType($extension): string
    {
        $typeMap = [
            // Documents
            'pdf' => 'document',
            'doc' => 'document',
            'docx' => 'document',
            'txt' => 'document',
            'rtf' => 'document',
            
            // Spreadsheets
            'xls' => 'spreadsheet',
            'xlsx' => 'spreadsheet',
            'csv' => 'spreadsheet',
            
            // Images
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'gif' => 'image',
            'bmp' => 'image',
            
            // Archives
            'zip' => 'archive',
            'rar' => 'archive',
            '7z' => 'archive',
            
            // Videos
            'mp4' => 'video',
            'avi' => 'video',
            'mov' => 'video',
            'mkv' => 'video'
        ];
        
        return $typeMap[$extension] ?? 'other';
    }
    
    /**
     * Determine document type from path
     */
    private function getDocumentType($path): string
    {
        if (strpos($path, '/kontrak/') !== false) return 'contract';
        if (strpos($path, '/teknis/') !== false) return 'technical';
        if (strpos($path, '/keuangan/') !== false) return 'financial';
        if (strpos($path, '/laporan/') !== false) return 'report';
        if (strpos($path, '/gambar/') !== false) return 'image';
        if (strpos($path, '/video/') !== false) return 'video';
        
        return 'other';
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