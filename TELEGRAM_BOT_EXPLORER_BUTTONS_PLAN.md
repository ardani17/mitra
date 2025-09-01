
# Telegram Bot Explorer - Upload, Refresh, and Sync Buttons Implementation Plan

## Overview
This document outlines the implementation plan for adding Upload, Refresh, and "Cek Sinkronisasi" (Check Synchronization) buttons to the Telegram Bot Explorer page, similar to the functionality in the Project Documents tab.

## Current State Analysis

### Project Documents Tab (Reference Implementation)
- **Technology**: Vue.js 3 with CDN
- **Location**: `resources/views/components/vue-file-explorer-advanced.blade.php`
- **Features**:
  1. Upload button with drag-and-drop support
  2. Refresh button to reload folder structure
  3. "Cek Sinkronisasi" button for storage-database sync
  4. Advanced file management (rename, delete, move, copy)

### Telegram Bot Explorer (Current Implementation)
- **Technology**: Plain JavaScript with server-side rendering
- **Location**: `resources/views/telegram-bot/explorer.blade.php`
- **Controller**: `app/Http/Controllers/TelegramBotController.php`
- **Current Features**:
  - Basic file/folder browsing
  - Search functionality
  - File operations (rename, copy, move, delete)
  - Create folder
  - Download files
  - Recent bot uploads display

## Implementation Strategy

### Phase 1: Add Button UI Elements
Add three buttons to the telegram-bot explorer page header:
1. **Upload** - Green button with upload icon
2. **Refresh** - Blue button with sync icon
3. **Cek Sinkronisasi** - Gray/Orange/Green button based on sync status

### Phase 2: Upload Functionality

#### Frontend Components
```javascript
// Upload modal with drag-and-drop
- Modal dialog for file selection
- Drag and drop zone
- Multiple file selection support
- Upload progress indicator
- File type and size validation (max 2GB per file)
```

#### Backend Endpoints
```php
// New routes needed:
Route::post('/telegram-bot/upload', 'TelegramBotController@uploadFiles');
Route::post('/telegram-bot/upload-chunk', 'TelegramBotController@uploadChunk'); // For large files
```

#### Implementation Details
1. **Upload Modal**:
   - Similar to project documents modal
   - Support for multiple file selection
   - Drag and drop functionality
   - Progress bar for each file
   - Success/error feedback

2. **File Processing**:
   - Store files in `storage/app/proyek/{current-path}`
   - Validate file types and sizes
   - Handle duplicate filenames
   - Update bot activity log

### Phase 3: Refresh Functionality

#### Implementation
```javascript
// Simple page reload or AJAX refresh
function refreshExplorer() {
    // Option 1: Simple reload
    location.reload();
    
    // Option 2: AJAX refresh (better UX)
    fetchAndUpdateFileList(currentPath);
}
```

### Phase 4: Sync Check Functionality

#### Backend Endpoints
```php
// New routes needed:
Route::get('/telegram-bot/check-sync', 'TelegramBotController@checkSync');
Route::post('/telegram-bot/sync-storage', 'TelegramBotController@syncStorage');
```

#### Sync Logic
1. **Check Sync Status**:
   - Compare files in storage with database records
   - Identify orphaned files (in storage but not in DB)
   - Identify missing files (in DB but not in storage)
   - Check file modifications (size/timestamp changes)

2. **Sync Process**:
   - Add orphaned files to database
   - Remove missing file records
   - Update modified file metadata
   - Show summary of changes

#### UI States
- **Unknown** (Gray): Initial state
- **Synced** (Green): All files match
- **Out of Sync** (Orange): Issues found, click to sync
- **Syncing** (Spinner): Sync in progress

## Detailed Implementation Steps

### Step 1: Update View File
```blade
<!-- Add to resources/views/telegram-bot/explorer.blade.php -->
<!-- After the search bar section -->
<div class="mb-4 flex justify-between items-center">
    <div class="flex gap-2">
        <button onclick="showUploadModal()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Upload
        </button>
        
        <button onclick="refreshExplorer()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh
        </button>
        
        <button id="syncButton" 
                onclick="handleSyncButton()" 
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out flex items-center">
            <svg id="syncIcon" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="syncText">Cek Sinkronisasi</span>
        </button>
    </div>
</div>
```

