<?php
/**
 * Telegram Bot Test Script - Standalone PHP
 * Test untuk local telegram-bot-api server
 * 
 * Usage: php telegram-bot-test.php
 */

class TelegramBotTest {
    private $botToken;
    private $apiUrl;
    private $chatId;
    
    public function __construct($botToken, $useLocalServer = true, $host = '103.195.190.235', $port = '8081') {
        $this->botToken = $botToken;
        
        // Gunakan local server atau official API
        if ($useLocalServer) {
            // Local server yang sudah diinstall
            $this->apiUrl = "http://{$host}:{$port}/bot{$botToken}";
            echo "✅ Using LOCAL telegram-bot-api server at {$host}:{$port}\n";
        } else {
            // Official Telegram API
            $this->apiUrl = "https://api.telegram.org/bot{$botToken}";
            echo "📡 Using OFFICIAL Telegram API\n";
        }
        
        echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
        echo "API URL: {$this->apiUrl}\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    /**
     * Test 1: Get Bot Info
     */
    public function testGetMe() {
        echo "\n🔍 TEST 1: Getting Bot Info...\n";
        
        $response = $this->makeRequest('getMe');
        
        if ($response && $response['ok']) {
            $bot = $response['result'];
            echo "✅ Bot Name: @{$bot['username']}\n";
            echo "✅ Bot ID: {$bot['id']}\n";
            echo "✅ First Name: {$bot['first_name']}\n";
            
            if (isset($bot['can_join_groups'])) {
                echo "✅ Can Join Groups: " . ($bot['can_join_groups'] ? 'Yes' : 'No') . "\n";
            }
            
            return true;
        } else {
            echo "❌ Failed to get bot info\n";
            if ($response) {
                echo "Error: " . json_encode($response) . "\n";
            }
            return false;
        }
    }
    
    /**
     * Test 2: Get Updates (Messages)
     */
    public function testGetUpdates() {
        echo "\n📨 TEST 2: Getting Updates...\n";
        echo "Send a message to your bot to test!\n";
        
        $response = $this->makeRequest('getUpdates', [
            'limit' => 5,
            'timeout' => 10
        ]);
        
        if ($response && $response['ok']) {
            $updates = $response['result'];
            
            if (empty($updates)) {
                echo "⚠️ No updates found. Send a message to your bot first!\n";
                return false;
            }
            
            echo "✅ Found " . count($updates) . " update(s)\n";
            
            foreach ($updates as $update) {
                if (isset($update['message'])) {
                    $message = $update['message'];
                    $this->chatId = $message['chat']['id'];
                    
                    echo "\n📩 Message from: " . ($message['from']['username'] ?? 'Unknown') . "\n";
                    echo "   Chat ID: {$this->chatId}\n";
                    
                    if (isset($message['text'])) {
                        echo "   Text: {$message['text']}\n";
                    }
                    
                    if (isset($message['document'])) {
                        echo "   📎 Document: {$message['document']['file_name']}\n";
                        echo "   Size: " . $this->formatBytes($message['document']['file_size']) . "\n";
                        echo "   File ID: {$message['document']['file_id']}\n";
                    }
                    
                    if (isset($message['photo'])) {
                        echo "   🖼️ Photo received\n";
                    }
                }
            }
            
            return true;
        } else {
            echo "❌ Failed to get updates\n";
            return false;
        }
    }
    
    /**
     * Test 3: Send Message
     */
    public function testSendMessage($chatId = null) {
        echo "\n💬 TEST 3: Sending Message...\n";
        
        if (!$chatId && !$this->chatId) {
            echo "❌ No chat ID available. Send a message to the bot first!\n";
            return false;
        }
        
        $targetChatId = $chatId ?: $this->chatId;
        
        $response = $this->makeRequest('sendMessage', [
            'chat_id' => $targetChatId,
            'text' => "🤖 Test Message from Local Bot Server!\n\n" .
                     "✅ Server Status: Connected\n" .
                     "📅 Time: " . date('Y-m-d H:i:s') . "\n" .
                     "🖥️ Using: " . (strpos($this->apiUrl, 'localhost') !== false ? 'LOCAL Server' : 'OFFICIAL API'),
            'parse_mode' => 'HTML'
        ]);
        
        if ($response && $response['ok']) {
            echo "✅ Message sent successfully!\n";
            echo "   Message ID: {$response['result']['message_id']}\n";
            return true;
        } else {
            echo "❌ Failed to send message\n";
            if ($response) {
                echo "Error: " . json_encode($response) . "\n";
            }
            return false;
        }
    }
    
    /**
     * Test 4: Download File
     */
    public function testDownloadFile($fileId) {
        echo "\n📥 TEST 4: Downloading File...\n";
        
        // Get file info
        $response = $this->makeRequest('getFile', [
            'file_id' => $fileId
        ]);
        
        if ($response && $response['ok']) {
            $file = $response['result'];
            echo "✅ File Path: {$file['file_path']}\n";
            
            if (isset($file['file_size'])) {
                echo "✅ File Size: " . $this->formatBytes($file['file_size']) . "\n";
            }
            
            // Download file
            if (strpos($this->apiUrl, 'localhost') !== false) {
                // Local server - file path is direct
                $fileUrl = "http://localhost:8081/file/bot{$this->botToken}/{$file['file_path']}";
            } else {
                // Official API
                $fileUrl = "https://api.telegram.org/file/bot{$this->botToken}/{$file['file_path']}";
            }
            
            echo "📥 Downloading from: {$fileUrl}\n";
            
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent) {
                $fileName = 'downloads/' . basename($file['file_path']);
                
                // Create downloads directory if not exists
                if (!is_dir('downloads')) {
                    mkdir('downloads', 0777, true);
                }
                
                file_put_contents($fileName, $fileContent);
                echo "✅ File saved to: {$fileName}\n";
                echo "✅ Downloaded size: " . $this->formatBytes(strlen($fileContent)) . "\n";
                return true;
            } else {
                echo "❌ Failed to download file\n";
                return false;
            }
        } else {
            echo "❌ Failed to get file info\n";
            if ($response) {
                echo "Error: " . json_encode($response) . "\n";
            }
            return false;
        }
    }
    
