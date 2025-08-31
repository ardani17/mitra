# Debug Guide - Download ZIP Feature

## Error yang Terjadi
Error: "Failed to download folder as ZIP: Unexpected token '<', "<!DOCTYPE "... is not valid JSON"

Ini menunjukkan server mengembalikan HTML (halaman error Laravel) bukan ZIP file.

## Langkah-langkah Debugging

### 1. Cek Browser Console
Buka Developer Tools (F12) > Console tab, dan lihat output dari:
- `Folder path to download: [path]`
- `API URL: [url]`
- `Response status: [status]`
- `Content-Type: [type]`

### 2. Cek Network Tab
1. Buka Developer Tools > Network tab
2. Klik Download ZIP pada folder
3. Cari request ke `/api/file-explorer/project/37/folders/download-zip`
4. Cek:
   - Status Code (harusnya 200)
   - Response Headers
   - Response Body (Preview/Response tab)

### 3. Kemungkinan Penyebab & Solusi

#### A. Authentication Issue (401/419)
**Gejala:** Status 401 atau 419, response HTML login page

**Solusi:**
```php
// Pastikan route memiliki middleware auth
Route::prefix('file-explorer')->middleware('auth')->group(function () {
    // routes...
});
```

#### B. CSRF Token Issue (419)
**Gejala:** Status 419, "Page Expired"

**Solusi:**
1. Pastikan meta tag CSRF ada di layout:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

2. Refresh halaman untuk mendapatkan token baru

#### C. Authorization Issue (403)
**Gejala:** Status 403, "Forbidden"

**Solusi:**
Cek method authorize di controller:
```php
// Pastikan user memiliki permission
$this->authorize('view', $project);
```

#### D. Route Not Found (404)
**Gejala:** Status 404, "Not Found"

**Solusi:**
1. Cek route terdaftar:
```bash
php artisan route:list | grep download-zip
```

2. Pastikan route file di-include di api.php:
```php
// routes/api.php
require __DIR__ . '/api/file-explorer.php';
```

#### E. PHP ZipArchive Not Found (500)
**Gejala:** Status 500, "Class 'ZipArchive' not found"

**Solusi:**
1. Cek PHP extension:
```bash
php -m | grep zip
```

2. Install jika belum ada:
```bash
# Windows (Laragon/XAMPP)
# Edit php.ini, uncomment:
extension=zip

# Restart Apache/Nginx
```

#### F. Folder Path Issue
**Gejala:** Folder not found error

**Debug:**
Tambahkan logging di controller:
```php
public function downloadFolderAsZip(Request $request, Project $project)
{
    Log::info('Download ZIP Request', [
        'project_id' => $project->id,
        'folder_path' => $request->input('folder_path'),
        'full_path' => storage_path("app/proyek/{$projectSlug}/{$folderPath}")
    ]);
    // ...
}
```

Cek log:
```bash
tail -f storage/logs/laravel.log
```

### 4. Test Manual dengan Tinker

Test path folder:
```bash
php artisan tinker

$projectSlug = 'your-project-slug';
$folderPath = 'dokumen';
$fullPath = storage_path("app/proyek/{$projectSlug}/{$folderPath}");

// Cek folder exists
file_exists($fullPath);
is_dir($fullPath);

// List files
scandir($fullPath);
```

### 5. Test dengan cURL

Test endpoint langsung (ganti dengan session cookie Anda):
```bash
curl -X POST http://127.0.0.1:8000/api/file-explorer/project/37/folders/download-zip \
  -H "Content-Type: application/json" \
  -H "Accept: application/octet-stream" \
  -H "X-CSRF-TOKEN: [your-csrf-token-from-meta-tag]" \
  -H "Cookie: laravel_session=[your-session-cookie]" \
  -d '{"folder_path": "dokumen"}' \
  -v
```

### 6. Temporary Debug Route

Buat route test sementara untuk debug:
```php
// routes/web.php (temporary)
Route::get('/test-zip', function() {
    $project = \App\Models\Project::find(37);
    $folderPath = 'dokumen';
    $projectSlug = Str::slug($project->name);
    $fullPath = storage_path("app/proyek/{$projectSlug}/{$folderPath}");
    
    return [
        'project_exists' => $project ? true : false,
        'project_slug' => $projectSlug,
        'folder_path' => $folderPath,
        'full_path' => $fullPath,
        'folder_exists' => file_exists($fullPath),
        'is_directory' => is_dir($fullPath),
        'files' => is_dir($fullPath) ? scandir($fullPath) : [],
        'zip_extension' => extension_loaded('zip'),
        'auth_user' => auth()->user() ? auth()->user()->email : 'not logged in'
    ];
})->middleware('auth');
```

Akses: http://127.0.0.1:8000/test-zip

### 7. Check Laravel Log

Cek error detail di log:
```bash
# Real-time monitoring
tail -f storage/logs/laravel.log

# Atau cari error spesifik
grep -i "download" storage/logs/laravel.log
grep -i "zip" storage/logs/laravel.log
```

### 8. Common Fixes

#### Fix 1: Clear Cache
```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

#### Fix 2: Permissions
```bash
# Linux/Mac
chmod -R 755 storage/app/proyek
chmod -R 755 storage/app/temp

# Pastikan folder temp ada
mkdir -p storage/app/temp
```

#### Fix 3: Response Headers
Pastikan controller return response dengan header yang benar:
```php
return response()->download($tempPath, $zipFileName, [
    'Content-Type' => 'application/zip',
    'Content-Disposition' => 'attachment; filename="'.$zipFileName.'"'
])->deleteFileAfterSend(true);
```

### 9. Alternative Implementation

Jika masih bermasalah, coba implementasi alternatif dengan streaming:
```php
// Di controller
return response()->streamDownload(function () use ($fullPath) {
    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'zip');
    $zip->open($zipFile, ZipArchive::CREATE);
    $this->addFolderToZip($zip, $fullPath, basename($fullPath));
    $zip->close();
    
    readfile($zipFile);
    unlink($zipFile);
}, $zipFileName, [
    'Content-Type' => 'application/zip'
]);
```

## Informasi yang Dibutuhkan untuk Debug

Tolong berikan informasi berikut:
1. Screenshot Browser Console dengan error
2. Screenshot Network tab showing the failed request
3. Response status code
4. Response headers (dari Network tab)
5. Response body/preview (dari Network tab)
6. Output dari `php artisan route:list | grep download-zip`
7. Apakah PHP zip extension terinstall? (`php -m | grep zip`)
8. Laravel version (`php artisan --version`)

Dengan informasi ini, kita bisa mengidentifikasi masalah spesifik dan memberikan solusi yang tepat.