### Step 2: Add Upload Modal
```blade
<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upload Files</h3>
            </div>
            <div class="px-6 py-4">
                <!-- Current Path Display -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload to:</label>
                    <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">
                        /{{ $currentPath ?: 'Root' }}
                    </p>
                </div>
                
                <!-- Drag and Drop Zone -->
                <div id="dropZone" 
                     class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                        Drag and drop files here, or click to browse
                    </p>
                    <input type="file" id="fileInput" multiple class="hidden">
                    <button onclick="document.getElementById('fileInput').click()" 
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Select Files
                    </button>
                </div>
                
                <!-- Selected Files List -->
                <div id="selectedFiles" class="mt-4 hidden">
                    <h4 class="font-medium text-gray-700 mb-2">Selected Files:</h4>
                    <div id="fileList" class="max-h-48 overflow-y-auto space-y-2"></div>
                </div>
                
                <!-- Upload Progress -->
                <div id="uploadProgress" class="mt-4 hidden">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span id="progressText">Uploading...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button onclick="closeUploadModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button id="uploadButton" 
                        onclick="performUpload()" 
                        disabled
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload
                </button>
            </div>
        </div>
    </div>
</div>
```

### Step 3: JavaScript Implementation
```javascript
// Global variables
let selectedFilesArray = [];
let syncStatus = 'unknown';
let isUploading = false;
let isSyncing = false;

// Upload functionality
function showUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    resetUploadState();
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    resetUploadState();
}

function resetUploadState() {
    selectedFilesArray = [];
    document.getElementById('fileInput').value = '';
    document.getElementById('selectedFiles').classList.add('hidden');
    document.getElementById('uploadProgress').classList.add('hidden');
    document.getElementById('uploadButton').disabled = true;
    document.getElementById('fileList').innerHTML = '';
}

// Drag and drop handlers
const dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    handleFiles(e.dataTransfer.files);
});

document.getElementById('fileInput').addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    selectedFilesArray = Array.from(files);
    displaySelectedFiles();
    document.getElementById('uploadButton').disabled = false;
}

function displaySelectedFiles() {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    selectedFilesArray.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 rounded';
        fileItem.innerHTML = `
            <div class="flex items-center">
                <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm">${file.name}</span>
                <span class="text-xs text-gray-500 ml-2">(${formatFileSize(file.size)})</span>
            </div>
            <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
    
    document.getElementById('selectedFiles').classList.remove('hidden');
}