    /**
     * Test 5: Send Large File (Test 2GB limit on local server)
     */
    public function testSendLargeFile($chatId = null) {
        echo "\n📤 TEST 5: Sending Large File...\n";
        
        if (!$chatId && !$this->chatId) {
            echo "❌ No chat ID available. Send a message to the bot first!\n";
            return false;
        }
        
        $targetChatId = $chatId ?: $this->chatId;
        
        // Create a test file (you can replace with actual large file)
        $testFile = 'test-large-file.txt';
        
        if (!file_exists($testFile)) {
            echo "Creating test file...\n";
            $handle = fopen($testFile, 'w');
            
            // Create 25MB file for testing (over 20MB limit)
            for ($i = 0; $i < 25 * 1024; $i++) {
                fwrite($handle, str_repeat('A', 1024));
            }
            fclose($handle);
            echo "✅ Created test file: " . $this->formatBytes(filesize($testFile)) . "\n";
        }
        
        echo "📤 Uploading file...\n";
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/sendDocument');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'chat_id' => $targetChatId,
            'document' => new CURLFile($testFile),
            'caption' => 'Large file test - ' . $this->formatBytes(filesize($testFile))
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $result = json_decode($response, true);
        
        if ($result && $result['ok']) {
            echo "✅ Large file sent successfully!\n";
            echo "   File size: " . $this->formatBytes(filesize($testFile)) . "\n";
            
            if (strpos($this->apiUrl, 'localhost') !== false) {
                echo "   ✅ LOCAL SERVER: Can handle files up to 2GB!\n";
            }
            
            return true;
        } else {
            echo "❌ Failed to send large file\n";
            if ($result) {
                echo "Error: " . json_encode($result) . "\n";
                
                if (strpos($result['description'] ?? '', 'too big') !== false) {
                    echo "\n⚠️ File size limit reached!\n";
                    if (strpos($this->apiUrl, 'api.telegram.org') !== false) {
                        echo "   Using OFFICIAL API: Max 50MB for upload\n";
                        echo "   Switch to LOCAL SERVER for 2GB limit!\n";
                    }
                }
            }
            return false;
        }
    }
    
