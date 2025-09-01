# Telegram Bot Explorer - Button Implementation Test Guide

## Implementation Summary

We have successfully added three buttons to the Telegram Bot Explorer page:

### 1. **Upload Button** (Green)
- **Location**: Top-left of the explorer page
- **Functionality**: Opens a modal for file upload with drag-and-drop support
- **Backend Route**: `POST /telegram-bot/upload`
- **Controller Method**: `TelegramBotController@uploadFiles`

### 2. **Refresh Button** (Blue)
- **Location**: Next to Upload button
- **Functionality**: Reloads the current page
- **Backend Route**: Not needed (client-side only)
- **Controller Method**: Not needed

### 3. **Cek Sinkronisasi Button** (Dynamic color)
- **Location**: Next to Refresh button
- **Functionality**: 
  - First click: Checks sync status (GET request)
  - Second click (if not synced): Performs synchronization (POST request)
- **Backend Routes**: 
  - `GET /telegram-bot/check-sync`
  - `POST /telegram-bot/sync-storage`
- **Controller Methods**: 
  - `TelegramBotController@checkSyncStatus`
  - `TelegramBotController@syncStorage`

## Files Modified

1. **Frontend**:
   - `resources/views/telegram-bot/explorer.blade.php` - Added buttons and JavaScript functionality

2. **Backend**:
   - `routes/web.php` - Added three new routes
   - `app/Http/Controllers/TelegramBotController.php` - Added three new methods

## Testing Instructions

### Prerequisites
1. Ensure Laravel server is running at http://localhost:8000
2. Login as a user with 'direktur' role
3. Navigate to http://localhost:8000/telegram-bot/explorer

### Test 1: Visual Verification
1. Check that three buttons appear at the top of the page:
   - Upload (green button with upload icon)
   - Refresh (blue button with refresh icon)
   - Cek Sinkronisasi (gray button initially)

### Test 2: Upload Functionality
1. Click the "Upload" button
2. Verify that a modal appears with:
   - Drag and drop zone
   - File input field
   - Upload and Cancel buttons
3. Try uploading a file:
   - Either drag and drop a file
   - Or click "Choose Files" and select files
4. Click "Upload Files"
5. Check for success message
6. Verify file appears in the explorer list

### Test 3: Refresh Functionality
1. Click the "Refresh" button
2. Verify the page reloads
3. Check that any new files appear in the list

### Test 4: Sync Check Functionality
1. Click "Cek Sinkronisasi" button
2. Observe the button color change:
   - Gray: Checking status
   - Green: Fully synced
   - Orange: Sync needed
3. If orange, click again to perform sync
4. Wait for sync to complete
5. Button should turn green when synced

## API Endpoints Test

You can test the API endpoints directly:

### Test Upload Endpoint
```bash
curl -X POST http://localhost:8000/telegram-bot/upload \
  -H "X-CSRF-TOKEN: {your-csrf-token}" \
  -F "files[]=@/path/to/test-file.txt" \
  -F "path=test-folder"
```

### Test Sync Check Endpoint
```bash
curl -X GET http://localhost:8000/telegram-bot/check-sync \
  -H "X-CSRF-TOKEN: {your-csrf-token}"
```

### Test Sync Storage Endpoint
```bash
curl -X POST http://localhost:8000/telegram-bot/sync-storage \
  -H "X-CSRF-TOKEN: {your-csrf-token}"
```

## Expected Behavior

### Upload Button
- Opens modal immediately
- Shows file count when files selected
- Displays progress during upload
- Shows success/error messages
- Closes modal after successful upload
- Page refreshes to show new files

### Refresh Button
- Page reloads immediately
- Maintains current folder path
- Updates file list

### Sync Button
- Changes color based on sync status
- Shows "Checking..." text during status check
- Shows "Syncing..." text during sync operation
- Displays appropriate messages for success/failure

## Troubleshooting

### If buttons don't appear:
1. Clear Laravel cache: `php artisan cache:clear`
2. Clear browser cache
3. Check browser console for JavaScript errors

### If upload fails:
1. Check file permissions on `storage/app/proyek` directory
2. Verify CSRF token is being sent
3. Check Laravel logs: `storage/logs/laravel.log`

### If sync doesn't work:
1. Ensure BotConfiguration is properly set up
2. Check if TelegramService is configured
3. Verify database migrations are run

## Success Criteria

✅ All three buttons are visible on the explorer page
✅ Upload button opens modal and allows file upload
✅ Refresh button reloads the page
✅ Sync button checks status and performs synchronization
✅ All backend routes respond correctly
✅ No JavaScript errors in browser console
✅ Files are properly stored in `storage/app/proyek`

## Notes

- The implementation matches the functionality from the Project Documents tab
- Uses vanilla JavaScript (not Vue.js) for consistency with the explorer page
- Includes proper error handling and user feedback
- Maintains security with CSRF protection and path validation