<?php
/**
 * Quick Test Script for Telegram Bot with Local Server
 * Simple version for immediate testing
 */

// ===== CONFIGURATION =====
$BOT_TOKEN = '8281280313:AAG0B4mu6tEzs3N0_BSO3VGatHov7t0klls';  // <-- Ganti dengan token bot Anda!
$USE_LOCAL = true;  // true = local server, false = official API

// API URL
$API_URL = $USE_LOCAL 
    ? "http://localhost:8081/bot{$BOT_TOKEN}"
    : "https://api.telegram.org/bot{$BOT_TOKEN}";

echo "========================================\n";
echo "TELEGRAM BOT QUICK TEST\n";
echo "========================================\n";
echo "Server: " . ($USE_LOCAL ? "LOCAL (localhost:8081)" : "OFFICIAL") . "\n";
echo "Token: " . substr($BOT_TOKEN, 0, 10) . "...\n\n";

// Function to make API request
function apiRequest($method, $params = []) {
    global $API_URL;
    
    $url = $API_URL . '/' . $method;
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($params)
        ]
    ];
    
    $response = @file_get_contents($url, false, stream_context_create($options));
    return $response ? json_decode($response, true) : null;
}

// Test 1: Get Bot Info
echo "1. Testing Bot Connection...\n";
$me = apiRequest('getMe');

if ($me && $me['ok']) {
    echo "✅ Bot Connected: @{$me['result']['username']}\n";
    echo "   Name: {$me['result']['first_name']}\n";
    echo "   ID: {$me['result']['id']}\n\n";
} else {
    echo "❌ Failed to connect to bot!\n";
    echo "   Check your token and server status.\n";
    exit(1);
}

// Test 2: Get Recent Messages
echo "2. Getting Recent Messages...\n";
$updates = apiRequest('getUpdates', ['limit' => 5]);

if ($updates && $updates['ok']) {
    $messages = $updates['result'];
    
    if (empty($messages)) {
        echo "⚠️ No messages found.\n";
        echo "   Send a message to your bot first!\n";
        echo "   Bot username: @{$me['result']['username']}\n\n";
    } else {
        echo "✅ Found " . count($messages) . " message(s)\n";
        
        $lastMessage = end($messages);
        if (isset($lastMessage['message'])) {
            $msg = $lastMessage['message'];
            $chatId = $msg['chat']['id'];
            
            echo "   Last message from: " . ($msg['from']['username'] ?? 'Unknown') . "\n";
            echo "   Chat ID: {$chatId}\n";
            
            if (isset($msg['text'])) {
                echo "   Text: {$msg['text']}\n";
            }
            
            // Test 3: Send Reply
            echo "\n3. Sending Test Reply...\n";
            $send = apiRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => "🤖 Test Reply from " . ($USE_LOCAL ? "LOCAL Server" : "OFFICIAL API") . "\n" .
                         "✅ Connection successful!\n" .
                         "📅 " . date('Y-m-d H:i:s')
            ]);
            
            if ($send && $send['ok']) {
                echo "✅ Reply sent successfully!\n";
            } else {
                echo "❌ Failed to send reply\n";
            }
            
            // Test 4: File Upload Test (if local server)
            if ($USE_LOCAL) {
                echo "\n4. Testing Large File Support...\n";
                
                // Create test file (25MB)
                $testFile = 'test-25mb.txt';
                if (!file_exists($testFile)) {
                    echo "   Creating 25MB test file...\n";
                    $handle = fopen($testFile, 'w');
                    for ($i = 0; $i < 25 * 1024; $i++) {
                        fwrite($handle, str_repeat('TEST', 256));
                    }
                    fclose($handle);
                }
                
                echo "   File size: " . round(filesize($testFile) / 1024 / 1024, 2) . " MB\n";
                echo "   Uploading to Telegram...\n";
                
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $API_URL . '/sendDocument');
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, [
                    'chat_id' => $chatId,
                    'document' => new CURLFile($testFile),
                    'caption' => '25MB Test File - Local Server Test'
                ]);
                
                $response = curl_exec($curl);
                curl_close($curl);
                
                $result = json_decode($response, true);
                
                if ($result && $result['ok']) {
                    echo "✅ Large file uploaded successfully!\n";
                    echo "   LOCAL SERVER supports files up to 2GB!\n";
                } else {
                    echo "❌ Failed to upload large file\n";
                    if ($result) {
                        echo "   Error: {$result['description']}\n";
                    }
                }
            }
        }
    }
} else {
    echo "❌ Failed to get updates\n";
}

echo "\n========================================\n";
echo "TEST COMPLETED\n";
echo "========================================\n";

if ($USE_LOCAL) {
    echo "\n📌 LOCAL SERVER BENEFITS:\n";
    echo "   • Download files up to 2GB (vs 20MB)\n";
    echo "   • Upload files up to 2GB (vs 50MB)\n";
    echo "   • Files stored locally on your server\n";
    echo "   • No rate limits\n";
    echo "   • Better privacy\n";
} else {
    echo "\n📌 Using OFFICIAL API:\n";
    echo "   • Download limit: 20MB\n";
    echo "   • Upload limit: 50MB\n";
    echo "   • Consider using LOCAL server for larger files\n";
}

echo "\n";