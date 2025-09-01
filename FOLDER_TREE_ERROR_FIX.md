# Folder Tree Expansion Error Fix

## Problem
When clicking to expand a folder, an error "Failed to load folder contents. Please try again" appeared on the first click, but worked on the second click.

## Root Cause
1. **Relative URL Issue**: The API endpoint was using a relative URL (`/telegram-bot/folder-tree-lazy`) which could cause routing issues
2. **Missing Headers**: The fetch request wasn't sending proper headers to identify it as an AJAX request
3. **No Retry Logic**: The first request would fail but no automatic retry was implemented

## Solution Applied

### 1. Use Absolute URLs
Changed from:
```javascript
fetch('/telegram-bot/folder-tree-lazy?' + params)
```

To:
```javascript
const baseUrl = window.location.origin;
fetch(`${baseUrl}/telegram-bot/folder-tree-lazy?` + params)
```

### 2. Added Proper Headers
```javascript
fetch(url, {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})
```

### 3. Implemented Retry Logic
- Automatically retries up to 2 times if the request fails
- Adds a 500ms delay between retries
- Only shows error message after all retries are exhausted

## Files Modified
- `resources/js/components/FolderTreeExplorer.vue` - Added absolute URLs, headers, and retry logic

## Testing
After rebuilding with `npm run build`:
1. Navigate to `/telegram-bot/explorer`
2. Click Copy or Move button
3. Expand folders - should work on first click
4. No more "Failed to load" errors on initial expansion

## Benefits
- ✅ Folders expand on first click
- ✅ More robust error handling
- ✅ Automatic retry for transient failures
- ✅ Better user experience