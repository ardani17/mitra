# Document Management System - Testing Checklist

## Pre-Testing Setup

- [ ] Database migrations executed successfully
- [ ] Service providers registered in `config/app.php`
- [ ] Rclone installed and configured
- [ ] Storage directories created with proper permissions
- [ ] Environment variables configured in `.env`
- [ ] Frontend assets compiled/copied

## 1. Database Testing

### Migrations
- [ ] Run `php artisan migrate` without errors
- [ ] Verify `project_documents` table has new columns:
  - [ ] storage_path
  - [ ] rclone_path
  - [ ] sync_status
  - [ ] sync_error
  - [ ] last_sync_at
  - [ ] checksum
  - [ ] folder_structure
- [ ] Verify `project_folders` table created
- [ ] Verify `sync_logs` table created

### Rollback Test
- [ ] Run `php artisan migrate:rollback` successfully
- [ ] Run `php artisan migrate` again successfully

## 2. Model Testing

### Project Model
- [ ] Verify `folders()` relationship works
- [ ] Verify `syncLogs()` relationship works
- [ ] Test creating new project triggers observer

### ProjectDocument Model
- [ ] Verify new attributes are fillable
- [ ] Verify `folder()` relationship works
- [ ] Verify `syncLogs()` relationship works

### ProjectFolder Model
- [ ] Test creating folders with parent-child relationships
- [ ] Verify `children()` relationship works
- [ ] Verify `parent()` relationship works
- [ ] Verify `documents()` relationship works

## 3. Service Testing

### StorageService
- [ ] Test `createProjectFolder()` creates directory structure
- [ ] Test `storeDocument()` saves file correctly
- [ ] Test duplicate file prevention works
- [ ] Test `deleteDocument()` removes file
- [ ] Test `getProjectPath()` returns correct path

### RcloneService
- [ ] Test `isAvailable()` detects rclone
- [ ] Test `testConnection()` connects to remote
- [ ] Test `syncToRemote()` uploads files
- [ ] Test `checkRemoteFile()` verifies remote files
- [ ] Test `getRemoteSize()` returns storage info

### SyncService
- [ ] Test `syncProject()` syncs all project files
- [ ] Test `syncDocument()` syncs single file
- [ ] Test `checkSyncStatus()` returns correct stats
- [ ] Test `verifySyncIntegrity()` detects issues
- [ ] Test `retryFailedSyncs()` retries failed uploads

## 4. Observer Testing

### ProjectObserver
- [ ] Create new project creates folder structure
- [ ] Verify all category folders created:
  - [ ] dokumen (kontrak, perizinan, legal)
  - [ ] teknis (desain, spesifikasi, gambar)
  - [ ] keuangan (invoice, pembayaran, laporan)
  - [ ] laporan (progress, mingguan, bulanan)
  - [ ] foto (sebelum, progress, selesai)
  - [ ] lainnya
- [ ] Update project name updates folder paths
- [ ] Delete project preserves files (soft delete)

## 5. Command Testing

### MigrateProjectDocuments Command
```bash
# Dry run test
php artisan project:migrate-documents --dry-run
```
- [ ] Shows what would be migrated
- [ ] No actual changes made

```bash
# Single project migration
php artisan project:migrate-documents --project=34
```
- [ ] Migrates specified project only
- [ ] Creates folder structure
- [ ] Moves existing documents
- [ ] Updates database records

```bash
# All projects migration
php artisan project:migrate-documents
```
- [ ] Asks for confirmation
- [ ] Shows progress bar
- [ ] Displays statistics

### SyncProjectToCloud Command
```bash
# Test connection
php artisan project:sync --test
```
- [ ] Shows rclone status
- [ ] Displays remote info

```bash
# Sync single project
php artisan project:sync --project=34
```
- [ ] Syncs project files
- [ ] Shows progress
- [ ] Updates sync status

```bash
# Show statistics
php artisan project:sync --stats
```
- [ ] Displays sync statistics
- [ ] Shows recent sync operations

```bash
# Verify integrity
php artisan project:sync --project=34 --verify
```
- [ ] Checks remote files
- [ ] Reports issues
- [ ] Offers re-sync option

## 6. API Testing

### Folder Operations
```bash
# Get folder structure
GET /api/projects/{id}/folders
```
- [ ] Returns folder tree
- [ ] Includes sync status
- [ ] Shows document count

```bash
# Get folder contents
GET /api/projects/{id}/folders/contents?path=dokumen/kontrak
```
- [ ] Returns documents in folder
- [ ] Supports filtering by path

```bash
# Create folder
POST /api/projects/{id}/folders/create
{
    "name": "test-folder",
    "parent_path": "dokumen"
}
```
- [ ] Creates new folder
- [ ] Updates folder tree

