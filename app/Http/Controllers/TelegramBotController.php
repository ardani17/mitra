<?php

namespace App\Http\Controllers;

use App\Models\BotConfiguration;
use App\Models\BotActivity;
use App\Models\BotUserSession;
use App\Models\BotUploadQueue;
use App\Services\Telegram\TelegramService;
use App\Services\Telegram\TelegramStorageSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    protected $telegramService;
    protected $telegramSyncService;

    public function __construct()
    {
        // Middleware akan diset di route level karena constructor middleware kadang bermasalah
        $this->telegramService = app(TelegramService::class);
        $this->telegramSyncService = app(TelegramStorageSyncService::class);
    }

    /**
     * Show bot configuration page
     */
    public function config()
    {
        $config = BotConfiguration::first();
        
        // Get webhook info if config exists
        $webhookInfo = null;
        if ($config && $config->is_active) {
            try {
                $webhookInfo = $this->telegramService->getWebhookInfo();
            } catch (\Exception $e) {
                Log::error('Failed to get webhook info: ' . $e->getMessage());
            }
        }
        
        return view('telegram-bot.config', compact('config', 'webhookInfo'));
    }

    /**
     * Save bot configuration
     */
    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'bot_name' => 'required|string|max:255',
            'bot_token' => 'required|string|max:255',
            'bot_username' => 'nullable|string|max:100',
            'server_host' => 'required|string|max:100',
            'server_port' => 'required|integer|min:1|max:65535',
            'bot_api_base_path' => 'required|string|max:500',
            'bot_api_temp_path' => 'nullable|string|max:500',
            'bot_api_documents_path' => 'nullable|string|max:500',
            'bot_api_photos_path' => 'nullable|string|max:500',
            'bot_api_videos_path' => 'nullable|string|max:500',
            'use_local_server' => 'boolean',
            'max_file_size_mb' => 'required|integer|min:1|max:2000',
            'auto_cleanup' => 'boolean',
            'cleanup_after_hours' => 'required_if:auto_cleanup,true|integer|min:1|max:168',
            'is_active' => 'boolean',
        ]);
        
        $config = BotConfiguration::firstOrNew();
        $config->fill($validated);
        
        // Set webhook URL
        $config->webhook_url = route('telegram.webhook');
        
        $config->save();
        
        // Setup webhook if active
        if ($config->is_active) {
            try {
                $result = $this->telegramService->setWebhook();
                if ($result && $result['ok']) {
                    return redirect()->route('telegram-bot.config')
                        ->with('success', 'Bot configuration saved and webhook set successfully!');
                }
            } catch (\Exception $e) {
                Log::error('Failed to set webhook: ' . $e->getMessage());
                return redirect()->route('telegram-bot.config')
                    ->with('warning', 'Configuration saved but failed to set webhook: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('telegram-bot.config')
            ->with('success', 'Bot configuration saved successfully!');
    }

    /**
     * Test bot connection
     */
    public function testConnection()
    {
        try {
            $result = $this->telegramService->getMe();
            
            if ($result && $result['ok']) {
                return response()->json([
                    'success' => true,
                    'bot_info' => $result['result']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to bot'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set webhook
     */
    public function setWebhook()
    {
        try {
            $result = $this->telegramService->setWebhook();
            
            if ($result && $result['ok']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook set successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to set webhook'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook()
    {
        try {
            $result = $this->telegramService->deleteWebhook();
            
            if ($result && $result['ok']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete webhook'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show file explorer page with unified project files
     */
    public function explorer(Request $request)
    {
        $search = $request->get('search', '');
        $currentPath = $request->get('path', '');
        
        // Base path for all project files
        $basePath = storage_path('app/proyek');
        
        // Ensure the base path exists
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
        
        // Build the full path
        $fullPath = $basePath;
        if ($currentPath) {
            $fullPath = $basePath . '/' . $currentPath;
        }
        
        // Security check - prevent directory traversal
        $realPath = realpath($fullPath);
        $realBasePath = realpath($basePath);
        if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
            $fullPath = $basePath;
            $currentPath = '';
        }
        
        // Get files and directories
        $items = [];
        if (is_dir($fullPath)) {
            $files = scandir($fullPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $itemPath = $fullPath . '/' . $file;
                $relativePath = $currentPath ? $currentPath . '/' . $file : $file;
                
                // Apply search filter
                if ($search && stripos($file, $search) === false) {
                    continue;
                }
                
                $item = [
                    'name' => $file,
                    'path' => $relativePath,
                    'is_dir' => is_dir($itemPath),
                    'size' => is_dir($itemPath) ? null : filesize($itemPath),
                    'modified' => filemtime($itemPath),
                    'extension' => is_dir($itemPath) ? null : pathinfo($file, PATHINFO_EXTENSION),
                ];
                
                // Get file type icon
                if ($item['is_dir']) {
                    $item['icon'] = 'folder';
                    $item['color'] = 'yellow-500';
                } else {
                    $ext = strtolower($item['extension']);
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                        $item['icon'] = 'image';
                        $item['color'] = 'green-500';
                    } elseif (in_array($ext, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'])) {
                        $item['icon'] = 'film';
                        $item['color'] = 'purple-500';
                    } elseif (in_array($ext, ['pdf'])) {
                        $item['icon'] = 'document-text';
                        $item['color'] = 'red-500';
                    } elseif (in_array($ext, ['doc', 'docx'])) {
                        $item['icon'] = 'document';
                        $item['color'] = 'blue-500';
                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                        $item['icon'] = 'table';
                        $item['color'] = 'green-600';
                    } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                        $item['icon'] = 'archive';
                        $item['color'] = 'gray-500';
                    } else {
                        $item['icon'] = 'document';
                        $item['color'] = 'gray-400';
                    }
                }
                
                $items[] = $item;
            }
        }
        
        // Sort items - directories first, then files
        usort($items, function($a, $b) {
            if ($a['is_dir'] && !$b['is_dir']) return -1;
            if (!$a['is_dir'] && $b['is_dir']) return 1;
            return strcasecmp($a['name'], $b['name']);
        });
        
        // Build breadcrumb
        $breadcrumb = [];
        if ($currentPath) {
            $parts = explode('/', $currentPath);
            $accumulated = '';
            foreach ($parts as $part) {
                $accumulated = $accumulated ? $accumulated . '/' . $part : $part;
                $breadcrumb[] = [
                    'name' => $part,
                    'path' => $accumulated
                ];
            }
        }
        
        // Get recent uploads from bot activity
        $recentUploads = BotActivity::with('project')
            ->where('message_type', 'file')
            ->latest()
            ->limit(5)
            ->get();
        
        return view('telegram-bot.explorer', compact('items', 'currentPath', 'breadcrumb', 'search', 'recentUploads'));
    }

    /**
     * Show bot activity page
     */
    public function activity()
    {
        $activities = BotActivity::with('project')
            ->latest()
            ->paginate(50);
            
        $stats = [
            'total' => BotActivity::count(),
            'today' => BotActivity::today()->count(),
            'this_week' => BotActivity::thisWeek()->count(),
            'this_month' => BotActivity::thisMonth()->count(),
            'total_files' => BotActivity::fileUploads()->count(),
            'total_commands' => BotActivity::commands()->count(),
            'success_rate' => $this->calculateSuccessRate(),
            'active_users' => BotUserSession::where('is_active', true)->count(),
        ];
        
        $uploadQueue = BotUploadQueue::getStatistics();
        
        return view('telegram-bot.activity', compact('activities', 'stats', 'uploadQueue'));
    }

    /**
     * Get activity data for charts
     */
    public function activityData(Request $request)
    {
        $period = $request->get('period', 'week');
        
        $query = BotActivity::query();
        
        switch ($period) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }
        
        $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count, message_type')
            ->groupBy('date', 'message_type')
            ->get();
        
        return response()->json($data);
    }

    /**
     * Manage allowed users
     */
    public function allowedUsers()
    {
        $config = BotConfiguration::first();
        $allowedUsers = $config ? $config->allowed_users : [];
        
        return view('telegram-bot.allowed-users', compact('allowedUsers'));
    }

    /**
     * Add allowed user
     */
    public function addAllowedUser(Request $request)
    {
        $validated = $request->validate([
            'telegram_id' => 'required|integer',
            'username' => 'nullable|string|max:100',
        ]);
        
        $config = BotConfiguration::firstOrFail();
        $config->addAllowedUser($validated['telegram_id'], $validated['username']);
        
        return response()->json([
            'success' => true,
            'message' => 'User added successfully'
        ]);
    }

    /**
     * Remove allowed user
     */
    public function removeAllowedUser(Request $request)
    {
        $validated = $request->validate([
            'telegram_id' => 'required|integer',
        ]);
        
        $config = BotConfiguration::firstOrFail();
        $config->removeAllowedUser($validated['telegram_id']);
        
        return response()->json([
            'success' => true,
            'message' => 'User removed successfully'
        ]);
    }

    /**
     * Process upload queue
     */
    public function processQueue()
    {
        $fileProcessingService = app(\App\Services\Telegram\FileProcessingService::class);
        $processed = $fileProcessingService->processQueuedUploads();
        
        return response()->json([
            'success' => true,
            'processed' => $processed,
            'message' => "Processed {$processed} files from queue"
        ]);
    }

    /**
     * Retry failed uploads
     */
    public function retryFailed()
    {
        $retried = BotUploadQueue::retryAllFailed();
        
        return response()->json([
            'success' => true,
            'retried' => $retried,
            'message' => "Retrying {$retried} failed uploads"
        ]);
    }

    /**
     * Clean old data
     */
    public function cleanOldData()
    {
        $commandHistory = \App\Models\BotCommandHistory::cleanOldHistory(30);
        $uploadQueue = BotUploadQueue::cleanOldCompleted(7);
        
        $fileProcessingService = app(\App\Services\Telegram\FileProcessingService::class);
        $cleanedFiles = $fileProcessingService->cleanupOldFiles();
        
        return response()->json([
            'success' => true,
            'cleaned' => [
                'command_history' => $commandHistory,
                'upload_queue' => $uploadQueue,
                'bot_api_files' => $cleanedFiles,
            ],
            'message' => 'Old data cleaned successfully'
        ]);
    }

    /**
     * Get storage statistics
     */
    public function storageStats()
    {
        $fileProcessingService = app(\App\Services\Telegram\FileProcessingService::class);
        $stats = $fileProcessingService->getStorageStatistics();
        
        return response()->json($stats);
    }

    /**
     * Download file from explorer
     */
    public function downloadFile(Request $request)
    {
        $filePath = $request->get('path');
        if (!$filePath) {
            abort(404);
        }
        
        // Base path for all project files
        $basePath = storage_path('app/proyek');
        $fullPath = $basePath . '/' . $filePath;
        
        // Security check - prevent directory traversal
        $realPath = realpath($fullPath);
        $realBasePath = realpath($basePath);
        if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
            abort(403, 'Access denied');
        }
        
        if (!file_exists($fullPath) || is_dir($fullPath)) {
            abort(404);
        }
        
        return response()->download($fullPath);
    }
    
    /**
     * Search files recursively
     */
    public function searchFiles(Request $request)
    {
        $search = $request->get('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $basePath = storage_path('app/proyek');
        $results = [];
        
        $this->searchDirectory($basePath, '', $search, $results, 50);
        
        return response()->json($results);
    }
    
    /**
     * Helper method to search directory recursively
     */
    private function searchDirectory($basePath, $relativePath, $search, &$results, $maxResults)
    {
        if (count($results) >= $maxResults) {
            return;
        }
        
        $fullPath = $basePath . ($relativePath ? '/' . $relativePath : '');
        
        if (!is_dir($fullPath)) {
            return;
        }
        
        $files = scandir($fullPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (count($results) >= $maxResults) break;
            
            $itemPath = $fullPath . '/' . $file;
            $itemRelativePath = $relativePath ? $relativePath . '/' . $file : $file;
            
            // Check if filename matches search
            if (stripos($file, $search) !== false) {
                $results[] = [
                    'name' => $file,
                    'path' => $itemRelativePath,
                    'is_dir' => is_dir($itemPath),
                    'size' => is_dir($itemPath) ? null : filesize($itemPath),
                    'modified' => date('Y-m-d H:i:s', filemtime($itemPath)),
                ];
            }
            
            // Recursively search subdirectories
            if (is_dir($itemPath)) {
                $this->searchDirectory($basePath, $itemRelativePath, $search, $results, $maxResults);
            }
        }
    }
    
    /**
     * Rename file or folder
     */
    public function renameItem(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
            'new_name' => 'required|string|max:255',
        ]);
        
        $basePath = storage_path('app/proyek');
        $oldPath = $basePath . '/' . $validated['path'];
        
        // Security check
        $realPath = realpath($oldPath);
        $realBasePath = realpath($basePath);
        if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
            return response()->json(['success' => false, 'message' => 'Invalid path'], 403);
        }
        
        if (!file_exists($oldPath)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }
        
        // Build new path
        $pathInfo = pathinfo($validated['path']);
        $newRelativePath = ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $validated['new_name'];
        $newPath = $basePath . '/' . $newRelativePath;
        
        // Check if new name already exists
        if (file_exists($newPath)) {
            return response()->json(['success' => false, 'message' => 'A file with this name already exists'], 409);
        }
        
        // Rename the file/folder
        if (rename($oldPath, $newPath)) {
            return response()->json([
                'success' => true,
                'message' => 'Item renamed successfully',
                'new_path' => $newRelativePath
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Failed to rename item'], 500);
    }
    
    /**
     * Delete file or folder
     */
    public function deleteItem(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);
        
        $basePath = storage_path('app/proyek');
        $fullPath = $basePath . '/' . $validated['path'];
        
        // Security check
        $realPath = realpath($fullPath);
        $realBasePath = realpath($basePath);
        if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
            return response()->json(['success' => false, 'message' => 'Invalid path'], 403);
        }
        
        if (!file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }
        
        // Delete file or directory
        try {
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete item: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Copy file or folder
     */
    public function copyItem(Request $request)
    {
        $validated = $request->validate([
            'source_path' => 'required|string',
            'dest_path' => 'required|string',
        ]);
        
        $basePath = storage_path('app/proyek');
        $sourcePath = $basePath . '/' . $validated['source_path'];
        $destPath = $basePath . '/' . $validated['dest_path'];
        
        // Security checks
        $realSourcePath = realpath($sourcePath);
        $realBasePath = realpath($basePath);
        if ($realSourcePath === false || strpos($realSourcePath, $realBasePath) !== 0) {
            return response()->json(['success' => false, 'message' => 'Invalid source path'], 403);
        }
        
        // Check destination parent directory
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        if (!file_exists($sourcePath)) {
            return response()->json(['success' => false, 'message' => 'Source file not found'], 404);
        }
        
        // Check if destination already exists
        if (file_exists($destPath)) {
            // Add number suffix to make it unique
            $info = pathinfo($destPath);
            $counter = 1;
            do {
                $destPath = $info['dirname'] . '/' . $info['filename'] . '_copy' . $counter .
                           (isset($info['extension']) ? '.' . $info['extension'] : '');
                $counter++;
            } while (file_exists($destPath));
        }
        
        // Copy file or directory
        try {
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Item copied successfully',
                'new_path' => str_replace($basePath . '/', '', $destPath)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to copy item: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Move file or folder
     */
    public function moveItem(Request $request)
    {
        $validated = $request->validate([
            'source_path' => 'required|string',
            'dest_path' => 'required|string',
        ]);
        
        $basePath = storage_path('app/proyek');
        $sourcePath = $basePath . '/' . $validated['source_path'];
        $destPath = $basePath . '/' . $validated['dest_path'];
        
        // Security checks
        $realSourcePath = realpath($sourcePath);
        $realBasePath = realpath($basePath);
        if ($realSourcePath === false || strpos($realSourcePath, $realBasePath) !== 0) {
            return response()->json(['success' => false, 'message' => 'Invalid source path'], 403);
        }
        
        // Check destination parent directory
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        if (!file_exists($sourcePath)) {
            return response()->json(['success' => false, 'message' => 'Source file not found'], 404);
        }
        
        // Check if destination already exists
        if (file_exists($destPath)) {
            return response()->json(['success' => false, 'message' => 'Destination already exists'], 409);
        }
        
        // Move file or directory
        if (rename($sourcePath, $destPath)) {
            return response()->json([
                'success' => true,
                'message' => 'Item moved successfully',
                'new_path' => str_replace($basePath . '/', '', $destPath)
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Failed to move item'], 500);
    }
    
    /**
     * Create new folder
     */
    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);
        
        $basePath = storage_path('app/proyek');
        $newFolderPath = $basePath . '/' . $validated['path'];
        
        // Security check - ensure parent directory exists
        $parentDir = dirname($newFolderPath);
        if (!is_dir($parentDir)) {
            // Create parent directories if they don't exist
            mkdir($parentDir, 0755, true);
        }
        
        // Check if folder already exists
        if (file_exists($newFolderPath)) {
            return response()->json(['success' => false, 'message' => 'Folder already exists'], 409);
        }
        
        // Create the folder
        if (mkdir($newFolderPath, 0755, true)) {
            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully',
                'path' => $validated['path']
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Failed to create folder'], 500);
    }
    
    /**
     * Helper method to delete directory recursively
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    /**
     * Helper method to copy directory recursively
     */
    private function copyDirectory($src, $dst)
    {
        if (!is_dir($src)) {
            return;
        }
        
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        $files = array_diff(scandir($src), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
    }
    
    /**
     * Get folder tree structure for folder selector
     */
    public function getFolderTree(Request $request)
    {
        $basePath = storage_path('app/proyek');
        $excludePath = $request->get('exclude', '');
        
        // Ensure the base path exists
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
        
        $tree = $this->buildFolderTree($basePath, '', $excludePath);
        
        return response()->json([
            'success' => true,
            'folders' => $tree
        ]);
    }
    
    /**
     * Get folder tree structure for folder selector (lazy loading version)
     */
    public function getFolderTreeLazy(Request $request)
    {
        $basePath = storage_path('app/proyek');
        $parentPath = $request->get('parent', '');
        $excludePath = $request->get('exclude', '');
        
        // Ensure the base path exists
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
        
        // Build the full path
        $fullPath = $basePath;
        if ($parentPath) {
            $fullPath = $basePath . '/' . $parentPath;
        }
        
        // Security check - prevent directory traversal
        $realPath = realpath($fullPath);
        $realBasePath = realpath($basePath);
        if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
                'folders' => []
            ], 403);
        }
        
        $folders = $this->getFoldersInDirectory($fullPath, $parentPath, $excludePath);
        
        return response()->json([
            'success' => true,
            'folders' => $folders
        ]);
    }
    
    /**
     * Get folders in a specific directory (for lazy loading)
     */
    private function getFoldersInDirectory($fullPath, $parentPath = '', $excludePath = '')
    {
        $folders = [];
        
        if (!is_dir($fullPath)) {
            return $folders;
        }
        
        try {
            $items = scandir($fullPath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $itemPath = $fullPath . '/' . $item;
                $itemRelativePath = $parentPath ? $parentPath . '/' . $item : $item;
                
                // Skip if this is the excluded path (for move operations)
                if ($excludePath && ($itemRelativePath === $excludePath || strpos($itemRelativePath, $excludePath . '/') === 0)) {
                    continue;
                }
                
                if (is_dir($itemPath)) {
                    // Check if this folder has subfolders
                    $hasChildren = $this->hasSubfolders($itemPath);
                    $childCount = $hasChildren ? $this->countSubfolders($itemPath) : 0;
                    
                    $folders[] = [
                        'name' => $item,
                        'path' => $itemRelativePath,
                        'hasChildren' => $hasChildren,
                        'childCount' => $childCount
                    ];
                }
            }
            
            // Sort folders alphabetically
            usort($folders, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        } catch (\Exception $e) {
            Log::error('Error reading directory: ' . $e->getMessage());
        }
        
        return $folders;
    }
    
    /**
     * Check if a directory has subdirectories
     */
    private function hasSubfolders($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (is_dir($path . '/' . $item)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Count subdirectories in a directory
     */
    private function countSubfolders($path)
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $count = 0;
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (is_dir($path . '/' . $item)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Build folder tree recursively (kept for backward compatibility)
     */
    private function buildFolderTree($basePath, $relativePath = '', $excludePath = '', $maxDepth = 20, $currentDepth = 0)
    {
        // Increase max depth to 20 levels (was 5)
        if ($currentDepth >= $maxDepth) {
            return [];
        }
        
        $fullPath = $basePath . ($relativePath ? '/' . $relativePath : '');
        $folders = [];
        
        if (!is_dir($fullPath)) {
            return $folders;
        }
        
        $items = scandir($fullPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $itemPath = $fullPath . '/' . $item;
            $itemRelativePath = $relativePath ? $relativePath . '/' . $item : $item;
            
            // Skip if this is the excluded path
            if ($excludePath && $itemRelativePath === $excludePath) {
                continue;
            }
            
            if (is_dir($itemPath)) {
                $children = $this->buildFolderTree($basePath, $itemRelativePath, $excludePath, $maxDepth, $currentDepth + 1);
                $folders[] = [
                    'name' => $item,
                    'path' => $itemRelativePath,
                    'hasChildren' => count($children) > 0,
                    'children' => $children
                ];
            }
        }
        
        // Sort folders alphabetically
        usort($folders, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        return $folders;
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate()
    {
        $total = BotActivity::count();
        if ($total === 0) return 0;
        
        $successful = BotActivity::successful()->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Upload files to the storage
     */
    public function uploadFiles(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:51200', // Max 50MB per file
            'path' => 'nullable|string',
        ]);

        $basePath = storage_path('app/proyek');
        $uploadPath = $basePath;
        
        // If a specific path is provided, use it
        if (!empty($validated['path'])) {
            $uploadPath = $basePath . '/' . $validated['path'];
            
            // Security check
            $realPath = realpath(dirname($uploadPath));
            $realBasePath = realpath($basePath);
            if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid upload path'
                ], 403);
            }
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
        }

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $originalName = $file->getClientOriginalName();
                $fileName = $originalName;
                
                // Get file info BEFORE moving the file
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
                
                // Handle duplicate filenames
                $counter = 1;
                while (file_exists($uploadPath . '/' . $fileName)) {
                    $info = pathinfo($originalName);
                    $fileName = $info['filename'] . '_' . $counter . '.' . $info['extension'];
                    $counter++;
                }
                
                // Move the uploaded file
                $file->move($uploadPath, $fileName);
                
                $relativePath = $validated['path'] ? $validated['path'] . '/' . $fileName : $fileName;
                
                $uploadedFiles[] = [
                    'name' => $fileName,
                    'path' => $relativePath,
                    'size' => $fileSize,  // Use the stored size
                    'type' => $mimeType,  // Use the stored mime type
                ];
                
                // Log the upload activity (optional - only if we want to track it)
                try {
                    BotActivity::create([
                        'telegram_user_id' => 0, // Use 0 for web uploads (not from Telegram)
                        'username' => auth()->user()->name ?? 'Web User',
                        'message_type' => 'file',
                        'message_text' => 'File uploaded via web interface',
                        'file_info' => [
                            'file_name' => $fileName,
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                        ],
                        'status' => 'success',
                    ]);
                } catch (\Exception $logError) {
                    // Log error but don't fail the upload
                    Log::warning('Could not log upload activity: ' . $logError->getMessage());
                }
                
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
                
                Log::error('File upload failed: ' . $e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some files failed to upload',
                'uploaded' => $uploadedFiles,
                'errors' => $errors
            ], 207); // 207 Multi-Status
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
            'files' => $uploadedFiles
        ]);
    }

    /**
     * Check synchronization status between local storage and Telegram bot
     */
    public function checkSyncStatus(Request $request)
    {
        try {
            // Use the new sync service to check status
            $syncStatus = $this->telegramSyncService->checkSyncStatus();
            
            // Get additional statistics
            $basePath = storage_path('app/proyek');
            $localStats = $this->getDirectoryStats($basePath);
            
            // Get bot API storage statistics
            $fileProcessingService = app(\App\Services\Telegram\FileProcessingService::class);
            $botApiStats = $fileProcessingService->getStorageStatistics();
            
            // Check last sync time from bot activity
            $lastSync = BotActivity::where('message_type', 'file')
                ->where('status', 'success')
                ->latest()
                ->first();
            
            $lastSyncTime = $lastSync ? $lastSync->created_at : null;
            $timeSinceSync = $lastSyncTime ? now()->diffInMinutes($lastSyncTime) : null;
            
            return response()->json([
                'success' => true,
                'is_synced' => $syncStatus['is_synced'],
                'issues' => $syncStatus['issues'],
                'stats' => array_merge($syncStatus['stats'], [
                    'local' => [
                        'total_files' => $localStats['files'],
                        'total_folders' => $localStats['folders'],
                        'total_size' => $localStats['size'],
                        'total_size_formatted' => $this->formatBytes($localStats['size']),
                    ],
                    'bot_api' => [
                        'total_size' => $botApiStats['total_size'] ?? 0,
                        'total_size_formatted' => $botApiStats['total_size_formatted'] ?? '0 B',
                        'file_count' => $botApiStats['file_count'] ?? 0,
                    ],
                    'last_sync' => $lastSyncTime ? $lastSyncTime->format('Y-m-d H:i:s') : null,
                    'time_since_sync' => $timeSinceSync,
                ])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check sync status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize storage with Telegram bot
     */
    public function syncStorage(Request $request)
    {
        try {
            // Get sync options from request
            $options = [
                'clean_orphaned' => $request->input('clean_orphaned', false),
                'process_queue' => $request->input('process_queue', true),
            ];
            
            // Use the new sync service to perform synchronization
            $result = $this->telegramSyncService->performSync($options);
            
            // Log sync activity
            try {
                BotActivity::create([
                    'telegram_user_id' => 0, // Use 0 for web actions
                    'username' => auth()->user()->name ?? 'Web User',
                    'message_type' => 'command',
                    'message_text' => '/sync',
                    'command' => 'sync',
                    'status' => $result['success'] ? 'success' : 'failed',
                    'response_text' => $result['message'],
                    'metadata' => $result['results']
                ]);
            } catch (\Exception $logError) {
                Log::warning('Could not log sync activity: ' . $logError->getMessage());
            }
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'results' => $result['results']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'results' => $result['results']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Storage sync failed: ' . $e->getMessage());
            
            // Log failed sync
            try {
                BotActivity::create([
                    'telegram_user_id' => 0, // Use 0 for web actions
                    'username' => auth()->user()->name ?? 'Web User',
                    'message_type' => 'command',
                    'message_text' => '/sync',
                    'command' => 'sync',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            } catch (\Exception $logError) {
                Log::warning('Could not log failed sync: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Storage synchronization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get directory statistics
     */
    private function getDirectoryStats($path)
    {
        $stats = [
            'files' => 0,
            'folders' => 0,
            'size' => 0,
        ];
        
        if (!is_dir($path)) {
            return $stats;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $stats['folders']++;
            } else {
                $stats['files']++;
                $stats['size'] += $item->getSize();
            }
        }
        
        return $stats;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}