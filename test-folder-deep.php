<?php
/**
 * Deep test for Telegram Bot Folder Tree API
 * Run this from the project root: php test-folder-deep.php
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

echo "Testing Deep Folder Structure\n";
echo "==============================\n\n";

// Test specific folder path that we know has deep structure
$testPaths = [
    '',  // Root
    '3sbu-bbe-md-pt2-expand-odp-bbe-fap-28',  // Level 1
    '3sbu-bbe-md-pt2-expand-odp-bbe-fap-28/dokumen',  // Level 2
    '3sbu-bbe-md-pt2-expand-odp-bbe-fap-28/dokumen/keuangan',  // Level 3
];

foreach ($testPaths as $index => $path) {
    $level = $index;
    echo "Level $level: Testing path '$path'\n";
    
    $request = new Illuminate\Http\Request(['parent' => $path, 'exclude' => '']);
    
    try {
        $response = $controller->getFolderTreeLazy($request);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success']) {
            echo "  ✓ Success! Found " . count($data['folders']) . " folders\n";
            
            // Show folder details
            foreach ($data['folders'] as $folder) {
                echo "    - " . $folder['name'];
                echo " (hasChildren: " . ($folder['hasChildren'] ? 'Yes' : 'No') . ")";
                if (isset($folder['childCount'])) {
                    echo " [" . $folder['childCount'] . " children]";
                }
                echo "\n";
            }
        } else {
            echo "  ✗ Failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test complete!\n";