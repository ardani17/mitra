<?php
/**
 * Script untuk check status telegram-bot-api server
 * Verifikasi server berjalan dengan benar
 */

echo "========================================\n";
echo "TELEGRAM BOT API SERVER CHECK\n";
echo "========================================\n\n";

// Check 1: Test koneksi ke port 8081
echo "1. Checking server connection...\n";

$host = 'localhost';
$port = 8081;

$connection = @fsockopen($host, $port, $errno, $errstr, 5);

if ($connection) {
    echo "✅ Server is running on port {$port}\n";
    fclose($connection);
    
    // Check 2: Test HTTP response
    echo "\n2. Testing HTTP response...\n";
    
    $url = "http://{$host}:{$port}/";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    
    if (!empty($headers)) {
        echo "✅ HTTP Response received\n";
        echo "   Status: " . $headers[0] . "\n";
        
        // Parse response
        if ($response) {
            $data = json_decode($response, true);
            if ($data) {
                echo "   Response: " . json_encode($data) . "\n";
            }
        }
    } else {
        echo "⚠️ No HTTP response\n";
    }
    
    // Check 3: Test dengan bot token (optional)
    echo "\n3. Testing with bot token...\n";
    echo "   Enter your bot token (or press Enter to skip): ";
    
    $token = trim(fgets(STDIN));
    
    if (!empty($token) && $token !== '') {
        $botUrl = "http://{$host}:{$port}/bot{$token}/getMe";
        
        echo "   Testing: {$botUrl}\n";
        
        $botResponse = @file_get_contents($botUrl, false, $context);
        
        if ($botResponse) {
            $botData = json_decode($botResponse, true);
            
            if ($botData && isset($botData['ok'])) {
                if ($botData['ok']) {
                    echo "✅ Bot connected successfully!\n";
                    echo "   Bot Username: @{$botData['result']['username']}\n";
                    echo "   Bot Name: {$botData['result']['first_name']}\n";
                    echo "   Bot ID: {$botData['result']['id']}\n";
                    
                    // Check if local mode
                    if (isset($botData['result']['can_join_groups'])) {
                        echo "\n📌 Server Features:\n";
                        echo "   • File size limit: 2GB (vs 20MB on official)\n";
                        echo "   • Local file storage\n";
                        echo "   • No rate limits\n";
                    }
                } else {
                    echo "❌ Bot error: {$botData['description']}\n";
                }
            } else {
                echo "❌ Invalid response from bot API\n";
            }
        } else {
            echo "❌ Failed to connect to bot API\n";
        }
    } else {
        echo "   Skipped bot token test\n";
    }
    
} else {
    echo "❌ Server is NOT running on port {$port}\n";
    echo "   Error: {$errstr} (Code: {$errno})\n";
    
    echo "\n📝 How to start the server:\n";
    echo "1. Navigate to telegram-bot-api directory:\n";
    echo "   cd /home/teleweb/backend/data-bot-api\n\n";
    
    echo "2. Start the server with your API credentials:\n";
    echo "   ./telegram-bot-api \\\n";
    echo "     --api-id=YOUR_API_ID \\\n";
    echo "     --api-hash=YOUR_API_HASH \\\n";
    echo "     --local \\\n";
    echo "     --port=8081\n\n";
    
    echo "3. Or run in background:\n";
    echo "   nohup ./telegram-bot-api \\\n";
    echo "     --api-id=YOUR_API_ID \\\n";
    echo "     --api-hash=YOUR_API_HASH \\\n";
    echo "     --local \\\n";
    echo "     --port=8081 > telegram-bot.log 2>&1 &\n\n";
    
    echo "Get API credentials from: https://my.telegram.org\n";
}

// Check 4: System process check
echo "\n4. Checking system processes...\n";

$processes = shell_exec("ps aux | grep telegram-bot-api | grep -v grep");

if (!empty($processes)) {
    echo "✅ telegram-bot-api process found:\n";
    $lines = explode("\n", trim($processes));
    foreach ($lines as $line) {
        if (!empty($line)) {
            // Parse process info
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 11) {
                echo "   PID: {$parts[1]}\n";
                echo "   CPU: {$parts[2]}%\n";
                echo "   MEM: {$parts[3]}%\n";
                echo "   Command: " . implode(' ', array_slice($parts, 10)) . "\n";
            }
        }
    }
} else {
    echo "⚠️ No telegram-bot-api process found\n";
}

// Check 5: Check listening ports
echo "\n5. Checking listening ports...\n";

$netstat = shell_exec("netstat -tuln | grep :8081");

if (!empty($netstat)) {
    echo "✅ Port 8081 is listening:\n";
    echo "   " . trim($netstat) . "\n";
} else {
    echo "⚠️ Port 8081 is not listening\n";
}

// Summary
echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n";

if ($connection) {
    echo "✅ Server Status: RUNNING\n";
    echo "✅ Port 8081: OPEN\n";
    echo "\nYou can now use the telegram bot test scripts:\n";
    echo "• php telegram-bot-quick-test.php\n";
    echo "• php telegram-bot-test.php\n";
} else {
    echo "❌ Server Status: NOT RUNNING\n";
    echo "❌ Port 8081: CLOSED\n";
    echo "\nPlease start the telegram-bot-api server first.\n";
}

echo "\n";