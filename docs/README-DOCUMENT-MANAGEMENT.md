# Laravel Project Document Management System

## 🚀 Overview

A comprehensive document management system for Laravel projects with organized folder structure, cloud synchronization using rclone, and an intuitive Alpine.js file explorer interface.

## ✨ Key Features

- **📁 Organized Folder Structure**: Automatic creation of categorized folders for each project
- **☁️ Cloud Synchronization**: Manual sync to Google Drive or other cloud storage via rclone
- **🔍 File Explorer**: Interactive Alpine.js-based file manager with grid/list views
- **🔒 Duplicate Prevention**: Validates to prevent duplicate files in the same folder
- **📝 Original Filenames**: Preserves original filenames without timestamp prefixes
- **🔄 Sync Management**: Track sync status, retry failed syncs, verify integrity
- **📊 Comprehensive Logging**: Detailed sync logs and operation history
- **🎯 Bulk Operations**: Select multiple files for batch operations
- **👁️ File Preview**: In-browser preview for images, PDFs, and documents
- **🔐 Security**: Authentication, authorization, and file validation

## 📋 Requirements

- Laravel 8.0+
- PHP 7.4+ or 8.0+
- MySQL/PostgreSQL
- Rclone (for cloud sync)
- Alpine.js (included with Laravel)

## 🛠️ Installation

### Step 1: Copy Files

Copy all the provided files to your Laravel project:

```bash
# Database Migrations
database/migrations/2025_01_29_000001_update_project_documents_for_storage_system.php
database/migrations/2025_01_29_000002_create_project_folders_table.php
database/migrations/2025_01_29_000003_create_sync_logs_table.php

# Models
app/Models/ProjectFolder.php
app/Models/SyncLog.php
# Update existing: app/Models/Project.php
# Update existing: app/Models/ProjectDocument.php

# Services
app/Services/StorageService.php
app/Services/RcloneService.php
app/Services/SyncService.php

# Observer & Providers
app/Observers/ProjectObserver.php
app/Providers/ObserverServiceProvider.php
app/Providers/FileExplorerServiceProvider.php

# Commands
app/Console/Commands/MigrateProjectDocuments.php
app/Console/Commands/SyncProjectToCloud.php

# Controller
app/Http/Controllers/Api/FileExplorerController.php

# Routes
routes/api/file-explorer.php

# Frontend
resources/js/components/file-explorer.js
resources/views/components/file-explorer.blade.php
resources/views/projects/partials/documents-tab.blade.php
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

### Step 3: Register Service Providers

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    App\Providers\ObserverServiceProvider::class,
    App\Providers\FileExplorerServiceProvider::class,
],
```

### Step 4: Configure Rclone

```bash
# Install rclone
sudo apt-get install rclone  # Ubuntu/Debian
brew install rclone           # macOS

# Configure remote
rclone config
# Follow prompts to add Google Drive as 'gdrive'
```

### Step 5: Environment Configuration

Add to `.env`:

```env
RCLONE_REMOTE=gdrive
RCLONE_BASE_PATH=/project-documents
RCLONE_CONFIG_PATH=/home/user/.config/rclone/rclone.conf
```

### Step 6: Create Storage Directories

```bash
mkdir -p storage/app/proyek
chmod -R 775 storage/app/proyek
```

### Step 7: Compile Assets

```bash
# Copy JavaScript file
cp resources/js/components/file-explorer.js public/js/components/

# Or use Laravel Mix
npm run dev
```

## 📁 Folder Structure

The system automatically creates this structure for each project:

```
storage/app/proyek/[project-slug]/
├── dokumen/
│   ├── kontrak/        # Contracts
│   ├── perizinan/      # Permits
│   └── legal/          # Legal documents
├── teknis/
│   ├── desain/         # Designs
│   ├── spesifikasi/    # Specifications
│   └── gambar/         # Drawings
├── keuangan/
│   ├── invoice/        # Invoices
│   ├── pembayaran/     # Payments
│   └── laporan/        # Financial reports
├── laporan/
│   ├── progress/       # Progress reports
│   ├── mingguan/       # Weekly reports
│   └── bulanan/        # Monthly reports
├── foto/
│   ├── sebelum/        # Before photos
│   ├── progress/       # Progress photos
│   └── selesai/        # Completion photos
└── lainnya/            # Other files
```

## 🎯 Usage

### In Your Project View

Add the document tab to your project show page:

```blade
<!-- resources/views/projects/show.blade.php -->
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#documents">Documents</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade" id="documents">
        @include('components.file-explorer', ['project' => $project])
    </div>
</div>
```

### Command Line Operations

