<?php

namespace App\Services\Telegram;

use App\Models\BotActivity;
use App\Models\BotUploadQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TelegramStorageSyncService
{
    private $disk = 'local';
    private $basePath = 'proyek'; // Base path for all project files
    
    /**
     * Check synchronization status between local storage and Telegram uploads
     */
    public function checkSyncStatus(): array
    {
        $issues = [
            'not_uploaded' => [],       // Files in storage but not uploaded to Telegram
            'upload_failed' => [],       // Files that failed to upload
            'upload_pending' => [],      // Files pending in upload queue
            'recently_modified' => [],   // Files modified after last upload
            'orphaned_uploads' => []     // Upload records without corresponding files
        ];
        
        $storagePath = storage_path("app/{$this->basePath}");
        
        // Check if storage folder exists
        if (!file_exists($storagePath)) {
            Log::warning("Storage folder does not exist: {$storagePath}");
            return [
                'is_synced' => false,
                'issues' => ['error' => 'Storage folder does not exist'],
                'stats' => [
                    'total_issues' => 1,
                    'not_uploaded' => 0,
                    'upload_failed' => 0,
                    'upload_pending' => 0,
                    'recently_modified' => 0,
                    'orphaned_uploads' => 0
                ]
            ];
        }
        
        // 1. Scan all files in storage
        $storageFiles = $this->scanStorageFiles($storagePath, $this->basePath);
        
        // 2. Get all successful uploads from bot activity
        $uploadedFiles = BotActivity::where('message_type', 'file')
            ->where('status', 'success')
            ->get()
            ->keyBy(function ($item) {
                $fileInfo = $item->file_info;
                return $fileInfo['file_name'] ?? null;
            })
            ->filter();
        
        // 3. Get pending uploads from queue
        $pendingUploads = BotUploadQueue::whereIn('status', ['pending', 'processing'])
            ->get()
            ->keyBy('file_path');
        
        // 4. Get failed uploads from queue
        $failedUploads = BotUploadQueue::where('status', 'failed')
            ->where('retry_count', '>=', 3) // Only show files that have been retried multiple times
            ->get()
            ->keyBy('file_path');
        
        // 5. Check each file in storage
        foreach ($storageFiles as $filePath => $fileInfo) {
            $fileName = basename($filePath);
            $relativePath = str_replace(storage_path('app/'), '', $filePath);
            
            // Check if file is in pending queue
            if ($pendingUploads->has($relativePath)) {
                $queueItem = $pendingUploads[$relativePath];
                $issues['upload_pending'][] = [
                    'path' => $relativePath,
                    'name' => $fileName,
                    'size' => $fileInfo['size'],
                    'size_formatted' => $this->formatBytes($fileInfo['size']),
                    'queued_at' => $queueItem->created_at->format('Y-m-d H:i:s'),
                    'status' => $queueItem->status,
                    'retry_count' => $queueItem->retry_count
                ];
                continue;
            }
            
            // Check if file failed to upload
            if ($failedUploads->has($relativePath)) {
                $queueItem = $failedUploads[$relativePath];
                $issues['upload_failed'][] = [
                    'path' => $relativePath,
                    'name' => $fileName,
                    'size' => $fileInfo['size'],
                    'size_formatted' => $this->formatBytes($fileInfo['size']),
                    'error' => $queueItem->error_message,
                    'retry_count' => $queueItem->retry_count,
                    'last_attempt' => $queueItem->updated_at->format('Y-m-d H:i:s')
                ];
                continue;
            }
            
            // Check if file was uploaded
            $uploadRecord = null;
            foreach ($uploadedFiles as $uploaded) {
                $uploadedFileInfo = $uploaded->file_info;
                if (isset($uploadedFileInfo['file_name']) && 
                    (basename($uploadedFileInfo['file_name']) === $fileName ||
                     $uploadedFileInfo['file_name'] === $fileName)) {
                    $uploadRecord = $uploaded;
                    break;
                }
            }
            
            if (!$uploadRecord) {
                // File not uploaded to Telegram
                $issues['not_uploaded'][] = [
                    'path' => $relativePath,
                    'name' => $fileName,
                    'size' => $fileInfo['size'],
                    'size_formatted' => $this->formatBytes($fileInfo['size']),
                    'modified' => date('Y-m-d H:i:s', $fileInfo['modified']),
                    'type' => $fileInfo['extension']
                ];
            } else {
                // Check if file was modified after upload
                $uploadTime = $uploadRecord->created_at->timestamp;
                if ($fileInfo['modified'] > $uploadTime) {
                    $issues['recently_modified'][] = [
                        'path' => $relativePath,
                        'name' => $fileName,
                        'size' => $fileInfo['size'],
                        'size_formatted' => $this->formatBytes($fileInfo['size']),
                        'modified' => date('Y-m-d H:i:s', $fileInfo['modified']),
                        'uploaded' => $uploadRecord->created_at->format('Y-m-d H:i:s'),
                        'time_diff' => $this->formatTimeDiff($fileInfo['modified'] - $uploadTime)
                    ];
                }
            }
        }
        
        // 6. Check for orphaned upload records (uploads without files)
        foreach ($uploadedFiles as $uploaded) {
            $uploadedFileInfo = $uploaded->file_info;
            if (!isset($uploadedFileInfo['file_name'])) {
                continue;
            }
            
            $fileName = basename($uploadedFileInfo['file_name']);
            $found = false;
            
            // Check if file exists in storage
            foreach ($storageFiles as $filePath => $fileInfo) {
                if (basename($filePath) === $fileName) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $issues['orphaned_uploads'][] = [
                    'id' => $uploaded->id,
                    'name' => $fileName,
                    'uploaded_at' => $uploaded->created_at->format('Y-m-d H:i:s'),
                    'telegram_user' => $uploaded->username,
                    'file_id' => $uploadedFileInfo['file_id'] ?? null
                ];
            }
        }
        
        // Calculate total issues
        $totalIssues = count($issues['not_uploaded']) + 
                      count($issues['upload_failed']) + 
                      count($issues['upload_pending']) +
                      count($issues['recently_modified']) +
                      count($issues['orphaned_uploads']);
        
        return [
            'is_synced' => $totalIssues === 0,
            'issues' => $issues,
            'stats' => [
                'total_issues' => $totalIssues,
                'not_uploaded' => count($issues['not_uploaded']),
                'upload_failed' => count($issues['upload_failed']),
                'upload_pending' => count($issues['upload_pending']),
                'recently_modified' => count($issues['recently_modified']),
                'orphaned_uploads' => count($issues['orphaned_uploads'])
            ]
        ];
    }
    
    /**
     * Perform synchronization to fix issues
     */
    public function performSync(array $options = []): array
    {
        $syncStatus = $this->checkSyncStatus();
        
        if ($syncStatus['is_synced']) {
            return [
                'success' => true,
                'message' => 'Already synchronized',
                'results' => [
                    'queued_for_upload' => 0,
                    'retried_failed' => 0,
                    'cleaned_orphaned' => 0,
                    'errors' => []
                ]
            ];
        }
        
        $results = [
            'queued_for_upload' => 0,
            'retried_failed' => 0,
            'cleaned_orphaned' => 0,
            'errors' => []
        ];
        
        $issues = $syncStatus['issues'];
        
        DB::beginTransaction();
        
        try {
            // 1. Queue not uploaded files for upload
            foreach ($issues['not_uploaded'] ?? [] as $file) {
                try {
                    // Check file size limit (50MB for Telegram)
                    if ($file['size'] > 50 * 1024 * 1024) {
                        $results['errors'][] = "File too large for Telegram: {$file['name']} ({$file['size_formatted']})";
                        continue;
                    }
                    
                    // Add to upload queue
                    BotUploadQueue::create([
                        'file_path' => $file['path'],
                        'file_name' => $file['name'],
                        'file_size' => $file['size'],
                        'mime_type' => $this->getMimeType($file['type']),
                        'status' => 'pending',
                        'priority' => 5,
                        'metadata' => [
                            'synced_at' => now()->toIso8601String(),
                            'source' => 'sync_service'
                        ]
                    ]);
                    
                    $results['queued_for_upload']++;
                    
                    Log::info('Queued file for Telegram upload', [
                        'file_path' => $file['path']
                    ]);
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to queue file {$file['name']}: " . $e->getMessage();
                    Log::error('Failed to queue file for upload', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 2. Retry failed uploads
            foreach ($issues['upload_failed'] ?? [] as $file) {
                try {
                    $queueItem = BotUploadQueue::where('file_path', $file['path'])
                        ->where('status', 'failed')
                        ->first();
                    
                    if ($queueItem) {
                        $queueItem->update([
                            'status' => 'pending',
                            'retry_count' => 0,
                            'error_message' => null,
                            'metadata' => array_merge($queueItem->metadata ?? [], [
                                'retried_at' => now()->toIso8601String(),
                                'retried_by' => 'sync_service'
                            ])
                        ]);
                        
                        $results['retried_failed']++;
                        
                        Log::info('Retried failed upload', [
                            'file_path' => $file['path']
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to retry upload for {$file['name']}: " . $e->getMessage();
                    Log::error('Failed to retry upload', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 3. Queue recently modified files for re-upload
            foreach ($issues['recently_modified'] ?? [] as $file) {
                try {
                    // Check if already in queue
                    $existingQueue = BotUploadQueue::where('file_path', $file['path'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->first();
                    
                    if (!$existingQueue) {
                        BotUploadQueue::create([
                            'file_path' => $file['path'],
                            'file_name' => $file['name'],
                            'file_size' => $file['size'],
                            'mime_type' => $this->getMimeType(pathinfo($file['name'], PATHINFO_EXTENSION)),
                            'status' => 'pending',
                            'priority' => 3, // Higher priority for modified files
                            'metadata' => [
                                'reason' => 'file_modified',
                                'modified_at' => $file['modified'],
                                'previous_upload' => $file['uploaded'],
                                'synced_at' => now()->toIso8601String()
                            ]
                        ]);
                        
                        $results['queued_for_upload']++;
                        
                        Log::info('Queued modified file for re-upload', [
                            'file_path' => $file['path']
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to queue modified file {$file['name']}: " . $e->getMessage();
                    Log::error('Failed to queue modified file', [
                        'file' => $file,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 4. Clean orphaned upload records (optional)
            if ($options['clean_orphaned'] ?? false) {
                foreach ($issues['orphaned_uploads'] ?? [] as $orphaned) {
                    try {
                        $activity = BotActivity::find($orphaned['id']);
                        if ($activity) {
                            // Mark as orphaned instead of deleting
                            $activity->update([
                                'status' => 'orphaned',
                                'metadata' => array_merge($activity->metadata ?? [], [
                                    'marked_orphaned_at' => now()->toIso8601String(),
                                    'reason' => 'file_not_found_in_storage'
                                ])
                            ]);
                            
                            $results['cleaned_orphaned']++;
                            
                            Log::info('Marked orphaned upload record', [
                                'activity_id' => $orphaned['id'],
                                'file_name' => $orphaned['name']
                            ]);
                        }
                        
                    } catch (\Exception $e) {
                        $results['errors'][] = "Failed to clean orphaned record {$orphaned['id']}: " . $e->getMessage();
                        Log::error('Failed to clean orphaned upload', [
                            'orphaned' => $orphaned,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            // Process the queue immediately if requested
            if ($options['process_queue'] ?? false) {
                try {
                    $fileProcessingService = app(FileProcessingService::class);
                    $processed = $fileProcessingService->processQueuedUploads();
                    $results['processed_immediately'] = $processed;
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to process queue: " . $e->getMessage();
                }
            }
            
            // Log sync summary
            Log::info('Telegram storage sync completed', [
                'results' => $results
            ]);
            
            return [
                'success' => true,
                'message' => 'Synchronization completed successfully',
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Telegram storage sync failed', [
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
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = str_replace('\\', '/', $file->getPathname());
                    $storageBasePath = str_replace('\\', '/', storage_path('app/'));
                    $relativeFilePath = str_replace($storageBasePath, '', $filePath);
                    
                    $files[$filePath] = [
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                        'extension' => strtolower($file->getExtension())
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error scanning storage files: ' . $e->getMessage());
        }
        
        return $files;
    }
    
    /**
     * Get MIME type from file extension
     */
    private function getMimeType($extension): string
    {
        $mimeTypes = [
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            
            // Spreadsheets
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            
            // Videos
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
    
    /**
     * Format time difference to human readable
     */
    private function formatTimeDiff($seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } elseif ($seconds < 86400) {
            return round($seconds / 3600) . ' hours';
        } else {
            return round($seconds / 86400) . ' days';
        }
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