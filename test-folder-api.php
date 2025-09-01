<?php
/**
 * Test script for Telegram Bot Folder Tree API
 * Run this from the project root: php test-folder-api.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

// Get the Telegram Bot Controller
$controller = new \App\Http\Controllers\TelegramBotController();

echo "Testing Telegram Bot Folder Tree API\n";
echo "=====================================\n\n";

// Test 1: Get root folders
echo "Test 1: Getting root folders\n";
$rootRequest = new Illuminate\Http\Request(['parent' => '', 'exclude' => '']);
try {
    $response = $controller->getFolderTreeLazy($rootRequest);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✓ Success! Found " . count($data['folders']) . " root folders\n";
        if (count($data['folders']) > 0) {
            echo "  First folder: " . $data['folders'][0]['name'] . "\n";
            echo "  Path: " . $data['folders'][0]['path'] . "\n";
            echo "  Has children: " . ($data['folders'][0]['hasChildren'] ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "✗ Failed: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Get subfolder contents (if we have folders)
if (isset($data['folders']) && count($data['folders']) > 0) {
    $firstFolder = $data['folders'][0];
    if ($firstFolder['hasChildren']) {
        echo "Test 2: Getting contents of '" . $firstFolder['name'] . "'\n";
        $subRequest = new Illuminate\Http\Request(['parent' => $firstFolder['path'], 'exclude' => '']);
        
        try {
            $response = $controller->getFolderTreeLazy($subRequest);
            $subData = json_decode($response->getContent(), true);
            
            if ($subData['success']) {
                echo "✓ Success! Found " . count($subData['folders']) . " subfolders\n";
                if (count($subData['folders']) > 0) {
                    echo "  First subfolder: " . $subData['folders'][0]['name'] . "\n";
                    echo "  Path: " . $subData['folders'][0]['path'] . "\n";
                }
            } else {
                echo "✗ Failed: " . ($subData['message'] ?? 'Unknown error') . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Exception: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Test 2: Skipped (first folder has no children)\n";
    }
}

echo "\n";

// Test 3: Check folder depth capability
echo "Test 3: Testing folder depth capability\n";
$testPath = '';
$depth = 0;
$maxDepth = 10;

while ($depth < $maxDepth) {
    $request = new Illuminate\Http\Request(['parent' => $testPath, 'exclude' => '']);
    
    try {
        $response = $controller->getFolderTreeLazy($request);
        $depthData = json_decode($response->getContent(), true);
        
        if ($depthData['success'] && count($depthData['folders']) > 0) {
            // Find a folder with children to go deeper
            $foundDeeper = false;
            foreach ($depthData['folders'] as $folder) {
                if ($folder['hasChildren']) {
                    $testPath = $folder['path'];
                    $foundDeeper = true;
                    $depth++;
                    echo "  Level $depth: Found folder with children: " . $folder['name'] . "\n";
                    break;
                }
            }
            
            if (!$foundDeeper) {
                echo "  Reached maximum depth at level $depth (no more folders with children)\n";
                break;
            }
        } else {
            echo "  Reached maximum depth at level $depth\n";
            break;
        }
    } catch (Exception $e) {
        echo "  Error at depth $depth: " . $e->getMessage() . "\n";
        break;
    }
}

echo "\n";
echo "Test complete!\n";
echo "\n";
echo "Summary:\n";
echo "--------\n";
echo "• API endpoint is " . (isset($data['success']) && $data['success'] ? "working" : "not working") . "\n";
echo "• Maximum tested depth: $depth levels\n";
echo "• Folder structure is " . ($depth > 3 ? "properly nested" : "shallow or limited") . "\n";