### Document Operations
```bash
# Upload document
POST /api/projects/{id}/documents/upload
FormData: file, folder, description
```
- [ ] Uploads file successfully
- [ ] Prevents duplicates
- [ ] Returns document info

```bash
# Delete document
DELETE /api/projects/{id}/documents/{docId}
```
- [ ] Deletes file and record
- [ ] Returns success message

```bash
# Rename document
PUT /api/projects/{id}/documents/{docId}/rename
{
    "name": "new-name.pdf"
}
```
- [ ] Renames file
- [ ] Preserves extension
- [ ] Updates database

```bash
# Move document
PUT /api/projects/{id}/documents/{docId}/move
{
    "destination": "teknis/desain"
}
```
- [ ] Moves file to new folder
- [ ] Updates path in database

### Sync Operations
```bash
# Sync project
POST /api/projects/{id}/sync
```
- [ ] Initiates sync process
- [ ] Returns sync status

```bash
# Get sync status
GET /api/projects/{id}/sync/status
```
- [ ] Returns current sync status
- [ ] Shows statistics

### Document Access
```bash
# Download document
GET /api/documents/{id}/download
```
- [ ] Downloads file
- [ ] Checks permissions

```bash
# Preview document
GET /api/documents/{id}/preview
```
- [ ] Shows preview for supported types
- [ ] Falls back to download

## 7. Frontend Testing

### File Explorer Component
- [ ] Component loads without errors
- [ ] Alpine.js initializes properly

### Folder Navigation
- [ ] Click folder to navigate
- [ ] Breadcrumbs work correctly
- [ ] Navigate up button works
- [ ] Folder tree displays correctly

### View Modes
- [ ] Grid view displays correctly
- [ ] List view displays correctly
- [ ] Toggle between views works

### File Operations
- [ ] Upload modal opens
- [ ] Multiple file upload works
- [ ] Drag and drop upload works
- [ ] File preview works for images/PDFs
- [ ] Download file works
- [ ] Delete file with confirmation
- [ ] Rename file modal works
- [ ] Move file modal works

### Selection
- [ ] Single file selection works
- [ ] Multiple file selection works
- [ ] Select all works
- [ ] Clear selection works
- [ ] Bulk delete works

### Search and Sort
- [ ] Search filters documents
- [ ] Sort by name works
- [ ] Sort by size works
- [ ] Sort by date works
- [ ] Sort order toggle works

### Sync Features
- [ ] Sync button triggers sync
- [ ] Sync status updates
- [ ] Progress indicator shows
- [ ] Sync completion notification

## 8. Integration Testing

### Project Show Page
- [ ] Documents tab displays
- [ ] File explorer loads in tab
- [ ] All features work within tab

### Permissions
- [ ] Users can only access own projects
- [ ] Permission checks work on API
- [ ] Unauthorized access returns 403

### Error Handling
- [ ] Network errors handled gracefully
- [ ] File upload errors show messages
- [ ] Sync failures logged properly
- [ ] User sees friendly error messages

## 9. Performance Testing

### File Operations
- [ ] Upload 10MB file successfully
- [ ] Upload 50MB file successfully
- [ ] Upload multiple files simultaneously
- [ ] Large folder loads quickly

### Sync Performance
- [ ] Sync 100 files successfully
- [ ] Sync handles interruptions
- [ ] Resume sync after failure

## 10. Security Testing

### File Upload
- [ ] File type validation works
- [ ] File size limits enforced
- [ ] Filename sanitization works
- [ ] No directory traversal possible

### API Security
- [ ] Authentication required
- [ ] CSRF protection works
- [ ] Rate limiting (if configured)

## 11. Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers

## 12. Production Readiness

- [ ] Error logging configured
- [ ] Backup strategy in place
- [ ] Monitoring setup
- [ ] Documentation complete
- [ ] Performance acceptable
- [ ] Security review passed

## Test Data Setup

Create test scenario:
1. Create test project
2. Upload various file types:
   - [ ] PDF documents
   - [ ] Images (JPG, PNG)
   - [ ] Office documents (DOC, XLS)
   - [ ] Text files
   - [ ] Large files (>10MB)
3. Create folder structure
4. Test sync operations
5. Verify cloud storage

## Troubleshooting Tests

### Common Issues
- [ ] Fix permission errors
- [ ] Handle missing rclone
- [ ] Recover from sync failures
- [ ] Handle full disk
- [ ] Fix corrupted uploads

## Sign-off

- [ ] All critical features tested
- [ ] No blocking bugs found
- [ ] Performance acceptable
- [ ] Security validated
- [ ] Documentation complete
- [ ] Ready for production

**Tested by:** _________________  
**Date:** _________________  
**Environment:** _________________  
**Notes:** _________________