# Authentication Fix for Folder Tree API

## Problem Identified
The folder tree was showing "Failed to load folder contents after multiple attempts" because:
1. The API endpoint `/telegram-bot/folder-tree-lazy` requires authentication
2. The fetch requests weren't sending the CSRF token
3. The fetch requests weren't including session cookies
4. This caused all API calls to return "Unauthenticated" (401 error)

## Root Cause Analysis
```bash
curl -X GET "http://localhost:8000/telegram-bot/folder-tree-lazy"
# Response: {"message":"Unauthenticated."}
```

The route is protected by Laravel's authentication middleware, requiring:
- Valid session cookie
- CSRF token for security
- Proper AJAX headers

## Solution Implemented

### Added Authentication Headers
```javascript
// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Include in fetch request
fetch(url, {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken || ''  // ← Added CSRF token
  },
  credentials: 'same-origin'  // ← Include cookies for session
});
```

## Files Modified
- `resources/js/components/FolderTreeExplorer.vue`
  - Added CSRF token extraction from meta tag
  - Added X-CSRF-TOKEN header to all fetch requests
  - Added credentials: 'same-origin' to include session cookies

## Testing Steps
1. Clear browser cache (Ctrl+F5)
2. Navigate to `/telegram-bot/explorer`
3. Click Copy or Move button
4. Expand folders - should work without authentication errors

## Why This Fix Works
1. **CSRF Token**: Laravel requires this for all non-GET requests and authenticated GET requests
2. **Session Cookies**: The `credentials: 'same-origin'` ensures the browser sends the session cookie
3. **AJAX Headers**: The `X-Requested-With: XMLHttpRequest` identifies the request as AJAX

## Prevention for Future
Always remember when making AJAX requests in Laravel:
- Include CSRF token from `<meta name="csrf-token">`
- Set `credentials: 'same-origin'` for authenticated endpoints
- Add proper AJAX headers