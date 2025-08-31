<?php
/**
 * Telegram Bot Debug Script
 * Diagnosa masalah koneksi ke telegram-bot-api server
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========================================\n";
echo "TELEGRAM BOT DEBUG DIAGNOSTIC\n";
echo "========================================\n\n";

// Configuration
$BOT_TOKEN = '8281280313:AAG0B4mu6tEzs3N0_BSO3VGatHov7t0klls';
$SERVER_HOST = 'localhost';  // Gunakan localhost untuk akses internal
$SERVER_PORT = '8081';

echo "Configuration:\n";
echo "- Bot Token: " . substr($BOT_TOKEN, 0, 10) . "...\n";
echo "- Server: {$SERVER_HOST}:{$SERVER_PORT}\n\n";

// Test 1: Basic connectivity
echo "1. Testing basic connectivity...\n";
$fp = @fsockopen($SERVER_HOST, $SERVER_PORT, $errno, $errstr, 5);
if ($fp) {
    echo "✅ Port {$SERVER_PORT} is open\n";
    fclose($fp);
} else {
    echo "❌ Cannot connect to {$SERVER_HOST}:{$SERVER_PORT}\n";
    echo "   Error: {$errstr} (#{$errno})\n";
    exit(1);
}

// Test 2: HTTP connectivity
echo "\n2. Testing HTTP connectivity...\n";
$base_url = "http://{$SERVER_HOST}:{$SERVER_PORT}";
echo "   Testing: {$base_url}\n";

$ch = curl_init($base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response !== false) {
    echo "✅ HTTP Response Code: {$http_code}\n";
} else {
    echo "❌ HTTP Request failed\n";
    echo "   Error: {$curl_error}\n";
}

// Test 3: Bot API endpoint
echo "\n3. Testing Bot API endpoint...\n";
$api_url = "http://{$SERVER_HOST}:{$SERVER_PORT}/bot{$BOT_TOKEN}/getMe";
echo "   Testing: {$api_url}\n";

// Method 1: Using cURL
echo "\n   Method 1 - Using cURL:\n";
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response !== false) {
    echo "   ✅ Response received (HTTP {$http_code})\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ok'])) {
        if ($data['ok']) {
            echo "   ✅ Bot info retrieved successfully!\n";
            echo "   Bot Username: @{$data['result']['username']}\n";
            echo "   Bot Name: {$data['result']['first_name']}\n";
            echo "   Bot ID: {$data['result']['id']}\n";
        } else {
            echo "   ❌ API Error: {$data['description']}\n";
        }
    } else {
        echo "   ❌ Invalid JSON response\n";
        echo "   Raw response: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ cURL request failed: {$curl_error}\n";
}

// Method 2: Using file_get_contents
echo "\n   Method 2 - Using file_get_contents:\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($api_url, false, $context);

if ($response !== false) {
    echo "   ✅ Response received\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ok']) && $data['ok']) {
        echo "   ✅ Bot verified: @{$data['result']['username']}\n";
    } else {
        echo "   Response: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ file_get_contents failed\n";
    $error = error_get_last();
    if ($error) {
        echo "   Error: " . $error['message'] . "\n";
    }
}

// Test 4: Check PHP settings
echo "\n4. Checking PHP settings...\n";

// Check if required functions are available
$functions = ['curl_init', 'file_get_contents', 'json_decode', 'stream_context_create'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "   ✅ {$func}() is available\n";
    } else {
        echo "   ❌ {$func}() is NOT available\n";
    }
}

// Check allow_url_fopen
if (ini_get('allow_url_fopen')) {
    echo "   ✅ allow_url_fopen is enabled\n";
} else {
    echo "   ❌ allow_url_fopen is disabled\n";
}

// Check openssl
if (extension_loaded('openssl')) {
    echo "   ✅ OpenSSL extension is loaded\n";
} else {
    echo "   ⚠️ OpenSSL extension is not loaded\n";
}

// Check curl
if (extension_loaded('curl')) {
    echo "   ✅ cURL extension is loaded\n";
} else {
    echo "   ❌ cURL extension is not loaded\n";
}

// Test 5: Direct test with simple request
echo "\n5. Testing simple POST request to getUpdates...\n";
$updates_url = "http://{$SERVER_HOST}:{$SERVER_PORT}/bot{$BOT_TOKEN}/getUpdates";

$ch = curl_init($updates_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['limit' => 1]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['ok']) && $data['ok']) {
        echo "✅ getUpdates successful\n";
        $count = count($data['result']);
        echo "   Found {$count} update(s)\n";
    } else {
        echo "❌ getUpdates failed\n";
        if ($data && isset($data['description'])) {
            echo "   Error: {$data['description']}\n";
        }
    }
} else {
    echo "❌ Request failed\n";
}

// Summary
echo "\n========================================\n";
echo "DIAGNOSTIC SUMMARY\n";
echo "========================================\n";

echo "\n📋 Checklist:\n";
echo "1. Server connectivity: " . ($fp ? "✅" : "❌") . "\n";
echo "2. HTTP response: " . ($http_code > 0 ? "✅" : "❌") . "\n";
echo "3. Bot API access: " . (isset($data['ok']) && $data['ok'] ? "✅" : "❌") . "\n";
echo "4. PHP requirements: " . (function_exists('curl_init') ? "✅" : "❌") . "\n";

echo "\n💡 Recommendations:\n";
if (!$fp) {
    echo "- Check if telegram-bot-api server is running\n";
    echo "- Verify firewall allows port {$SERVER_PORT}\n";
}
if (!function_exists('curl_init')) {
    echo "- Enable cURL extension in PHP\n";
}
if (!ini_get('allow_url_fopen')) {
    echo "- Enable allow_url_fopen in php.ini\n";
}

echo "\n🔗 Test URLs:\n";
echo "- Server: http://{$SERVER_HOST}:{$SERVER_PORT}\n";
echo "- Bot API: http://{$SERVER_HOST}:{$SERVER_PORT}/bot{$BOT_TOKEN}/getMe\n";
echo "\nYou can test these URLs directly in your browser.\n";

echo "\n";