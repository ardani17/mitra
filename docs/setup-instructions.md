# Project Document Management System - Setup Instructions

## Prerequisites

1. **Laravel 8+ Application**
2. **PHP 7.4+ or 8.0+**
3. **MySQL/PostgreSQL Database**
4. **Rclone installed and configured** (for cloud sync)
5. **Alpine.js** (included in Laravel)

## Installation Steps

### 1. Database Setup

Run the migrations to create necessary tables:

```bash
php artisan migrate
```

This will create:
- Updated `project_documents` table with new columns
- `project_folders` table for folder structure
- `sync_logs` table for sync history

### 2. Register Service Providers

Add these providers to `config/app.php`:

```php
'providers' => [
    // ... other providers
    App\Providers\ObserverServiceProvider::class,
    App\Providers\FileExplorerServiceProvider::class,
],
```

### 3. Configure Rclone

Install rclone on your server:

```bash
# Ubuntu/Debian
sudo apt-get install rclone

# macOS
brew install rclone

# Windows
# Download from https://rclone.org/downloads/
```

Configure rclone with your cloud storage:

```bash
rclone config

# Follow the prompts to add your cloud storage
# Name it 'gdrive' for Google Drive
```

Update `.env` file:

```env
# Rclone Configuration
RCLONE_REMOTE=gdrive
RCLONE_BASE_PATH=/project-documents
RCLONE_CONFIG_PATH=/home/user/.config/rclone/rclone.conf
```

### 4. Storage Configuration

Create storage directories:

```bash
# Create base storage directory
mkdir -p storage/app/proyek

# Set permissions
chmod -R 775 storage/app/proyek
```

### 5. Migrate Existing Projects

If you have existing projects with documents:

```bash
# Dry run first to see what will happen
php artisan project:migrate-documents --dry-run

# Migrate all projects
php artisan project:migrate-documents

# Migrate specific project
php artisan project:migrate-documents --project=34
```

### 6. Frontend Assets

Include Alpine.js and the file explorer component:

```html
<!-- In your layout file -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('js/components/file-explorer.js') }}"></script>
```

Copy the file explorer JavaScript:

```bash
# Copy the component file
cp resources/js/components/file-explorer.js public/js/components/
```

Or compile with Laravel Mix:

```javascript
// webpack.mix.js
mix.js('resources/js/components/file-explorer.js', 'public/js/components');
```

### 7. Update Project Show View

In your `resources/views/projects/show.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Project header -->
    <div class="project-header">
        <h1>{{ $project->name }}</h1>
    </div>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#documents">Documents</a>
        </li>
        <!-- Other tabs -->
    </ul>
    
    <!-- Tab content -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="overview">
            <!-- Overview content -->
        </div>
        
        <!-- Documents Tab -->
        @include('projects.partials.documents-tab')
        
        <!-- Other tab panes -->
    </div>
</div>
@endsection
```

## Usage

### Manual Sync

Sync project documents to cloud storage:

```bash
# Sync specific project
php artisan project:sync --project=34

# Sync all projects
php artisan project:sync --all

# Check sync status
php artisan project:sync --stats

# Test rclone connection
php artisan project:sync --test

# Retry failed syncs
php artisan project:sync --retry-failed
```

### Automatic Sync (Optional)

Add to Laravel scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync all projects daily at 2 AM
    $schedule->command('project:sync --all')
             ->dailyAt('02:00')
             ->withoutOverlapping();
}
```

## API Endpoints

The system provides these API endpoints:

```
GET    /api/projects/{project}/folders           - Get folder structure
GET    /api/projects/{project}/folders/contents  - Get folder contents
POST   /api/projects/{project}/folders/create    - Create new folder
POST   /api/projects/{project}/documents/upload  - Upload document
DELETE /api/projects/{project}/documents/{id}    - Delete document
PUT    /api/projects/{project}/documents/{id}/move   - Move document
PUT    /api/projects/{project}/documents/{id}/rename - Rename document
POST   /api/projects/{project}/sync              - Sync project
GET    /api/projects/{project}/sync/status       - Get sync status
GET    /api/documents/{id}/download              - Download document
GET    /api/documents/{id}/preview               - Preview document
```

## Folder Structure

The system creates this folder structure for each project:

```
storage/app/proyek/
└── [project-name-slug]/
    ├── dokumen/
    │   ├── kontrak/
    │   ├── perizinan/
    │   └── legal/
    ├── teknis/
    │   ├── desain/
    │   ├── spesifikasi/
    │   └── gambar/
    ├── keuangan/
    │   ├── invoice/
    │   ├── pembayaran/
    │   └── laporan/
    ├── laporan/
    │   ├── progress/
    │   ├── mingguan/
    │   └── bulanan/
    ├── foto/
    │   ├── sebelum/
    │   ├── progress/
    │   └── selesai/
    └── lainnya/
```

## Troubleshooting

### Rclone Not Found

If you get "rclone not found" error:

1. Check rclone is installed: `which rclone`
2. Add rclone to PATH
3. Set full path in `.env`: `RCLONE_PATH=/usr/bin/rclone`

### Permission Errors

Fix storage permissions:

```bash
sudo chown -R www-data:www-data storage/app/proyek
sudo chmod -R 775 storage/app/proyek
```

### Sync Failures

Check rclone configuration:

```bash
# Test connection
rclone lsd gdrive:

# Check config
rclone config show

# View logs
tail -f storage/logs/laravel.log
```

### Large File Uploads

Update PHP configuration:

```ini
; php.ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

Update nginx configuration:

```nginx
# nginx.conf
client_max_body_size 50M;
```

## Security Considerations

1. **Authentication**: All API endpoints require authentication
2. **Authorization**: Users can only access their project documents
3. **File Validation**: Validate file types and sizes
4. **Sanitization**: Sanitize filenames to prevent directory traversal
5. **HTTPS**: Use HTTPS in production
6. **CORS**: Configure CORS if using separate frontend

## Performance Optimization

1. **Queue Sync Jobs**: Use Laravel queues for large sync operations
2. **Chunk Uploads**: Implement chunked uploads for large files
3. **CDN**: Serve documents through CDN for better performance
4. **Caching**: Cache folder structure and sync status
5. **Database Indexes**: Add indexes on frequently queried columns

## Monitoring

Monitor the system using:

1. **Laravel Telescope**: For debugging and monitoring
2. **Log Files**: Check `storage/logs/` for errors
3. **Sync Logs**: Review `sync_logs` table for sync history
4. **Storage Usage**: Monitor disk space usage
5. **Rclone Logs**: Check rclone operation logs

## Backup

Regular backups recommended:

1. **Database**: Backup all document-related tables
2. **Files**: Backup `storage/app/proyek/` directory
3. **Cloud Sync**: Cloud storage serves as additional backup
4. **Configuration**: Backup rclone configuration

## Support

For issues or questions:

1. Check the documentation
2. Review error logs
3. Test rclone connection
4. Verify file permissions
5. Check API responses in browser console

## License

This system is part of the Laravel project and follows the same license.