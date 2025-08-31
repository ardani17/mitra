<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Services\StorageDatabaseSyncService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

// Get a test project - first check what projects exist
$projects = Project::take(5)->get(['id', 'code', 'name']);
echo "Available projects:\n";
foreach ($projects as $p) {
    echo "  ID: {$p->id}, Code: {$p->code}, Name: {$p->name}\n";
}
echo "\n";

// Get first project or specific one
$project = Project::where('code', '3SBU-BBE-PT3')->first();
if (!$project) {
    $project = Project::first();
}

if (!$project) {
    echo "No test project found!\n";
    exit(1);
}

// Check what slug/folder name is used in storage
$storagePath = storage_path("app/proyek");
$projectFolders = [];
if (is_dir($storagePath)) {
    $folders = scandir($storagePath);
    foreach ($folders as $folder) {
        if ($folder != '.' && $folder != '..' && is_dir($storagePath . '/' . $folder)) {
            $projectFolders[] = $folder;
        }
    }
}

echo "Testing Storage-Database Synchronization\n";
echo "=========================================\n";
echo "Project: {$project->name}\n";
echo "Code: {$project->code}\n";
echo "ID: {$project->id}\n\n";

echo "Storage folders found:\n";
foreach (array_slice($projectFolders, 0, 5) as $folder) {
    echo "  - {$folder}\n";
}

// Try to determine the correct folder name
$projectSlug = null;
foreach ($projectFolders as $folder) {
    if (stripos($folder, strtolower(str_replace(' ', '-', $project->code))) !== false) {
        $projectSlug = $folder;
        break;
    }
}

if (!$projectSlug && count($projectFolders) > 0) {
    // Use first available folder for testing
    $projectSlug = $projectFolders[0];
    echo "\nUsing folder: {$projectSlug} for testing\n";
}

echo "\n";

$syncService = new StorageDatabaseSyncService();

// Step 1: Check current sync status
echo "Step 1: Checking current sync status...\n";
$syncStatus = $syncService->checkSyncStatus($project);

echo "Is Synced: " . ($syncStatus['is_synced'] ? 'Yes' : 'No') . "\n";
echo "Statistics:\n";
echo "  - Orphaned Files: {$syncStatus['stats']['orphaned_files']}\n";
echo "  - Missing Files: {$syncStatus['stats']['missing_files']}\n";
echo "  - Modified Files: {$syncStatus['stats']['modified_files']}\n";
echo "  - Orphaned Folders: {$syncStatus['stats']['orphaned_folders']}\n";
echo "  - Missing Folders: {$syncStatus['stats']['missing_folders']}\n";
echo "  - Total Issues: {$syncStatus['stats']['total_issues']}\n\n";

if (!$syncStatus['is_synced']) {
    echo "Issues found:\n";
    foreach ($syncStatus['issues'] as $type => $items) {
        if (!empty($items)) {
            echo "  {$type}:\n";
            foreach (array_slice($items, 0, 3) as $item) {
                if (is_array($item)) {
                    echo "    - Path: {$item['path']}\n";
                    if (isset($item['storage_size'])) {
                        echo "      Storage Size: {$item['storage_size']} bytes\n";
                    }
                    if (isset($item['db_size'])) {
                        echo "      DB Size: {$item['db_size']} bytes\n";
                    }
                } else {
                    echo "    - {$item}\n";
                }
            }
            if (count($items) > 3) {
                echo "    ... and " . (count($items) - 3) . " more\n";
            }
        }
    }
    echo "\n";
}

// Step 2: Create a test file in storage to simulate orphaned file
echo "Step 2: Creating test file in storage...\n";
if ($projectSlug) {
    $testFilePath = "proyek/{$projectSlug}/test-sync-" . time() . ".txt";
} else {
    // Create a new folder if none exists
    $projectSlug = strtolower(str_replace(' ', '-', $project->code)) . '-' . $project->id;
    $testFilePath = "proyek/{$projectSlug}/test-sync-" . time() . ".txt";
}
Storage::disk('local')->put($testFilePath, "Test content for sync validation");
echo "Created: storage/app/{$testFilePath}\n\n";

