# Folder Tree Debug Guide

## Current Status
- Fixed Vue 3 compatibility issue (removed `this.$set`)
- Added comprehensive debug logging
- Rebuilt assets successfully

## How to Test and Debug

### 1. Clear Browser Cache
Before testing, clear your browser cache:
- Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
- Select "Cached images and files"
- Clear data

Or do a hard refresh:
- Press `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)

### 2. Open Browser Console
1. Navigate to: http://localhost:8000/telegram-bot/explorer
2. Open Developer Tools: Press `F12` or right-click → "Inspect"
3. Go to the "Console" tab
4. Clear console: Click the clear button or press `Ctrl + L`

### 3. Test Folder Expansion
1. Click on any folder to expand it
2. Watch the console for debug messages

### Expected Console Output
When clicking a folder, you should see:
```
[DEBUG] handleNodeExpand called with path: folder-name
[DEBUG] Loading from server: folder-name
[DEBUG] CSRF Token present: true
[DEBUG] Fetching URL: http://localhost:8000/telegram-bot/folder-tree-lazy?parent=folder-name&exclude=
[DEBUG] Response status: 200
[DEBUG] Response data: {success: true, folders: [...]}
[DEBUG] Successfully loaded X folders
[DEBUG] Found folder to update: true
[DEBUG] Updated folder children: {...}
[DEBUG] Expanded nodes: ["folder-name"]
[DEBUG] Finished loading: folder-name
```

### 4. Common Issues and Solutions

#### Issue: "TypeError: this.$set is not a function"
**Status:** FIXED
- Vue 3 doesn't have `$set` method
- Changed to direct assignment: `folder.children = data.folders`

#### Issue: "Unauthenticated" Error
**Status:** FIXED
- Added CSRF token to all requests
- Added `credentials: 'same-origin'` to include session cookies

#### Issue: Folders Not Expanding
**Check:**
1. Look for error messages in console
2. Check Network tab for failed requests
3. Verify the API response contains folders

#### Issue: Loading Spinner Stuck
**Check:**
1. Look for JavaScript errors in console
2. Check if API request completed
3. Verify response format is correct

### 5. API Response Format
The API should return:
```json
{
  "success": true,
  "folders": [
    {
      "name": "folder-name",
      "path": "parent/folder-name",
      "hasChildren": true,
      "children": null
    }
  ]
}
```

### 6. Testing Deep Nesting
1. Start with root folder
2. Expand first level folder
3. Continue expanding subfolders
4. Test up to 20+ levels deep

### 7. Network Tab Analysis
1. Go to Network tab in Developer Tools
2. Filter by "XHR" or "Fetch"
3. Click a folder to expand
4. Check the request:
   - URL should be: `/telegram-bot/folder-tree-lazy`
   - Status should be: 200
   - Response should contain folder data

### 8. If Still Not Working

#### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

#### Check PHP Error Log
```bash
# Windows (Laragon)
C:\laragon\bin\php\php-8.x.x\logs\php_error.log

# Or check Laragon's log viewer
```

#### Verify Routes
```bash
php artisan route:list | grep folder-tree
```

#### Clear Laravel Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 9. Manual API Test
Test the API directly in browser:
```
http://localhost:8000/telegram-bot/folder-tree-lazy?parent=&exclude=
```

Should return JSON with root folders.

### 10. Vue DevTools
Install Vue DevTools browser extension to inspect:
- Component state
- Props
- Events
- Data changes

## Current Implementation Details

### Files Modified
1. `resources/js/components/FolderTreeExplorer.vue` - Added debug logging, fixed Vue 3 compatibility
2. `resources/js/components/FolderTreeNode.vue` - Recursive component
3. `app/Http/Controllers/TelegramBotController.php` - API endpoint
4. `vite.config.js` - Build configuration

### Key Features
- Lazy loading (loads on demand)
- Unlimited depth (up to 20 levels)
- Caching (prevents redundant API calls)
- Retry logic (2 retries on failure)
- CSRF protection
- Session authentication

## Next Steps
1. Test with the debug logging enabled
2. Share any error messages from console
3. Check Network tab for API responses
4. Verify folder structure in database/filesystem