    /**
     * Make HTTP Request to Telegram API
     */
    private function makeRequest($method, $params = []) {
        $url = $this->apiUrl . '/' . $method;
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($params),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            echo "❌ Connection failed! Is the server running?\n";
            echo "   URL: {$url}\n";
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "🚀 STARTING TELEGRAM BOT TESTS\n";
        echo str_repeat("=", 50) . "\n";
        
        // Test 1: Bot Info
        $this->testGetMe();
        sleep(1);
        
        // Test 2: Get Updates
        $this->testGetUpdates();
        sleep(1);
        
        // Test 3: Send Message (if we have chat ID)
        if ($this->chatId) {
            $this->testSendMessage();
            sleep(1);
            
            // Test 5: Send Large File
            $this->testSendLargeFile();
        } else {
            echo "\n⚠️ Skipping message tests - no chat ID available\n";
            echo "Send a message to your bot first, then run the test again!\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "✅ TESTS COMPLETED\n";
        echo str_repeat("=", 50) . "\n";
    }
}

// ============================================
// CONFIGURATION - EDIT HERE!
// ============================================

// Your bot token from @BotFather
$BOT_TOKEN = '8281280313:AAG0B4mu6tEzs3N0_BSO3VGatHov7t0klls';  // <-- Ganti dengan token bot Anda!

// Use local server? (true = local, false = official API)
$USE_LOCAL_SERVER = true;  // Set ke true untuk test local server

// Local server configuration
$LOCAL_SERVER_HOST = '103.195.190.235';  // IP public VPS Anda
$LOCAL_SERVER_PORT = '8081';

// Optional: Specific chat ID for testing (leave null to auto-detect)
$CHAT_ID = null;  // Atau masukkan chat ID Anda langsung

// ============================================
// RUN TESTS
// ============================================

if ($BOT_TOKEN === 'YOUR_BOT_TOKEN_HERE') {
    echo "❌ ERROR: Please set your bot token first!\n";
    echo "Edit this file and replace YOUR_BOT_TOKEN_HERE with your actual bot token.\n";
    echo "Get your token from @BotFather on Telegram.\n";
    exit(1);
}

// Create test instance
$bot = new TelegramBotTest($BOT_TOKEN, $USE_LOCAL_SERVER, $LOCAL_SERVER_HOST, $LOCAL_SERVER_PORT);

// Run all tests
$bot->runAllTests();

// Interactive mode
echo "\n📝 Interactive Commands:\n";
echo "  1. Send a message to test\n";
echo "  2. Get updates\n";
echo "  3. Download a file (need file_id)\n";
echo "  4. Send large file test\n";
echo "  5. Switch server (local/official)\n";
echo "  0. Exit\n";

while (true) {
    echo "\nEnter command (0-5): ";
    $command = trim(fgets(STDIN));
    
    switch ($command) {
        case '1':
            if ($CHAT_ID || $bot->chatId) {
                $bot->testSendMessage($CHAT_ID);
            } else {
                echo "Send a message to the bot first to get chat ID!\n";
                $bot->testGetUpdates();
            }
            break;
            
        case '2':
            $bot->testGetUpdates();
            break;
            
        case '3':
            echo "Enter file_id: ";
            $fileId = trim(fgets(STDIN));
            if ($fileId) {
                $bot->testDownloadFile($fileId);
            }
            break;
            
        case '4':
            if ($CHAT_ID || $bot->chatId) {
                $bot->testSendLargeFile($CHAT_ID);
            } else {
                echo "Send a message to the bot first to get chat ID!\n";
            }
            break;
            
        case '5':
            $USE_LOCAL_SERVER = !$USE_LOCAL_SERVER;
            $bot = new TelegramBotTest($BOT_TOKEN, $USE_LOCAL_SERVER, $LOCAL_SERVER_HOST, $LOCAL_SERVER_PORT);
            $bot->testGetMe();
            break;
            
        case '0':
            echo "Goodbye!\n";
            exit(0);
            
        default:
            echo "Invalid command!\n";
    }
}