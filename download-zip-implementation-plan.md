# Implementasi Download ZIP untuk Folder di File Explorer

## Analisis Struktur yang Ada

### File-file yang Perlu Dimodifikasi:
1. **Frontend (Vue.js)**:
   - `resources/views/components/vue-file-explorer-advanced.blade.php` - Tambah menu Download ZIP
   - `resources/views/components/vue-file-explorer-mobile.blade.php` - Tambah menu Download ZIP untuk mobile

2. **Backend (Laravel)**:
   - `app/Http/Controllers/Api/FileExplorerController.php` - Tambah method downloadFolderAsZip
   - `routes/api.php` - Tambah route untuk download ZIP

## Implementasi Detail

### 1. Frontend - Tambah Menu Download ZIP

**Lokasi**: Line 197-209 di `vue-file-explorer-advanced.blade.php`

```html
<!-- Folder Context Menu -->
<div v-if="showContextMenu"
     :style="{ top: contextMenuY + 'px', left: contextMenuX + 'px' }"
     class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
    <button @click="openRenameFolderModal"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
        <i class="fas fa-edit mr-2"></i> Rename
    </button>
    <button @click="confirmDeleteFolder"
            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
        <i class="fas fa-trash mr-2"></i> Delete
    </button>
    <!-- TAMBAHKAN INI -->
    <button @click="downloadFolderAsZip"
            class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
        <i class="fas fa-file-archive mr-2"></i> Download ZIP
    </button>
</div>
```

### 2. Frontend - Tambah Method downloadFolderAsZip

**Lokasi**: Dalam methods section (sekitar line 943)

```javascript
async downloadFolderAsZip() {
    const folder = this.contextMenuFolder;
    this.closeContextMenu();
    
    if (!folder) return;
    
    try {
        // Show loading notification
        alert('Preparing ZIP download for folder: ' + folder.name + '...');
        
        // Extract folder path relative to project
        let folderPath = folder.path;
        const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
        if (folderPath.startsWith(projectPrefix)) {
            folderPath = folderPath.substring(projectPrefix.length);
        }
        
        // Create form and submit for download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/api/file-explorer/project/{{ $project->id }}/folders/download-zip';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        
        // Add folder path
        const pathInput = document.createElement('input');
        pathInput.type = 'hidden';
        pathInput.name = 'folder_path';
        pathInput.value = folderPath;
        form.appendChild(pathInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
    } catch (error) {
        console.error('Error downloading folder as ZIP:', error);
        alert('Failed to download folder as ZIP: ' + error.message);
    }
}
```

### 3. Backend - Controller Method

**File**: `app/Http/Controllers/Api/FileExplorerController.php`

```php
use ZipArchive;
use Illuminate\Support\Str;

public function downloadFolderAsZip(Request $request, $projectId)
{
    $request->validate([
        'folder_path' => 'required|string'
    ]);
    
    $project = Project::findOrFail($projectId);
    
    // Check permissions
    $this->authorize('view', $project);
    
    $folderPath = $request->folder_path;
    $fullPath = storage_path('app/proyek/' . Str::slug($project->name) . '/' . $folderPath);
    
    // Check if folder exists
    if (!is_dir($fullPath)) {
        return response()->json(['error' => 'Folder not found'], 404);
    }
    
    // Create temporary ZIP file
    $zipFileName = Str::slug($project->code . '-' . basename($folderPath)) . '-' . date('Y-m-d-His') . '.zip';
    $tempPath = storage_path('app/temp/' . $zipFileName);
    
    // Ensure temp directory exists
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }
    
    $zip = new ZipArchive();
    
    if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Add files to ZIP recursively
        $this->addFolderToZip($zip, $fullPath, basename($folderPath));
        $zip->close();
        
        // Return ZIP file as download
        return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    return response()->json(['error' => 'Failed to create ZIP file'], 500);
}

private function addFolderToZip($zip, $folderPath, $zipPath = '')
{
    $files = scandir($folderPath);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $filePath = $folderPath . '/' . $file;
        $zipFilePath = $zipPath ? $zipPath . '/' . $file : $file;
        
        if (is_dir($filePath)) {
            // Add directory
            $zip->addEmptyDir($zipFilePath);
            // Recursively add subdirectory
            $this->addFolderToZip($zip, $filePath, $zipFilePath);
        } else {
            // Add file
            $zip->addFile($filePath, $zipFilePath);
        }
    }
}
```

### 4. Backend - Route

**File**: `routes/api.php`

```php
Route::post('/file-explorer/project/{project}/folders/download-zip', 
    [FileExplorerController::class, 'downloadFolderAsZip'])
    ->name('api.file-explorer.download-zip')
    ->middleware('auth');
```

## Testing Checklist

1. [ ] Klik kanan pada folder di file explorer
2. [ ] Verifikasi menu "Download ZIP" muncul
3. [ ] Klik "Download ZIP"
4. [ ] Verifikasi file ZIP terdownload
5. [ ] Extract ZIP dan verifikasi isi lengkap
6. [ ] Test dengan folder yang memiliki subfolder
7. [ ] Test dengan folder kosong
8. [ ] Test dengan folder yang berisi file besar

## Catatan Penting

1. **PHP ZipArchive Extension**: Pastikan extension `php-zip` sudah terinstall
2. **Memory Limit**: Untuk folder besar, perlu diperhatikan memory_limit PHP
3. **Timeout**: Untuk folder sangat besar, mungkin perlu adjust max_execution_time
4. **Temp Directory**: Pastikan directory `storage/app/temp/` memiliki permission write
5. **Security**: Method sudah include authorization check untuk memastikan user memiliki akses ke project

## Alternative Implementation (Using Response Stream)

Jika ingin menggunakan AJAX dengan blob download:

```javascript
async downloadFolderAsZip() {
    const folder = this.contextMenuFolder;
    this.closeContextMenu();
    
    if (!folder) return;
    
    try {
        let folderPath = folder.path;
        const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
        if (folderPath.startsWith(projectPrefix)) {
            folderPath = folderPath.substring(projectPrefix.length);
        }
        
        const response = await fetch('/api/file-explorer/project/{{ $project->id }}/folders/download-zip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                folder_path: folderPath
            })
        });
        
        if (!response.ok) throw new Error('Failed to download folder');
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = folder.name + '.zip';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
    } catch (error) {
        console.error('Error downloading folder as ZIP:', error);
        alert('Failed to download folder as ZIP: ' + error.message);
    }
}