```bash
# Migrate existing projects to new structure
php artisan project:migrate-documents --project=34

# Manual sync to cloud
php artisan project:sync --project=34

# Sync all projects
php artisan project:sync --all

# Check sync status
php artisan project:sync --stats

# Test rclone connection
php artisan project:sync --test
```

## 🔌 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/projects/{id}/folders` | Get folder structure |
| GET | `/api/projects/{id}/folders/contents` | Get folder contents |
| POST | `/api/projects/{id}/folders/create` | Create new folder |
| POST | `/api/projects/{id}/documents/upload` | Upload document |
| DELETE | `/api/projects/{id}/documents/{docId}` | Delete document |
| PUT | `/api/projects/{id}/documents/{docId}/rename` | Rename document |
| PUT | `/api/projects/{id}/documents/{docId}/move` | Move document |
| POST | `/api/projects/{id}/sync` | Start sync |
| GET | `/api/projects/{id}/sync/status` | Get sync status |

## 🎨 File Explorer Features

### Views
- **Grid View**: Visual file cards with icons
- **List View**: Detailed table with sorting

### Operations
- **Upload**: Drag & drop or click to upload
- **Download**: Direct file download
- **Preview**: In-browser preview for supported formats
- **Rename**: Change file names while preserving extensions
- **Move**: Relocate files between folders
- **Delete**: Remove files with confirmation
- **Bulk Select**: Select multiple files for batch operations

### Search & Sort
- Real-time file search
- Sort by name, size, or date
- Ascending/descending order

### Sync Status Indicators
- ✅ **Synced**: File is synchronized
- 🕐 **Pending**: Awaiting sync
- 🔄 **Syncing**: Currently syncing
- ❌ **Failed**: Sync failed
- ⚠️ **Out of Sync**: File modified after sync

## 🔧 Configuration

### File Upload Limits

Update `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

Update nginx:
```nginx
client_max_body_size 50M;
```

### Scheduled Sync (Optional)

Add to `app/Console/Kernel.php`:
```php
$schedule->command('project:sync --all')
         ->dailyAt('02:00')
         ->withoutOverlapping();
```

## 🐛 Troubleshooting

### Rclone Issues
```bash
# Check installation
which rclone

# Test connection
rclone lsd gdrive:

# View configuration
rclone config show
```

### Permission Errors
```bash
sudo chown -R www-data:www-data storage/app/proyek
sudo chmod -R 775 storage/app/proyek
```

### Large File Uploads
- Increase PHP limits (see Configuration)
- Check server timeout settings
- Consider chunked upload implementation

## 📊 Database Schema

### project_documents (updated)
- `storage_path`: Full system path to file
- `rclone_path`: Remote path in cloud storage
- `sync_status`: Current sync status
- `sync_error`: Last sync error message
- `last_sync_at`: Timestamp of last successful sync
- `checksum`: File checksum for integrity
- `folder_structure`: JSON structure info

### project_folders (new)
- `project_id`: Related project
- `folder_name`: Folder name
- `folder_path`: Full folder path
- `parent_id`: Parent folder reference
- `folder_type`: Type (root/category/subcategory/custom)
- `sync_status`: Folder sync status

### sync_logs (new)
- Complete sync operation history
- Polymorphic relations for projects/documents
- Timing and error tracking

## 🔒 Security Features

- Authentication required for all operations
- Project-level authorization
- File type validation
- Filename sanitization
- CSRF protection
- Secure file storage outside public directory

## 📈 Performance Optimization

- Lazy loading for large folders
- Chunked file uploads for large files
- Background sync operations (queue-ready)
- Efficient database queries with eager loading
- Client-side caching for folder structure

## 🧪 Testing

Run the comprehensive test suite:

```bash
# Run migrations test
php artisan migrate:fresh --seed

# Test file operations
php artisan project:migrate-documents --dry-run

# Test sync
php artisan project:sync --test
```

See `docs/testing-checklist.md` for complete testing guide.

## 📚 Documentation

- `docs/setup-instructions.md` - Detailed setup guide
- `docs/testing-checklist.md` - Comprehensive testing checklist
- `docs/project-document-system-architecture.md` - System architecture
- `docs/migration-existing-projects.md` - Migration guide
- `docs/manual-sync-configuration.md` - Sync configuration

## 🤝 Contributing

1. Follow Laravel coding standards
2. Write tests for new features
3. Update documentation
4. Submit pull request

## 📄 License

This system is part of your Laravel project and follows the same license.

## 🆘 Support

For issues:
1. Check documentation in `/docs` folder
2. Review error logs in `storage/logs/`
3. Verify rclone configuration
4. Check file permissions
5. Test API endpoints directly

## 🎉 Credits

Built with Laravel, Alpine.js, and rclone for robust document management.

---

**Version**: 1.0.0  
**Last Updated**: January 2025  
**Compatibility**: Laravel 8.0+