# VPS Deployment Troubleshooting - Mitra Project

## Masalah: Route Login 404 Not Found

### Kemungkinan Penyebab dan Solusi

## 1. Cache Issues (Paling Umum)

**Gejala**: Route login mengembalikan 404 di VPS tapi bekerja di local

**Solusi**:
```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Untuk production, cache ulang
php artisan config:cache
php artisan route:cache
```

## 2. Document Root Configuration

**Gejala**: Semua route mengembalikan 404

**Cek**: Pastikan document root mengarah ke folder `public`

**Apache Virtual Host**:
```apache
<VirtualHost *:80>
    ServerName mitra.cloudnexify.com
    DocumentRoot /path/to/mitra/public
    
    <Directory /path/to/mitra/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx Configuration**:
```nginx
server {
    listen 80;
    server_name mitra.cloudnexify.com;
    root /path/to/mitra/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 3. File Permissions

**Cek permissions**:
```bash
# Set correct permissions
chmod -R 755 /path/to/mitra
chmod -R 775 /path/to/mitra/storage
chmod -R 775 /path/to/mitra/bootstrap/cache
chown -R www-data:www-data /path/to/mitra
```

## 4. .htaccess Issues (Apache)

**Cek file** `/path/to/mitra/public/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Enable mod_rewrite** (Apache):
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 5. Environment Configuration

**Cek file** `.env`:
```env
APP_NAME="Mitra"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://mitra.cloudnexify.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mitra_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 6. Composer Dependencies

**Install dependencies**:
```bash
composer install --optimize-autoloader --no-dev
```

## 7. Database Migration

**Run migrations**:
```bash
php artisan migrate --force
php artisan db:seed --force
```

## Diagnostic Commands

### 1. Jalankan Script Diagnostik
```bash
php check_routes_debug.php
```

### 2. Test Routes Manual
```bash
php artisan route:list --name=login
curl -I https://mitra.cloudnexify.com/login
```

### 3. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### 4. Check Web Server Logs
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

## Quick Fix Script

Buat file `fix_deployment.sh`:
```bash
#!/bin/bash
echo "Fixing Mitra deployment..."

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set permissions
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev

# Cache for production
php artisan config:cache
php artisan route:cache

# Test route
php artisan route:list --name=login

echo "Deployment fix completed!"
```

Jalankan:
```bash
chmod +x fix_deployment.sh
./fix_deployment.sh
```

## Verifikasi Final

1. **Test route login**:
   ```bash
   curl -I https://mitra.cloudnexify.com/login
   ```

2. **Expected response**:
   ```
   HTTP/1.1 200 OK
   Content-Type: text/html; charset=UTF-8
   ```

3. **Test di browser**:
   - Buka https://mitra.cloudnexify.com
   - Klik "Masuk" 
   - Harus redirect ke halaman login

## Kontak Support

Jika masih bermasalah, kirim informasi berikut:
1. Output dari `php check_routes_debug.php`
2. Web server error logs
3. Laravel error logs
4. Virtual host configuration
5. File permissions output: `ls -la`

---

**Catatan**: Pastikan semua command dijalankan dari directory root project Mitra di VPS.
