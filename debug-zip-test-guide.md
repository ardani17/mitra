# Debug Guide: Download ZIP Feature Testing

## Test Routes Created

I've created several test routes to help diagnose the Download ZIP issue. These routes are available at:

### 1. Environment Check
**URL:** `http://127.0.0.1:8000/test-zip-env`
**Method:** GET
**Purpose:** Check if all paths, permissions, and PHP extensions are properly configured

This will show:
- Project details (ID, name, slug)
- File paths being used
- Directory existence and permissions
- PHP ZIP extension status
- Authentication status
- Folder contents

### 2. Simple ZIP Creation Test
**URL:** `http://127.0.0.1:8000/test-zip-create`
**Method:** GET
**Purpose:** Test if PHP can create ZIP files at all

This will:
- Create a simple test ZIP file
- Add a test text file to it
- Report success/failure with details

### 3. Folder ZIP Test
**URL:** `http://127.0.0.1:8000/test-zip-folder/37`
**Method:** GET
**Purpose:** Test creating a ZIP from the actual project folder

This will:
- Try to ZIP the "dokumen" folder from project 37
- Count files processed
- Report ZIP creation status

### 4. API Endpoint Test
**URL:** `http://127.0.0.1:8000/test-api-download-zip/37`
**Method:** POST
**Headers Required:**
```json
{
  "Content-Type": "application/json",
  "X-CSRF-TOKEN": "[Your CSRF Token]"
}
```
**Body:**
```json
{
  "folder_path": "dokumen"
}
```
**Purpose:** Test the API endpoint configuration without actually creating a ZIP

## Testing Steps

### Step 1: Check Environment
1. Open your browser
2. Navigate to: `http://127.0.0.1:8000/test-zip-env`
3. Check the JSON response for:
   - `checks.zip_extension` should be `true`
   - `checks.folder_exists` should be `true`
   - `checks.temp_is_writable` should be `true`
   - `auth.user_email` should show your email
   - `folder_contents` should list files in the folder

### Step 2: Test ZIP Creation
1. Navigate to: `http://127.0.0.1:8000/test-zip-create`
2. Check for:
   - `success: true`
   - `file_created: true`
   - `file_size` should be > 0

### Step 3: Test Folder ZIP
1. Navigate to: `http://127.0.0.1:8000/test-zip-folder/37`
2. Check for:
   - `success: true`
   - `files_processed` should be > 0
   - `zip_created: true`
   - `zip_size` should be > 0

### Step 4: Test API Endpoint (Using Browser Console)
1. Open the project page: `http://127.0.0.1:8000/projects/37`
2. Open browser console (F12)
3. Run this test:

```javascript
// Test the API endpoint
fetch('/test-api-download-zip/37', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ folder_path: 'dokumen' })
})
.then(response => response.json())
.then(data => console.log('API Test Result:', data))
.catch(error => console.error('API Test Error:', error));
```

## Common Issues and Solutions

### Issue 1: PHP ZIP Extension Not Installed
**Symptom:** `zip_extension: false` in test-zip-env
**Solution:**
```bash
# For Ubuntu/Debian
sudo apt-get install php-zip

# For CentOS/RHEL
sudo yum install php-zip

# For Windows with XAMPP/Laragon
# Enable in php.ini: extension=zip
```

### Issue 2: Permission Issues
**Symptom:** `temp_is_writable: false` or `is_readable: false`
**Solution:**
```bash
# Fix storage permissions
chmod -R 775 storage
chmod -R 775 storage/app/temp
chown -R www-data:www-data storage  # Linux
```

### Issue 3: CSRF Token Mismatch
**Symptom:** 419 error
**Solution:**
- Clear browser cache
- Logout and login again
- Check session configuration

### Issue 4: Route Not Found
**Symptom:** 404 error
**Solution:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Check if route exists
php artisan route:list | grep download-zip
```

### Issue 5: Authentication Issues
**Symptom:** 401 error or redirect to login
**Solution:**
- Ensure you're logged in
- Check session hasn't expired
- Try incognito/private browsing mode

## Checking Laravel Logs

Always check the Laravel log for detailed error messages:

```bash
# Watch the log in real-time
tail -f storage/logs/laravel.log

# Or check the last 50 lines
tail -n 50 storage/logs/laravel.log
```

## Testing the Original Feature

After running the test routes successfully, try the original feature again:

1. Go to: `http://127.0.0.1:8000/projects/37`
2. Navigate to the Documents tab
3. Right-click on a folder
4. Select "Download ZIP"
5. Check browser console for debug output

## Debug Output to Share

When reporting issues, please provide:

1. **Screenshot of test-zip-env output**
2. **Browser Console errors** (F12 → Console tab)
3. **Network tab details** (F12 → Network tab → find the failed request)
4. **Laravel log entries** related to the error
5. **Results from each test route**

## Cleanup

After debugging is complete, remember to:
1. Remove the test routes file: `routes/test-zip.php`
2. Remove the include from `routes/web.php`
3. Clear route cache: `php artisan route:cache`

## Additional Commands

```bash
# Check PHP version and extensions
php -v
php -m | grep zip

# Check Laravel version
php artisan --version

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check file permissions
ls -la storage/app/
ls -la storage/app/temp/