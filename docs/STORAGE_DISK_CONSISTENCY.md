# Storage Disk Consistency Documentation

## Overview
This document describes the storage disk configuration and usage consistency for the document management system in the Mitra project.

## Disk Configuration

### Available Disks
The application has three configured storage disks in `config/filesystems.php`:

1. **`local`** - Main storage disk
   - Root: `storage/app/`
   - Used for: Project documents and files

2. **`public`** - Public accessible storage
   - Root: `storage/app/public/`
   - Used for: Billing documents, employee avatars
   - Accessible via URL

3. **`private`** - Private storage (deprecated for documents)
   - Root: `storage/app/private/`
   - Status: Available but not used for project documents

## Consistent Usage for Project Documents

### Principle
All project document operations use the **`local`** disk explicitly to avoid ambiguity and ensure consistency.

### Implementation

#### 1. StorageService (`app/Services/StorageService.php`)
```php
class StorageService
{
    private $disk = 'local'; // Explicit disk declaration
    
    // All storage operations use:
    Storage::disk($this->disk)->operation();
}
```

#### 2. FileExplorerController (`app/Http/Controllers/Api/FileExplorerController.php`)
```php
class FileExplorerController extends Controller
{
    private $disk = 'local'; // Explicit disk declaration
    
    // All storage operations use:
    Storage::disk($this->disk)->operation();
}
```

#### 3. TestFolderOperations (`app/Console/Commands/TestFolderOperations.php`)
```php
class TestFolderOperations extends Command
{
    private $disk = 'local'; // Explicit disk declaration
    
    // All test operations use:
    Storage::disk($this->disk)->operation();
}
```

## Folder Structure

### Project Documents
All project documents are stored under the `local` disk with the following structure:

```
storage/app/
└── proyek/                    # Root folder for all projects
    └── {project-slug}/        # Individual project folder
        ├── dokumen/           # Documents folder
        │   ├── kontrak/       # Contracts
        │   ├── teknis/        # Technical documents
        │   ├── keuangan/      # Financial documents
        │   ├── laporan/       # Reports
        │   └── lainnya/       # Other documents
        ├── gambar/            # Images
        └── video/             # Videos
```

## Benefits of This Approach

1. **No Ambiguity**: Clear which disk is being used for each operation
2. **Easy Maintenance**: All document operations use the same disk
3. **No Breaking Changes**: Other systems (billing, employee) continue to work unchanged
4. **Consistent Path Resolution**: All paths are relative to `storage/app/`

## Migration Notes

### From Mixed Storage to Consistent Storage
If you have files in `storage/app/private/proyek/`, they should be moved to `storage/app/proyek/` for consistency. The `private` disk remains available but is not used for project documents.

### Testing
Run the following command to test folder operations:
```bash
php artisan test:folders
```

## Best Practices

1. **Always use explicit disk declaration** when working with project documents
2. **Use relative paths** from the disk root (e.g., `proyek/project-slug/dokumen/`)
3. **Don't mix disk usage** within the same feature/module
4. **Document any disk changes** in this file

## Related Files

- Configuration: `config/filesystems.php`
- Storage Service: `app/Services/StorageService.php`
- File Explorer Controller: `app/Http/Controllers/Api/FileExplorerController.php`
- API Routes: `routes/api/file-explorer.php`
- Vue Components: `resources/views/components/vue-file-explorer-*.blade.php`

## Maintenance

Last Updated: {{ date('Y-m-d') }}
Updated By: System Refactoring

### Change Log
- **2025-08-30**: Implemented explicit disk usage for all project document operations
- **2025-08-30**: Removed ambiguity between `local` and `private` disks
- **2025-08-30**: Added consistent disk property to all relevant classes