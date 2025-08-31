# Storage-Database Synchronization Documentation

## Overview
The synchronization system ensures consistency between physical files in Laravel storage and their metadata records in the database.

## Features

### 1. Sync Check Button
- **Mobile**: ✅ Working
- **Desktop**: ✅ Fixed (methods were missing, now added)

### 2. Synchronization Capabilities
- Detects 5 types of sync issues:
  - Orphaned files (in storage but not in DB)
  - Missing files (in DB but not in storage)
  - Modified files (different size/checksum)
  - Orphaned folders (in storage but not in DB)
  - Missing folders (in DB but not in storage)

### 3. Artisan Commands

#### Check Project Documents
```bash
# Basic check - shows summary
php artisan project:check-documents {project_id}

# Show all folders in database
php artisan project:check-documents {project_id} --show-folders

# Show all files in database
php artisan project:check-documents {project_id} --show-files

# Check if files exist in storage
php artisan project:check-documents {project_id} --check-storage

# Show detailed information
php artisan project:check-documents {project_id} --detailed

# Combine options for comprehensive check
php artisan project:check-documents 1 --show-files --show-folders --check-storage --detailed
```

#### Example Output:
```
========================================
Project: 3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-16
Code: PRJ-2025-08-001
ID: 1
========================================

📊 SUMMARY
├─ Total Documents: 1
├─ Total Folders: 7
└─ Total Size: 162.94 KB

📁 DOCUMENT TYPES
├─ other: 1 files

📄 FILES IN DATABASE
[Table showing file details including ID, Name, Size, Path, etc.]

🔍 STORAGE CHECK
✅ Files exist in storage: 1

📦 ORPHANED FILES CHECK
✅ No orphaned files found
```

## API Endpoints

### Check Sync Status
```
GET /api/file-explorer/project/{project}/check-sync
```

Response:
```json
{
  "success": true,
  "data": {
    "is_synced": false,
    "issues": {
      "orphaned_files": [],
      "missing_files": [],
      "modified_files": [],
      "orphaned_folders": [],
      "missing_folders": []
    },
    "stats": {
      "total_issues": 5,
      "orphaned_files": 0,
      "missing_files": 2,
      "modified_files": 0,
      "orphaned_folders": 3,
      "missing_folders": 0
    }
  }
}
```

### Perform Synchronization
```
POST /api/file-explorer/project/{project}/sync-storage-db
```

Request Body:
```json
{
  "soft_delete": false
}
```

Response:
```json
{
  "success": true,
  "message": "Synchronization completed successfully",
  "data": {
    "results": {
      "added_files": 0,
      "removed_files": 2,
      "updated_files": 0,
      "added_folders": 3,
      "removed_folders": 0,
      "errors": []
    }
  }
}
```

## Testing

### Test Script
```bash
# Run comprehensive test
php test-sync.php
```

### Manual Browser Testing
1. Open project document page
2. Click "Cek Sinkronisasi" button
3. If issues found, click "Sinkronkan (X)" to fix

### Browser Console Test
```javascript
// Check sync status
fetch('/api/file-explorer/project/28/check-sync', {
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => console.log('Sync Status:', data));
```

## Troubleshooting

### Desktop Button Not Working
**Issue**: Button shows but doesn't respond to clicks
**Solution**: The sync methods were missing in the desktop component. This has been fixed by adding:
- `checkSync()` method
- `performSync()` method  
- `handleSyncButton()` method

### Common Issues
1. **404 Error**: Check if routes are properly registered
2. **500 Error**: Check Laravel logs for server errors
3. **Permission Issues**: Ensure storage folder has proper permissions
4. **Missing Files**: Use sync button to clean up database records

## File Structure
```
app/
├── Services/
│   └── StorageDatabaseSyncService.php    # Core sync logic
├── Http/Controllers/Api/
│   └── FileExplorerController.php        # API endpoints
├── Console/Commands/
│   └── CheckProjectDocuments.php         # Artisan command
resources/views/components/
├── vue-file-explorer-advanced.blade.php  # Desktop component
└── vue-file-explorer-mobile.blade.php    # Mobile component
```

## Database Tables
- `project_documents` - Stores file metadata
- `project_folders` - Stores folder structure

## Storage Location
Files are stored in: `storage/app/proyek/{project-slug}/`