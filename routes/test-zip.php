
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// TEMPORARY DEBUG ROUTES - REMOVE AFTER TESTING

Route::middleware(['web', 'auth'])->group(function () {
    
    // Test 1: Check environment and paths
    Route::get('/test-zip-env', function() {
        $project = \App\Models\Project::find(37);
        
        if (!$project) {
            return response()->json(['error' => 'Project 37 not found']);
        }
        
        $folderPath = 'dokumen';
        $projectSlug = Str::slug($project->name);
        $fullPath = storage_path("app/proyek/{$projectSlug}/{$folderPath}");
        
        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $projectSlug
            ],
            'paths' => [
                'folder_path' => $folderPath,
                'full_path' => $fullPath,
                'storage_path' => storage_path('app'),
                'temp_path' => storage_path('app/temp')
            ],
            'checks' => [
                'folder_exists' => file_exists($fullPath),
                'is_directory' => is_dir($fullPath),
                'is_readable' => is_readable($fullPath),
                'temp_dir_exists' => file_exists(storage_path('app/temp')),
                'temp_is_writable' => is_writable(storage_path('app/temp'))
            ],
            'folder_contents' => is_dir($fullPath) ? array_slice(scandir($fullPath), 2) : [],
            'php' => [
                'zip_extension' => extension_loaded('zip'),
                'max_execution_time' => ini_get('max_execution_time'),
                'memory_limit' => ini_get('memory_limit')
            ],
            'auth' => [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email ?? 'not logged in',
                'can_view_project' => auth()->user()->can('view', $project)
            ]
        ]);
    });
    
    // Test 2: Simple ZIP creation test
    Route::get('/test-zip-create', function() {
        if (!extension_loaded('zip')) {
            return response()->json(['error' => 'PHP Zip extension not installed']);
        }
        
        $testFile = storage_path('app/temp/test.zip');
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        try {
            $zip = new \ZipArchive();
            $result = $zip->open($testFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            
            if ($result !== TRUE) {
                return response()->json([
                    'error' => 'Cannot create ZIP file',
                    'error_code' => $result,
                    'path' => $testFile
                ]);
            }
            
            // Add a test file
            $zip->addFromString('test.txt', 'This is a test file');
            $zip->close();
            
            $fileExists = file_exists($testFile);
            $fileSize = $fileExists ? filesize($testFile) : 0;
            
            // Clean up
            if ($fileExists) {
                unlink($testFile);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'ZIP creation test successful',
                'file_created' => $fileExists,
                'file_size' => $fileSize
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception during ZIP creation',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });
    
    // Test 3: Test actual folder ZIP with small folder
    Route::get('/test-zip-folder/{projectId}', function($projectId) {
        $project = \App\Models\Project::find($projectId);
        
        if (!$project) {
            return response()->json(['error' => "Project {$projectId} not found"]);
        }
        
        $folderPath = 'dokumen';
        $projectSlug = Str::slug($project->name);
        $fullPath = storage_path("app/proyek/{$projectSlug}/{$folderPath}");
        
        if (!is_dir($fullPath)) {
            return response()->json([
                'error' => 'Folder not found',
                'path' => $fullPath
            ]);
        }
        
        try {
            $zipFileName = 'test-' . time() . '.zip';
            $tempPath = storage_path('app/temp/' . $zipFileName);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $zip = new \ZipArchive();
            
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                return response()->json(['error' => 'Cannot create ZIP file']);
            }
            
            // Add files to ZIP (simplified version)
            $files = scandir($fullPath);
            $fileCount = 0;
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filePath = $fullPath . '/' . $file;
                
                if (is_file($filePath)) {
                    $zip->addFile($filePath, $file);
                    $fileCount++;
                }
            }
            
            $zip->close();
            
            $zipExists = file_exists($tempPath);
            $zipSize = $zipExists ? filesize($tempPath) : 0;
            
            // Clean up
            if ($zipExists) {
                unlink($tempPath);
            }
            
            return response()->json([
                'success' => true,
                'folder_path' => $folderPath,
                'files_processed' => $fileCount,
                'zip_created' => $zipExists,
                'zip_size' => $zipSize,
                'message' => "Successfully created ZIP with {$fileCount} files"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception during ZIP creation',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });
    
    // Test 4: Direct test of the actual API endpoint
    Route::post('/test-api-download-zip/{projectId}', function($projectId) {
        $project = \App\Models\Project::find($projectId);
        
        if (!$project) {
            return response()->json(['error' => "Project {$projectId} not found"]);
        }
        
        // Check if user can view project
        if (!auth()->user()->can('view', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $folderPath = request()->input('folder_path', 'dokumen');
        
        return response()->json([
            'test' => 'API endpoint test',
            'project_id' => $projectId,
            'folder_path' => $folderPath,
            'user' => auth()->user()->email,
            'can_view' => auth()->user()->can('view', $project),
            'request_headers' => request()->headers->all(),
            'csrf_token_valid' => request()->session()->token() === request()->header('X-CSRF-TOKEN')
        ]);
    });
});