<?php

/**
 * Test script for Telegram Bot Sync functionality
 * Run this script to test the sync status and perform sync operations
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Telegram\TelegramStorageSyncService;
use App\Models\BotActivity;
use App\Models\BotUploadQueue;

echo "===========================================\n";
echo "Telegram Bot Sync Functionality Test\n";
echo "===========================================\n\n";

try {
    // Initialize the sync service
    $syncService = app(TelegramStorageSyncService::class);
    
    echo "1. Checking current sync status...\n";
    echo "-----------------------------------\n";
    
    $syncStatus = $syncService->checkSyncStatus();
    
    echo "Is Synced: " . ($syncStatus['is_synced'] ? 'Yes ✅' : 'No ⚠️') . "\n";
    echo "Total Issues: " . $syncStatus['stats']['total_issues'] . "\n\n";
    
    if ($syncStatus['stats']['total_issues'] > 0) {
        echo "Issues Found:\n";
        echo "- Not Uploaded: " . $syncStatus['stats']['not_uploaded'] . " files\n";
        echo "- Upload Failed: " . $syncStatus['stats']['upload_failed'] . " files\n";
        echo "- Upload Pending: " . $syncStatus['stats']['upload_pending'] . " files\n";
        echo "- Recently Modified: " . $syncStatus['stats']['recently_modified'] . " files\n";
        echo "- Orphaned Uploads: " . $syncStatus['stats']['orphaned_uploads'] . " records\n\n";
        
        // Show details of some issues
        if (!empty($syncStatus['issues']['not_uploaded'])) {
            echo "Files not uploaded (showing first 5):\n";
            foreach (array_slice($syncStatus['issues']['not_uploaded'], 0, 5) as $file) {
                echo "  - {$file['name']} ({$file['size_formatted']})\n";
            }
            echo "\n";
        }
        
        if (!empty($syncStatus['issues']['upload_failed'])) {
            echo "Failed uploads (showing first 5):\n";
            foreach (array_slice($syncStatus['issues']['upload_failed'], 0, 5) as $file) {
                echo "  - {$file['name']}: {$file['error']}\n";
            }
            echo "\n";
        }
    }
    
    // Test the sync operation (dry run)
    echo "2. Testing sync operation (dry run)...\n";
    echo "--------------------------------------\n";
    
    // Check if there are issues to sync
    if (!$syncStatus['is_synced']) {
        echo "Would perform the following actions:\n";
        
        if ($syncStatus['stats']['not_uploaded'] > 0) {
            echo "✅ Queue {$syncStatus['stats']['not_uploaded']} files for upload\n";
        }
        
        if ($syncStatus['stats']['upload_failed'] > 0) {
            echo "🔄 Retry {$syncStatus['stats']['upload_failed']} failed uploads\n";
        }
        
        if ($syncStatus['stats']['recently_modified'] > 0) {
            echo "📝 Re-upload {$syncStatus['stats']['recently_modified']} modified files\n";
        }
        
        if ($syncStatus['stats']['orphaned_uploads'] > 0) {
            echo "🧹 Clean {$syncStatus['stats']['orphaned_uploads']} orphaned records\n";
        }
        
        echo "\n";
        
        // Ask if user wants to perform actual sync
        echo "Do you want to perform the actual sync? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        
        if (trim($line) === 'yes') {
            echo "\n3. Performing actual sync...\n";
            echo "----------------------------\n";
            
            $syncResult = $syncService->performSync([
                'clean_orphaned' => true,
                'process_queue' => false // Don't process immediately in test
            ]);
            
            if ($syncResult['success']) {
                echo "✅ Sync completed successfully!\n\n";
                echo "Results:\n";
                echo "- Files queued for upload: " . $syncResult['results']['queued_for_upload'] . "\n";
                echo "- Failed uploads retried: " . $syncResult['results']['retried_failed'] . "\n";
                echo "- Orphaned records cleaned: " . $syncResult['results']['cleaned_orphaned'] . "\n";
                
                if (!empty($syncResult['results']['errors'])) {
                    echo "\nErrors encountered:\n";
                    foreach ($syncResult['results']['errors'] as $error) {
                        echo "  ⚠️ {$error}\n";
                    }
                }
            } else {
                echo "❌ Sync failed: " . $syncResult['message'] . "\n";
            }
        } else {
            echo "Sync operation cancelled.\n";
        }
        
        fclose($handle);
    } else {
        echo "✅ Everything is already synchronized!\n";
    }
    
    // Show queue statistics
    echo "\n4. Upload Queue Statistics\n";
    echo "--------------------------\n";
    
    $pendingCount = BotUploadQueue::where('status', 'pending')->count();
    $processingCount = BotUploadQueue::where('status', 'processing')->count();
    $failedCount = BotUploadQueue::where('status', 'failed')->count();
    $completedCount = BotUploadQueue::where('status', 'completed')->count();
    
    echo "Pending: {$pendingCount}\n";
    echo "Processing: {$processingCount}\n";
    echo "Failed: {$failedCount}\n";
    echo "Completed: {$completedCount}\n";
    
    // Show recent bot activities
    echo "\n5. Recent Bot Upload Activities\n";
    echo "--------------------------------\n";
    
    $recentActivities = BotActivity::where('message_type', 'file')
        ->latest()
        ->limit(5)
        ->get();
    
    if ($recentActivities->count() > 0) {
        foreach ($recentActivities as $activity) {
            $fileInfo = $activity->file_info;
            $fileName = $fileInfo['file_name'] ?? 'Unknown';
            $status = $activity->status;
            $time = $activity->created_at->format('Y-m-d H:i:s');
            
            $statusIcon = $status === 'success' ? '✅' : '❌';
            echo "{$statusIcon} {$fileName} - {$time}\n";
        }
    } else {
        echo "No recent upload activities found.\n";
    }
    
    echo "\n===========================================\n";
    echo "Test completed successfully!\n";
    echo "===========================================\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}