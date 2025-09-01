<?php

namespace App\Services\Telegram;

use App\Models\BotConfiguration;
use App\Models\BotUploadQueue;
use App\Models\Project;
use App\Services\StorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileProcessingService
{
    protected $botConfig;
    protected $storageService;
    protected $telegramService;

    public function __construct(TelegramService $telegramService, StorageService $storageService)
    {
        $this->botConfig = BotConfiguration::getActive();
        $this->telegramService = $telegramService;
        $this->storageService = $storageService;
    }

    /**
     * Process uploaded file from Telegram
     */
    public function processUploadedFile($fileData, $projectId)
    {
        try {
            // 1. Get file from Bot API path
            $botApiPath = $this->getBotApiFilePath($fileData);
            
            // 2. Validate file exists
            if (!file_exists($botApiPath)) {
                // If file not in Bot API path, try to download from Telegram
                $fileContent = $this->downloadFromTelegram($fileData);
                if (!$fileContent) {
                    throw new \Exception("File not found in bot API path and failed to download: {$botApiPath}");
                }
                
                // Save to temp path first
                $tempPath = storage_path('app/temp/' . uniqid() . '_' . $fileData['file_name']);
                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }
                file_put_contents($tempPath, $fileContent);
                $botApiPath = $tempPath;
            }
            
            // 3. Get project and determine target folder
            $project = Project::findOrFail($projectId);
            $targetFolder = $this->determineTargetFolder($fileData['mime_type'] ?? null);
            $laravelPath = "proyek/{$project->code}/{$targetFolder}/";
            
            // 4. Sanitize and prepare file name
            $fileName = $this->sanitizeFileName($fileData['file_name'] ?? 'file_' . time());
            $fullLaravelPath = storage_path("app/{$laravelPath}{$fileName}");
            
            // Create directory if not exists
            if (!file_exists(dirname($fullLaravelPath))) {
                mkdir(dirname($fullLaravelPath), 0755, true);
            }
            
            // 5. Copy file to Laravel storage
            if (copy($botApiPath, $fullLaravelPath)) {
                // 6. Create document record using StorageService
                $document = $this->storageService->storeDocument(
                    $project,
                    $fileName,
                    $laravelPath . $fileName,
                    filesize($fullLaravelPath),
                    $fileData['mime_type'] ?? mime_content_type($fullLaravelPath)
                );
                
                // Add telegram metadata
                $document->telegram_file_id = $fileData['file_id'] ?? null;
                $document->telegram_original_path = $botApiPath;
                $document->upload_source = 'telegram';
                $document->save();
                
                // 7. Clean up temp file if it was downloaded
                if (isset($tempPath) && file_exists($tempPath)) {
                    unlink($tempPath);
                }
                
                // 8. Schedule cleanup of Bot API file if enabled
                if ($this->botConfig->auto_cleanup) {
                    $this->scheduleCleanup($botApiPath);
                }
                
                return $document;
            }
            
            throw new \Exception("Failed to copy file from bot API to storage");
            
        } catch (\Exception $e) {
            Log::error('File processing error', [
                'error' => $e->getMessage(),
                'file_data' => $fileData,
                'project_id' => $projectId
            ]);
            throw $e;
        }
    }

    /**
     * Queue file for processing
     */
    public function queueFileUpload($user, $chatId, $fileData, $projectId)
    {
        return BotUploadQueue::enqueue([
            'telegram_user_id' => $user['id'],
            'telegram_username' => $user['username'] ?? null,
            'chat_id' => $chatId,
            'telegram_file_id' => $fileData['file_id'],
            'file_name' => $fileData['file_name'] ?? 'unnamed_file',
            'mime_type' => $fileData['mime_type'] ?? null,
            'file_size' => $fileData['file_size'] ?? 0,
            'file_type' => $fileData['type'] ?? 'document',
            'bot_api_path' => $fileData['file_path'] ?? null,
            'project_id' => $projectId,
            'target_folder' => $this->determineTargetFolder($fileData['mime_type'] ?? null),
        ]);
    }

    /**
     * Process queued uploads
     */
    public function processQueuedUploads()
    {
        $processed = 0;
        
        while ($item = BotUploadQueue::getNextToProcess()) {
            $item->markAsProcessing();
            
            try {
                $fileData = [
                    'file_id' => $item->telegram_file_id,
                    'file_name' => $item->file_name,
                    'file_size' => $item->file_size,
                    'mime_type' => $item->mime_type,
                    'type' => $item->file_type,
                    'file_path' => $item->bot_api_path,
                ];
                
                $document = $this->processUploadedFile($fileData, $item->project_id);
                
                $item->markAsCompleted();
                $processed++;
                
            } catch (\Exception $e) {
                $item->markAsFailed($e->getMessage());
                
                // Check if can retry
                if ($item->canRetry()) {
                    Log::info("File upload failed but will retry", [
                        'queue_id' => $item->id,
                        'retry_count' => $item->retry_count
                    ]);
                } else {
                    Log::error("File upload failed and exceeded retry limit", [
                        'queue_id' => $item->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return $processed;
    }

    /**
     * Get Bot API file path
     */
    protected function getBotApiFilePath($fileData)
    {
        // If we have a direct path from Telegram
        if (isset($fileData['file_path']) && !empty($fileData['file_path'])) {
            return $this->botConfig->getBotApiFilePath(
                $fileData['type'] ?? 'document',
                basename($fileData['file_path'])
            );
        }
        
        // Build path based on file type and ID
        $fileName = $fileData['file_id'] . '_' . ($fileData['file_name'] ?? 'file');
        return $this->botConfig->getBotApiFilePath(
            $fileData['type'] ?? 'document',
            $fileName
        );
    }

    /**
     * Download file from Telegram if not in Bot API path
     */
    protected function downloadFromTelegram($fileData)
    {
        if (!isset($fileData['file_id'])) {
            return null;
        }
        
        // Get file info from Telegram
        $fileInfo = $this->telegramService->getFile($fileData['file_id']);
        
        if (!$fileInfo || !isset($fileInfo['file_path'])) {
            return null;
        }
        
        // Download file content
        return $this->telegramService->downloadFile($fileInfo['file_path']);
    }

    /**
     * Determine target folder based on file type
     */
    protected function determineTargetFolder($mimeType)
    {
        if (!$mimeType) {
            return 'dokumen/lainnya';
        }
        
        // Images
        if (str_starts_with($mimeType, 'image/')) {
            return 'gambar';
        }
        
        // Videos
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        // Documents by extension
        $documentTypes = [
            'application/pdf' => 'dokumen/pdf',
            'application/msword' => 'dokumen/word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'dokumen/word',
            'application/vnd.ms-excel' => 'dokumen/excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'dokumen/excel',
            'application/vnd.ms-powerpoint' => 'dokumen/powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'dokumen/powerpoint',
            'application/zip' => 'dokumen/arsip',
            'application/x-rar-compressed' => 'dokumen/arsip',
            'application/x-7z-compressed' => 'dokumen/arsip',
        ];
        
        if (isset($documentTypes[$mimeType])) {
            return $documentTypes[$mimeType];
        }
        
        // Default folder
        return 'dokumen/lainnya';
    }

    /**
     * Sanitize file name
     */
    protected function sanitizeFileName($fileName)
    {
        // Remove path traversal attempts
        $fileName = basename($fileName);
        
        // Replace spaces and special characters
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Remove multiple underscores
        $fileName = preg_replace('/_+/', '_', $fileName);
        
        // Add timestamp if file exists
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Ensure unique filename
        $counter = 1;
        $originalFileName = $fileName;
        while (Storage::exists("proyek/{$fileName}")) {
            $fileName = $nameWithoutExt . '_' . time() . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $fileName;
    }

    /**
     * Schedule cleanup of Bot API file
     */
    protected function scheduleCleanup($filePath)
    {
        // This would typically dispatch a job to clean up the file after X hours
        // For now, we'll just log it
        Log::info('File scheduled for cleanup', [
            'path' => $filePath,
            'cleanup_after_hours' => $this->botConfig->cleanup_after_hours
        ]);
        
        // In production, you would dispatch a job:
        // CleanupBotApiFile::dispatch($filePath)->delay(now()->addHours($this->botConfig->cleanup_after_hours));
    }

    /**
     * Clean up old Bot API files
     */
    public function cleanupOldFiles()
    {
        if (!$this->botConfig->auto_cleanup) {
            return 0;
        }
        
        $cutoffTime = now()->subHours($this->botConfig->cleanup_after_hours);
        $activities = \App\Models\BotActivity::where('message_type', 'file')
            ->where('status', 'success')
            ->where('created_at', '<', $cutoffTime)
            ->whereNotNull('telegram_original_path')
            ->get();
        
        $cleaned = 0;
        foreach ($activities as $activity) {
            $botApiPath = $activity->telegram_original_path;
            
            if (file_exists($botApiPath)) {
                unlink($botApiPath);
                Log::info("Cleaned up bot API file: {$botApiPath}");
                $cleaned++;
            }
            
            // Clear the path from database
            $activity->telegram_original_path = null;
            $activity->save();
        }
        
        return $cleaned;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStatistics()
    {
        $botApiPath = $this->botConfig->bot_api_base_path;
        $laravelPath = storage_path('app/proyek');
        
        return [
            'bot_api' => [
                'path' => $botApiPath,
                'used' => $this->getDirectorySize($botApiPath),
                'files' => $this->countFiles($botApiPath),
            ],
            'laravel' => [
                'path' => $laravelPath,
                'used' => $this->getDirectorySize($laravelPath),
                'files' => $this->countFiles($laravelPath),
            ],
            'pending_cleanup' => \App\Models\BotActivity::where('message_type', 'file')
                ->where('status', 'success')
                ->whereNotNull('telegram_original_path')
                ->count(),
        ];
    }

    /**
     * Get directory size
     */
    protected function getDirectorySize($path)
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }

    /**
     * Count files in directory
     */
    protected function countFiles($path)
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $count = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
}