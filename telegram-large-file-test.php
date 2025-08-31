<?php
/**
 * Telegram Bot Large File Test - 200MB
 * Test upload file besar dengan local server
 */

// Configuration
$BOT_TOKEN = '8281280313:AAG0B4mu6tEzs3N0_BSO3VGatHov7t0klls';
$USE_LOCAL = true;
$LOCAL_SERVER_HOST = 'localhost';
$LOCAL_SERVER_PORT = '8081';

// API URL
$API_URL = $USE_LOCAL 
    ? "http://{$LOCAL_SERVER_HOST}:{$LOCAL_SERVER_PORT}/bot{$BOT_TOKEN}"
    : "https://api.telegram.org/bot{$BOT_TOKEN}";

echo "========================================\n";
echo "TELEGRAM BOT LARGE FILE TEST (200MB)\n";
echo "========================================\n";
echo "Server: LOCAL (localhost:8081)\n";
echo "Bot: @proyek_ardani_bot\n\n";

// Function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Function to create test file
function createTestFile($filename, $sizeMB) {
    echo "Creating {$sizeMB}MB test file: {$filename}\n";
    
    $handle = fopen($filename, 'w');
    if (!$handle) {
        echo "❌ Failed to create file\n";
        return false;
    }
    
    // Write in chunks of 1MB
    $chunkSize = 1024 * 1024; // 1MB
    $chunks = $sizeMB;
    
    // Create content with timestamp and info
    $header = "=== TELEGRAM BOT LOCAL SERVER TEST FILE ===\n";
    $header .= "Size: {$sizeMB} MB\n";
    $header .= "Created: " . date('Y-m-d H:i:s') . "\n";
    $header .= "Server: Local telegram-bot-api (2GB limit)\n";
    $header .= "Bot: @proyek_ardani_bot\n";
    $header .= str_repeat("=", 50) . "\n\n";
    fwrite($handle, $header);
    
    // Progress bar
    echo "Progress: ";
    for ($i = 0; $i < $chunks; $i++) {
        // Generate 1MB of data
        $data = str_repeat("TEST_DATA_CHUNK_{$i}_", 52428); // ~1MB when repeated
        fwrite($handle, $data);
        
        // Show progress
        if ($i % 10 == 0) {
            echo ".";
            flush();
        }
    }
    echo " Done!\n";
    
    fclose($handle);
    
    $actualSize = filesize($filename);
    echo "✅ File created: " . formatBytes($actualSize) . "\n";
    
    return true;
}

// Function to get chat ID
function getChatId($apiUrl) {
    echo "\n1. Getting chat ID...\n";
    
    $url = $apiUrl . '/getUpdates?limit=1';
    $response = @file_get_contents($url);
    
    if (!$response) {
        echo "❌ Failed to get updates\n";
        return null;
    }
    
    $data = json_decode($response, true);
    if ($data && $data['ok'] && !empty($data['result'])) {
        $update = end($data['result']);
        if (isset($update['message']['chat']['id'])) {
            $chatId = $update['message']['chat']['id'];
            echo "✅ Chat ID found: {$chatId}\n";
            return $chatId;
        }
    }
    
    echo "⚠️ No messages found. Send a message to @proyek_ardani_bot first!\n";
    return null;
}

// Main test
echo "========================================\n";
echo "STARTING LARGE FILE TEST\n";
echo "========================================\n\n";

// Get chat ID
$chatId = getChatId($API_URL);

if (!$chatId) {
    // Try to use a default chat ID if available
    echo "\nEnter your chat ID manually (or press Enter to skip): ";
    $input = trim(fgets(STDIN));
    if (!empty($input)) {
        $chatId = $input;
    } else {
        echo "❌ Cannot proceed without chat ID\n";
        echo "Please send a message to @proyek_ardani_bot first\n";
        exit(1);
    }
}

// Test different file sizes
$testSizes = [
    25,   // 25 MB (already tested)
    50,   // 50 MB
    100,  // 100 MB
    200,  // 200 MB
];

echo "\n2. Testing file uploads...\n";
echo "Available tests:\n";
foreach ($testSizes as $i => $size) {
    echo "  " . ($i + 1) . ". Test {$size}MB file\n";
}
echo "  5. Test all sizes\n";
echo "  0. Exit\n";

