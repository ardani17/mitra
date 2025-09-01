# Telegram Bot Sync Functionality Documentation

## Overview
The Telegram Bot Explorer now includes an advanced synchronization feature that ensures files in local storage are properly synced with Telegram cloud storage. This document explains how the sync functionality works and the differences between the two sync implementations in the system.

## Two Different Sync Implementations

### 1. Project Documents Sync (`StorageDatabaseSyncService`)
**Purpose**: Synchronizes physical files in storage with database records for project documents.

**What it syncs**:
- Physical files in `storage/app/proyek/` directory
- Database records in `project_documents` and `project_folders` tables

**Issues it detects**:
- **Orphaned files**: Files exist in storage but not in database
- **Missing files**: Database records exist but files are missing from storage
- **Modified files**: Files with different size/checksum than database records
- **Orphaned folders**: Folders in storage without database records
- **Missing folders**: Database records for folders that don't exist

**How it fixes issues**:
- Adds orphaned files to database
- Removes or marks missing file records as deleted
- Updates file metadata for modified files
- Creates folder records for orphaned folders
- Removes folder records for missing folders

### 2. Telegram Bot Sync (`TelegramStorageSyncService`)
**Purpose**: Synchronizes local storage files with Telegram bot uploads.

**What it syncs**:
- Local files in `storage/app/proyek/` directory
- Telegram upload records in `bot_activities` table
- Upload queue in `bot_upload_queue` table

**Issues it detects**:
- **Not uploaded**: Files in storage that haven't been uploaded to Telegram
- **Upload failed**: Files that failed to upload (after multiple retries)
- **Upload pending**: Files currently in the upload queue
- **Recently modified**: Files modified after their last upload to Telegram
- **Orphaned uploads**: Upload records without corresponding local files

**How it fixes issues**:
- Queues not-uploaded files for Telegram upload
- Retries failed uploads
- Re-queues modified files for re-upload
- Marks orphaned upload records
- Optionally processes the upload queue immediately

## User Interface

### Sync Button States

1. **Gray (Unknown)**: Initial state, sync status not checked
   - Icon: Check circle
   - Text: "Cek Sinkronisasi" / "Sync"
   - Action: Checks sync status

2. **Green (Synced)**: All files are synchronized
   - Icon: Check circle with checkmark
   - Text: "Tersinkronisasi" / "Synced"
   - Action: Shows sync status modal

3. **Orange (Out of Sync)**: Issues detected
   - Icon: Warning circle
   - Text: "Sinkronkan (N)" / "Sync (N)" where N is issue count
   - Action: Shows sync status modal with details

### Sync Status Modal

The modal displays:
- Overall sync status (Synced/Not Synced)
- Statistics:
  - Local files count and total size
  - Total issues count
  - Bot API files count
  - Last sync timestamp
- Detailed issues breakdown:
  - Number of files not uploaded
  - Number of failed uploads
  - Number of pending uploads
  - Number of modified files
  - Number of orphaned records
- Expandable details for each issue type
- "Synchronize Now" button when issues exist

## How Sync Works

### Check Sync Status Flow
1. User clicks sync button
2. System calls `TelegramStorageSyncService::checkSyncStatus()`
3. Service scans all files in `storage/app/proyek/`
4. Service queries `bot_activities` for successful uploads
5. Service queries `bot_upload_queue` for pending/failed uploads
6. Service compares files with upload records
7. Returns detailed status with issues categorized

### Perform Sync Flow
1. User clicks "Synchronize Now" in modal
2. System calls `TelegramStorageSyncService::performSync()`
3. Service processes each issue type:
   - Creates upload queue entries for not-uploaded files
   - Resets retry count for failed uploads
   - Queues modified files for re-upload
   - Marks orphaned records (optional)
4. Optionally processes queue immediately via `FileProcessingService`
5. Returns results summary

## Configuration Options

### Sync Options
```php
$options = [
    'clean_orphaned' => false,  // Whether to mark orphaned records
    'process_queue' => true,     // Whether to process queue immediately
];
```

### File Size Limits
- Maximum file size for Telegram: 50MB
- Files larger than 50MB are skipped with error message

### Priority Levels
- Normal files: Priority 5
- Modified files: Priority 3 (higher priority)

## Database Schema

### Key Tables
1. **bot_activities**: Stores all bot interactions including file uploads
   - `message_type = 'file'`: Indicates file upload
   - `status`: success/failed/orphaned
   - `file_info`: JSON with file details

2. **bot_upload_queue**: Manages file upload queue
   - `status`: pending/processing/completed/failed
   - `retry_count`: Number of retry attempts
   - `priority`: Upload priority (lower = higher priority)

## Testing

### Manual Testing
1. Navigate to http://localhost:8000/telegram-bot/explorer
2. Click "Cek Sinkronisasi" button
3. Review sync status in modal
4. Click "Synchronize Now" if issues exist
5. Verify results after sync

### Command Line Testing
```bash
php test-telegram-sync.php
```

This test script:
- Shows current sync status
- Lists all detected issues
- Offers to perform sync (with confirmation)
- Displays queue statistics
- Shows recent upload activities

## Error Handling

### Common Issues and Solutions

1. **File too large for Telegram**
   - Issue: File exceeds 50MB limit
   - Solution: File is skipped with error message
   - User action: Manually handle large files

2. **Database constraint errors**
   - Issue: Invalid telegram_user_id
   - Solution: Uses telegram_user_id = 0 for web uploads
   - Wrapped in try-catch to prevent failures

3. **Network errors during upload**
   - Issue: Connection timeout or network failure
   - Solution: File remains in queue for retry
   - Retry count incremented

4. **File moved/deleted during sync**
   - Issue: File disappears between scan and queue
   - Solution: Error logged, sync continues
   - User action: Re-run sync if needed

## Best Practices

1. **Regular Sync Checks**
   - Check sync status periodically
   - Run sync after bulk file operations
   - Monitor failed uploads in queue

2. **Queue Management**
   - Process queue regularly
   - Clean old completed entries
   - Monitor retry counts for persistent failures

3. **Performance Considerations**
   - Sync operations scan entire directory tree
   - Large directories may take time to scan
   - Consider running sync during low-traffic periods

## Differences from Project Documents Sync

| Aspect | Project Documents | Telegram Bot |
|--------|------------------|--------------|
| Purpose | Storage ↔ Database | Storage ↔ Telegram |
| Sync Target | Database records | Telegram cloud |
| File Location | Same (storage/app/proyek) | Same (storage/app/proyek) |
| Issues Detected | 5 types | 5 types (different) |
| Auto-fix | Yes (immediate) | Yes (via queue) |
| Processing | Synchronous | Asynchronous (queue) |
| User Trigger | Manual only | Manual + automatic |
| File Size Limit | None | 50MB (Telegram limit) |

## Future Enhancements

1. **Automatic Sync**
   - Schedule periodic sync checks
   - Auto-sync on file changes
   - Background sync processing

2. **Selective Sync**
   - Choose specific folders to sync
   - Exclude patterns
   - File type filters

3. **Sync History**
   - Track sync operations
   - Show sync logs
   - Sync statistics dashboard

4. **Conflict Resolution**
   - Handle file conflicts
   - Version management
   - Merge strategies

## Conclusion

The Telegram Bot sync functionality provides a robust solution for keeping local files synchronized with Telegram cloud storage. It complements the existing project documents sync by focusing on the Telegram upload workflow rather than database consistency. Together, these two sync systems ensure data integrity across all storage layers of the application.