// Step 3: Create a test database record without file to simulate missing file
echo "Step 3: Creating test database record without file...\n";
$testFileName = 'test-missing-' . time() . '.txt';
$testDbRecord = DB::table('project_documents')->insertGetId([
    'project_id' => $project->id,
    'name' => $testFileName,
    'original_name' => $testFileName,
    'file_path' => "proyek/{$projectSlug}/dokumen/{$testFileName}",
    'file_size' => 100,
    'file_type' => 'document',
    'document_type' => 'other',
    'uploaded_by' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);
echo "Created DB record ID: {$testDbRecord}\n\n";

// Step 4: Check sync status again
echo "Step 4: Checking sync status after modifications...\n";
$syncStatus2 = $syncService->checkSyncStatus($project);

echo "Is Synced: " . ($syncStatus2['is_synced'] ? 'Yes' : 'No') . "\n";
echo "Statistics:\n";
echo "  - Orphaned Files: {$syncStatus2['stats']['orphaned_files']}\n";
echo "  - Missing Files: {$syncStatus2['stats']['missing_files']}\n";
echo "  - Modified Files: {$syncStatus2['stats']['modified_files']}\n";
echo "  - Orphaned Folders: {$syncStatus2['stats']['orphaned_folders']}\n";
echo "  - Missing Folders: {$syncStatus2['stats']['missing_folders']}\n";
echo "  - Total Issues: {$syncStatus2['stats']['total_issues']}\n\n";

// Step 5: Perform synchronization
if (!$syncStatus2['is_synced']) {
    echo "Step 5: Performing synchronization...\n";
    $syncResult = $syncService->performSync($project, ['soft_delete' => false]);
    
    if ($syncResult['success']) {
        echo "Sync Status: SUCCESS\n";
        echo "Message: {$syncResult['message']}\n";
        
        if (isset($syncResult['results'])) {
            echo "Sync Results:\n";
            echo "  - Added Files: {$syncResult['results']['added_files']}\n";
            echo "  - Removed Files: {$syncResult['results']['removed_files']}\n";
            echo "  - Updated Files: {$syncResult['results']['updated_files']}\n";
            echo "  - Added Folders: {$syncResult['results']['added_folders']}\n";
            echo "  - Removed Folders: {$syncResult['results']['removed_folders']}\n";
            
            if (!empty($syncResult['results']['errors'])) {
                echo "  - Errors:\n";
                foreach ($syncResult['results']['errors'] as $error) {
                    echo "    - {$error}\n";
                }
            }
        }
    } else {
        echo "Sync Status: FAILED\n";
        echo "Message: {$syncResult['message']}\n";
    }
    echo "\n";
}

// Step 6: Final sync status check
echo "Step 6: Final sync status check...\n";
$syncStatus3 = $syncService->checkSyncStatus($project);

echo "Is Synced: " . ($syncStatus3['is_synced'] ? 'Yes' : 'No') . "\n";
echo "Statistics:\n";
echo "  - Orphaned Files: {$syncStatus3['stats']['orphaned_files']}\n";
echo "  - Missing Files: {$syncStatus3['stats']['missing_files']}\n";
echo "  - Modified Files: {$syncStatus3['stats']['modified_files']}\n";
echo "  - Orphaned Folders: {$syncStatus3['stats']['orphaned_folders']}\n";
echo "  - Missing Folders: {$syncStatus3['stats']['missing_folders']}\n";
echo "  - Total Issues: {$syncStatus3['stats']['total_issues']}\n\n";

// Cleanup
echo "Step 7: Cleaning up test data...\n";
Storage::disk('local')->delete($testFilePath);
echo "Deleted test file from storage\n";

echo "\n=========================================\n";
echo "Test completed successfully!\n";