function removeFile(index) {
    selectedFilesArray.splice(index, 1);
    if (selectedFilesArray.length === 0) {
        resetUploadState();
    } else {
        displaySelectedFiles();
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

async function performUpload() {
    if (selectedFilesArray.length === 0 || isUploading) return;
    
    isUploading = true;
    document.getElementById('uploadButton').disabled = true;
    document.getElementById('uploadProgress').classList.remove('hidden');
    
    const currentPath = '{{ $currentPath }}';
    let successCount = 0;
    let errorCount = 0;
    
    for (let i = 0; i < selectedFilesArray.length; i++) {
        const file = selectedFilesArray[i];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('path', currentPath);
        
        try {
            const response = await fetch('{{ route("telegram-bot.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                successCount++;
            } else {
                errorCount++;
                console.error('Upload failed for', file.name, result.message);
            }
        } catch (error) {
            errorCount++;
            console.error('Upload error for', file.name, error);
        }
        
        // Update progress
        const progress = Math.round(((i + 1) / selectedFilesArray.length) * 100);
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressPercent').textContent = progress + '%';
        document.getElementById('progressText').textContent = `Uploading ${i + 1} of ${selectedFilesArray.length}...`;
    }
    
    // Show results
    let message = `Upload complete! `;
    if (successCount > 0) {
        message += `${successCount} file(s) uploaded successfully. `;
    }
    if (errorCount > 0) {
        message += `${errorCount} file(s) failed.`;
    }
    
    alert(message);
    
    if (successCount > 0) {
        // Refresh the page to show new files
        location.reload();
    }
    
    isUploading = false;
    closeUploadModal();
}

// Refresh functionality
function refreshExplorer() {
    location.reload();
}

// Sync functionality
async function checkSync() {
    const syncButton = document.getElementById('syncButton');
    const syncIcon = document.getElementById('syncIcon');
    const syncText = document.getElementById('syncText');
    
    // Show loading state
    syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
    syncIcon.classList.add('animate-spin');
    syncText.textContent = 'Checking...';
    
    try {
        const response = await fetch('{{ route("telegram-bot.check-sync") }}', {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            if (result.data.is_synced) {
                syncStatus = 'synced';
                syncButton.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center';
                syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                syncText.textContent = 'Tersinkronisasi';
            } else {
                syncStatus = 'out-of-sync';
                syncButton.className = 'px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-150 ease-in-out flex items-center';
                syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                syncText.textContent = `Sinkronkan (${result.data.stats.total_issues})`;
                
                // Show issues summary
                let message = 'Ditemukan masalah sinkronisasi:\n';
                if (result.data.stats.orphaned_files > 0) {
                    message += `\n• ${result.data.stats.orphaned_files} file belum tercatat`;
                }
                if (result.data.stats.missing_files > 0) {
                    message += `\n• ${result.data.stats.missing_files} file tidak ditemukan`;
                }
                message += '\n\nKlik "Sinkronkan" untuk memperbaiki.';
                
                alert(message);
            }
        }
    } catch (error) {
        console.error('Error checking sync:', error);
        alert('Failed to check synchronization status');
    } finally {
        syncIcon.classList.remove('animate-spin');
    }
}

async function performSync() {
    if (isSyncing) return;
    
    if (!confirm('Sinkronisasi akan:\n• Menambah file belum tercatat\n• Hapus record file hilang\n• Update metadata\n\nLanjutkan?')) {
        return;
    }
    
    isSyncing = true;
    const syncButton = document.getElementById('syncButton');
    const syncIcon = document.getElementById('syncIcon');
    const syncText = document.getElementById('syncText');
    
    // Show syncing state
    syncIcon.classList.add('animate-spin');
    syncText.textContent = 'Menyinkronkan...';
    
    try {
        const response = await fetch('{{ route("telegram-bot.sync-storage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                soft_delete: false
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            let message = 'Sinkronisasi berhasil!\n';
            if (result.data && result.data.results) {
                const res = result.data.results;
                if (res.added_files > 0) {
                    message += `\n• ${res.added_files} file ditambahkan`;
                }
                if (res.removed_files > 0) {
                    message += `\n• ${res.removed_files} record dihapus`;
                }
            }
            
            alert(message);
            
            // Update button to synced state
            syncStatus = 'synced';
            syncButton.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center';
            syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            syncText.textContent = 'Tersinkronisasi';
            
            // Refresh page to show updated files
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Sinkronisasi gagal: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error performing sync:', error);
        alert('Failed to synchronize storage');
    } finally {
        isSyncing = false;
        syncIcon.classList.remove('animate-spin');
    }
}

function handleSyncButton() {
    if (syncStatus === 'unknown' || syncStatus === 'synced') {
        checkSync();
    } else if (syncStatus === 'out-of-sync') {
        performSync();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check sync status on load (optional)
    // checkSync();
});
</script>
```

### Step 4: Backend Controller Methods

Add these methods to `TelegramBotController.php`:

```php
/**
 * Upload files to telegram bot storage
 */
public function uploadFiles(Request $request)
{
    $validated = $request->validate([
        'file' => 'required|file|max:2097152', // 2GB max
        'path' => 'nullable|string',
    ]);
    
    $file = $request->file('file');
    $path = $request->get('path', '');
    
    // Build storage path
    $basePath = storage_path('app/proyek');
    $targetPath = $basePath . ($path ? '/' . $path : '');
    
    // Security check
    $realPath = realpath($targetPath);
    $realBasePath = realpath($basePath);
    if ($realPath === false || strpos($realPath, $realBasePath) !== 0) {
        return response()->json(['success' => false, 'message' => 'Invalid path'], 403);
    }
    
    try {
        // Generate unique filename if exists
        $filename = $file->getClientOriginalName();
        $fullPath = $targetPath . '/' . $filename;
        
        if (file_exists($fullPath)) {
            $info = pathinfo($filename);
            $counter = 1;
            do {
                $filename = $info['filename'] . '_' . $counter . '.' . $info['extension'];
                $fullPath = $targetPath . '/' . $filename;
                $counter++;
            } while (file_exists($fullPath));
        }
        
        // Move uploaded file
        $file->move($targetPath, $filename);
        
        // Log activity
        BotActivity::create([
            'user_id' => auth()->id(),
            'message_type' => 'file',
            'file_name' => $filename,
            'file_path' => ($path ? $path . '/' : '') . $filename,
            'file_size' => $file->getSize(),
            'status' => 'uploaded',
            'created_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'filename' => $filename,
            'path' => ($path ? $path . '/' : '') . $filename
        ]);
        
    } catch (\Exception $e) {
        Log::error('File upload error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload file: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Check synchronization status between storage and database
 */
public function checkSyncStatus()
{
    try {
        $basePath = storage_path('app/proyek');
        $issues = [];
        $stats = [
            'orphaned_files' => 0,
            'missing_files' => 0,
            'total_issues' => 0
        ];
        
        // Get all files from storage
        $storageFiles = $this->getAllFilesRecursive($basePath);
        
        // Get all file records from database
        $dbFiles = BotActivity::where('message_type', 'file')
            ->whereNotNull('file_path')
            ->pluck('file_path')
            ->toArray();
        
        // Find orphaned files (in storage but not in DB)
        foreach ($storageFiles as $file) {
            $relativePath = str_replace($basePath . '/', '', $file);
            if (!in_array($relativePath, $dbFiles)) {
                $issues[] = [
                    'type' => 'orphaned',
                    'path' => $relativePath
                ];
                $stats['orphaned_files']++;
            }
        }
        
        // Find missing files (in DB but not in storage)
        foreach ($dbFiles as $dbFile) {
            $fullPath = $basePath . '/' . $dbFile;
            if (!file_exists($fullPath)) {
                $issues[] = [
                    'type' => 'missing',
                    'path' => $dbFile
                ];
                $stats['missing_files']++;
            }
        }
        
        $stats['total_issues'] = $stats['orphaned_files'] + $stats['missing_files'];
        $isSynced = $stats['total_issues'] === 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_synced' => $isSynced,
                'stats' => $stats,
                'issues' => array_slice($issues, 0, 100) // Limit to 100 issues
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Sync check error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to check sync status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Synchronize storage with database
 */
public function syncStorage(Request $request)
{
    try {
        $basePath = storage_path('app/proyek');
        $results = [
            'added_files' => 0,
            'removed_files' => 0,
            'errors' => []
        ];
        
        // Get all files from storage
        $storageFiles = $this->getAllFilesRecursive($basePath);
        
        // Get all file records from database
        $dbFiles = BotActivity::where('message_type', 'file')
            ->whereNotNull('file_path')
            ->get()
            ->keyBy('file_path');
        
        // Add orphaned files to database
        foreach ($storageFiles as $file) {
            $relativePath = str_replace($basePath . '/', '', $file);
            
            if (!$dbFiles->has($relativePath)) {
                try {
                    BotActivity::create([
                        'user_id' => auth()->id(),
                        'message_type' => 'file',
                        'file_name' => basename($file),
                        'file_path' => $relativePath,
                        'file_size' => filesize($file),
                        'status' => 'synced',
                        'created_at' => now(),
                    ]);
                    $results['added_files']++;
                } catch (\Exception $e) {
                    $results['errors'][] = 'Failed to add: ' . $relativePath;
                }
            }
        }
        
        // Remove missing file records
        foreach ($dbFiles as $path => $record) {
            $fullPath = $basePath . '/' . $path;
            if (!file_exists($fullPath)) {
                try {
                    $record->delete();
                    $results['removed_files']++;
                } catch (\Exception $e) {
                    $results['errors'][] = 'Failed to remove record: ' . $path;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Synchronization completed',
            'data' => [
                'results' => $results
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Sync error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to sync storage: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper method to get all files recursively
 */
private function getAllFilesRecursive($dir, &$results = [])
{
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $this->getAllFilesRecursive($path, $results);
        } else {
            $results[] = $