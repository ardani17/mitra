# 🔧 Production Fix Guide - Telegram Bot User Management

## Error yang Terjadi
```
The /www/wwwroot/mitra.cloudnexify.com/mitra/bootstrap/cache directory must be present and writable.
```

## Cara Perbaiki di Production

### Metode 1: Gunakan Script Otomatis

1. **Upload script ke server:**
   ```bash
   scp fix-production-telegram-bot.sh user@your-server:/tmp/
   ```

2. **SSH ke server:**
   ```bash
   ssh user@your-server
   ```

3. **Jalankan script:**
   ```bash
   cd /www/wwwroot/mitra.cloudnexify.com/mitra
   bash /tmp/fix-production-telegram-bot.sh
   ```

### Metode 2: Manual Step by Step

1. **SSH ke server production:**
   ```bash
   ssh user@your-server
   cd /www/wwwroot/mitra.cloudnexify.com/mitra
   ```

2. **Buat directory yang diperlukan:**
   ```bash
   mkdir -p bootstrap/cache
   mkdir -p storage/framework/cache/data
   mkdir -p storage/framework/sessions
   mkdir -p storage/framework/views
   mkdir -p storage/logs
   ```

3. **Fix permissions:**
   ```bash
   # Ganti www-data dengan user web server Anda (bisa jadi nginx atau apache)
   sudo chown -R www-data:www-data bootstrap/cache
   sudo chown -R www-data:www-data storage
   sudo chmod -R 775 bootstrap/cache
   sudo chmod -R 775 storage
   ```

4. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

5. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

   Jika error, jalankan satu per satu:
   ```bash
   php artisan migrate --path=database/migrations/2025_09_01_131732_create_bot_users_table.php --force
   php artisan migrate --path=database/migrations/2025_09_01_131802_create_bot_roles_table.php --force
   php artisan migrate --path=database/migrations/2025_09_01_131819_create_bot_registration_requests_table.php --force
   php artisan migrate --path=database/migrations/2025_09_01_131841_create_bot_user_activity_logs_table.php --force
   php artisan migrate --path=database/migrations/2025_09_01_150000_add_processed_at_to_bot_registration_requests.php --force
   php artisan migrate --path=database/migrations/2025_09_01_132319_migrate_existing_allowed_users_to_bot_users.php --force
   ```

6. **Optimize untuk production:**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

7. **Restart services:**
   ```bash
   # PHP-FPM (pilih sesuai versi PHP Anda)
   sudo systemctl restart php8.2-fpm
   # atau
   sudo systemctl restart php8.1-fpm
   # atau
   sudo systemctl restart php7.4-fpm

   # Web Server
   sudo systemctl restart nginx
   # atau
   sudo systemctl restart apache2
   ```

### Metode 3: Quick Fix (Jika Urgent)

Jika sangat urgent dan hanya ingin fix error cache:

```bash
cd /www/wwwroot/mitra.cloudnexify.com/mitra
mkdir -p bootstrap/cache
sudo chown -R www-data:www-data bootstrap/cache storage
sudo chmod -R 775 bootstrap/cache storage
php artisan cache:clear
php artisan config:cache
```

## Setelah Fix Berhasil

1. **Assign admin role untuk diri sendiri:**
   ```bash
   php artisan bot:assign-admin YOUR_TELEGRAM_ID --role=super_admin
   ```

2. **Test halaman:**
   - Buka: https://mitra.cloudnexify.com/telegram-bot/users
   - Buka: https://mitra.cloudnexify.com/telegram-bot/registrations

3. **Test bot commands:**
   - Kirim `/register` ke bot dari akun test
   - Cek halaman registrations
   - Approve user dari web interface

## Troubleshooting

### Jika masih error setelah fix:

1. **Check error log:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check PHP error:**
   ```bash
   tail -f /var/log/php8.1-fpm.log
   ```

3. **Check web server error:**
   ```bash
   tail -f /var/log/nginx/error.log
   ```

4. **Pastikan .env file ada:**
   ```bash
   ls -la .env
   # Jika tidak ada, copy dari .env.example
   cp .env.example .env
   php artisan key:generate
   ```

5. **Check database connection:**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

### Common Issues & Solutions:

| Error | Solution |
|-------|----------|
| Permission denied | `sudo chown -R www-data:www-data .` |
| Class not found | `composer dump-autoload` |
| Migration error | Check if table already exists, drop and re-run |
| 500 Error | Check `storage/logs/laravel.log` |
| Blank page | `php artisan view:clear` |

## Contact Support

Jika masih ada masalah setelah mengikuti guide ini:

1. Screenshot error message
2. Copy last 50 lines dari laravel.log
3. Kirim ke tim development

---

**Note:** Selalu backup database sebelum run migrations di production!

```bash
# Backup command untuk PostgreSQL
pg_dump -U postgres -d your_database > backup_$(date +%Y%m%d_%H%M%S).sql