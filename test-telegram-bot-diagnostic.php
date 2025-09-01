<?php
/**
 * Telegram Bot Explorer - Diagnostic Script
 * Run this script to test the upload and sync functionality
 * 
 * Usage: php test-telegram-bot-diagnostic.php
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

echo "===========================================\n";
echo "Telegram Bot Explorer - Diagnostic Script\n";
echo "===========================================\n\n";

// 1. Check storage directory
echo "1. Checking storage directory...\n";
$storagePath = __DIR__ . '/storage/app/proyek';
if (file_exists($storagePath)) {
    echo "   ✓ Storage directory exists: $storagePath\n";
    
    // Check permissions
    if (is_writable($storagePath)) {
        echo "   ✓ Storage directory is writable\n";
    } else {
        echo "   ✗ Storage directory is NOT writable - this will cause upload failures\n";
        echo "   Fix: chmod 755 $storagePath\n";
    }
} else {
    echo "   ✗ Storage directory does not exist: $storagePath\n";
    echo "   Creating directory...\n";
    if (mkdir($storagePath, 0755, true)) {
        echo "   ✓ Directory created successfully\n";
    } else {
        echo "   ✗ Failed to create directory\n";
    }
}

// 2. Check database tables
echo "\n2. Checking database tables...\n";
$requiredTables = [
    'bot_configurations',
    'bot_activities', 
    'bot_user_sessions',
    'bot_upload_queues'
];

// Note: This is a simplified check - in production you'd use Laravel's DB facade
foreach ($requiredTables as $table) {
    echo "   - Table '$table': Check manually in database\n";
}

// 3. Check routes
echo "\n3. Checking routes...\n";
$routes = [
    '/telegram-bot/explorer' => 'GET',
    '/telegram-bot/upload' => 'POST',
    '/telegram-bot/check-sync' => 'GET',
    '/telegram-bot/sync-storage' => 'POST'
];

echo "   Routes to verify:\n";
foreach ($routes as $route => $method) {
    echo "   - [$method] $route\n";
}
echo "   Run: php artisan route:list | grep telegram-bot\n";

// 4. Create test file for upload
echo "\n4. Creating test file for upload...\n";
$testFile = __DIR__ . '/test-upload-file.txt';
$testContent = "Test file created at " . date('Y-m-d H:i:s') . "\n";
$testContent .= "This file can be used to test the upload functionality.\n";

if (file_put_contents($testFile, $testContent)) {
    echo "   ✓ Test file created: $testFile\n";
    echo "   Use this file to test the upload functionality\n";
} else {
    echo "   ✗ Failed to create test file\n";
}

// 5. Check PHP configuration
echo "\n5. Checking PHP configuration...\n";
$uploadMaxSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$maxFileUploads = ini_get('max_file_uploads');

echo "   - upload_max_filesize: $uploadMaxSize\n";
echo "   - post_max_size: $postMaxSize\n";
echo "   - max_file_uploads: $maxFileUploads\n";

if (intval($uploadMaxSize) < 50) {
    echo "   ⚠ upload_max_filesize is less than 50M, large files may fail\n";
}

// 6. Test CSRF token generation
echo "\n6. Testing CSRF token...\n";
echo "   Note: CSRF token is handled by Laravel automatically\n";
echo "   Make sure @csrf is included in forms\n";

// 7. Summary and recommendations
echo "\n===========================================\n";
echo "SUMMARY & RECOMMENDATIONS\n";
echo "===========================================\n\n";

echo "To test the buttons:\n";
echo "1. Start Laravel server: php artisan serve\n";
echo "2. Navigate to: http://localhost:8000/telegram-bot/explorer\n";
echo "3. Login as a user with 'direktur' role\n";
echo "4. Test each button:\n";
echo "   - Upload: Select the test file created above\n";
echo "   - Refresh: Should reload the page\n";
echo "   - Cek Sinkronisasi: Should check sync status\n";
echo "\nIf upload fails, check:\n";
echo "   - Browser console for JavaScript errors\n";
echo "   - Network tab for failed requests\n";
echo "   - Laravel logs: storage/logs/laravel.log\n";
echo "\nCommon issues:\n";
echo "   - CSRF token mismatch: Clear cookies and cache\n";
echo "   - 419 error: Session expired, login again\n";
echo "   - 500 error: Check Laravel logs for details\n";

echo "\n===========================================\n";
echo "Diagnostic complete!\n";
echo "===========================================\n";