echo "\nSelect test (1-5, or 0 to exit): ";
$choice = trim(fgets(STDIN));

$sizesToTest = [];
switch ($choice) {
    case '1':
        $sizesToTest = [25];
        break;
    case '2':
        $sizesToTest = [50];
        break;
    case '3':
        $sizesToTest = [100];
        break;
    case '4':
        $sizesToTest = [200];
        break;
    case '5':
        $sizesToTest = $testSizes;
        break;
    case '0':
        echo "Exiting...\n";
        exit(0);
    default:
        echo "Invalid choice. Testing 200MB...\n";
        $sizesToTest = [200];
}

// Run tests
foreach ($sizesToTest as $sizeMB) {
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "TEST: {$sizeMB}MB File Upload\n";
    echo str_repeat("=", 40) . "\n\n";
    
    $filename = "test-{$sizeMB}mb.txt";
    
    // Create test file
    if (!createTestFile($filename, $sizeMB)) {
        continue;
    }
    
    // Upload file
    echo "\n3. Uploading to Telegram...\n";
    echo "   This may take a while for large files...\n";
    
    $startTime = microtime(true);
    
    // Use cURL for file upload
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $API_URL . '/sendDocument');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 600); // 10 minutes timeout for large files
    
    // Prepare file
    $postData = [
        'chat_id' => $chatId,
        'document' => new CURLFile($filename),
        'caption' => "{$sizeMB}MB Test File - Local Server Test\n" .
                    "✅ LOCAL SERVER supports up to 2GB!\n" .
                    "📅 " . date('Y-m-d H:i:s')
    ];
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    
    // Progress callback
    curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($sizeMB) {
        if ($upload_size > 0) {
            $percent = round(($uploaded / $upload_size) * 100);
            echo "\r   Upload progress: {$percent}% ";
            flush();
        }
    });
    curl_setopt($curl, CURLOPT_NOPROGRESS, false);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n";
    
    if ($response) {
        $result = json_decode($response, true);
        
        if ($result && $result['ok']) {
            echo "✅ SUCCESS! {$sizeMB}MB file uploaded!\n";
            echo "   Upload time: {$duration} seconds\n";
            echo "   Speed: " . round($sizeMB / $duration, 2) . " MB/s\n";
            
            if (isset($result['result']['document'])) {
                $doc = $result['result']['document'];
                echo "   File ID: {$doc['file_id']}\n";
                echo "   File size: " . formatBytes($doc['file_size']) . "\n";
            }
            
            echo "\n🎉 LOCAL SERVER ADVANTAGE:\n";
            echo "   • Official API limit: 50MB upload\n";
            echo "   • Your local server: 2GB upload!\n";
            echo "   • That's " . round(2000 / 50) . "x larger!\n";
        } else {
            echo "❌ Upload failed\n";
            if ($result && isset($result['description'])) {
                echo "   Error: {$result['description']}\n";
                
                if (strpos($result['description'], 'too big') !== false) {
                    echo "\n⚠️ File size limit reached!\n";
                    if (!$USE_LOCAL) {
                        echo "   You're using OFFICIAL API (50MB limit)\n";
                        echo "   Switch to LOCAL SERVER for 2GB limit!\n";
                    } else {
                        echo "   Even local server has limits\n";
                        echo "   Maximum: 2GB (2000MB)\n";
                    }
                }
            }
        }
    } else {
        echo "❌ Upload failed: {$error}\n";
        echo "   HTTP Code: {$httpCode}\n";
    }
    
    // Clean up test file
    echo "\n4. Cleaning up...\n";
    if (file_exists($filename)) {
        unlink($filename);
        echo "   Test file deleted\n";
    }
}

echo "\n========================================\n";
echo "TEST COMPLETED\n";
echo "========================================\n\n";

echo "Summary:\n";
echo "• Local server: localhost:8081\n";
echo "• Bot: @proyek_ardani_bot\n";
echo "• Max file size: 2GB (2000MB)\n";
echo "• Advantage over official API: 40x larger files!\n";

echo "\n";