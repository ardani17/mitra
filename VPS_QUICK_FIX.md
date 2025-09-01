# 🚀 VPS QUICK FIX - Bot Akses Ditolak

## COPY-PASTE LANGSUNG KE VPS

### Method 1: SSH + One Command
```bash
ssh root@your-vps "cd /www/wwwroot/mitra.cloudnexify.com/mitra && echo '{\"allowed_users\":[\"731289973\"]}' > storage/app/allowed_users.json && php artisan cache:clear"
```

### Method 2: Login ke VPS lalu jalankan
```bash
# Login
ssh root@your-vps

# Navigate to project
cd /www/wwwroot/mitra.cloudnexify.com/mitra

# Fix allowed_users.json
echo '{"allowed_users":["731289973"]}' > storage/app/allowed_users.json

# Clear cache
php artisan cache:clear
php artisan config:clear

# Test
cat storage/app/allowed_users.json
```

### Method 3: Via Tinker di VPS
```bash
cd /www/wwwroot/mitra.cloudnexify.com/mitra
php artisan tinker
```

Paste ini:
```php
$data = ['allowed_users' => ['731289973']];
file_put_contents('storage/app/allowed_users.json', json_encode($data));
echo "Fixed: " . file_get_contents('storage/app/allowed_users.json');
exit;
```

## PERMANENT FIX - Update CommandHandler.php

Edit di VPS:
```bash
nano /www/wwwroot/mitra.cloudnexify.com/mitra/app/Services/Telegram/CommandHandler.php
```

Cari (line ~31-34):
```php
if (!$this->telegramService->isUserAllowed($user['id'])) {
    return $this->sendUnauthorizedMessage($chatId);
}
```

Ganti dengan:
```php
// Check new system first, fallback to old
$botUser = \App\Models\BotUser::findByTelegramId($user['id']);
if ($botUser && $botUser->isActive()) {
    // User authorized via new system - continue
} elseif (!$this->telegramService->isUserAllowed($user['id'])) {
    // Not in new system and not in old system
    return $this->sendUnauthorizedMessage($chatId);
}
```

## FULL AUTOMATION SCRIPT

Save as `fix_bot.sh` di VPS:
```bash
#!/bin/bash
PROJECT_DIR="/www/wwwroot/mitra.cloudnexify.com/mitra"
USER_ID="731289973"

cd $PROJECT_DIR

# Backup
cp storage/app/allowed_users.json storage/app/allowed_users.json.bak 2>/dev/null

# Fix allowed_users.json
echo "{\"allowed_users\":[\"$USER_ID\"]}" > storage/app/allowed_users.json

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# Verify
echo "✅ Fixed! Current allowed_users.json:"
cat storage/app/allowed_users.json

# Check database
php artisan tinker --execute="
\$user = \App\Models\BotUser::where('telegram_id', '$USER_ID')->first();
if (\$user) {
    echo 'DB User: ' . \$user->telegram_id . ' Status: ' . \$user->status . PHP_EOL;
}
"

# Restart services if needed
# systemctl restart php8.1-fpm
# systemctl restart nginx

echo "✅ Done! Test bot dengan /start"
```

Run:
```bash
chmod +x fix_bot.sh
./fix_bot.sh
```

## VERIFY FIX

Check di VPS:
```bash
# Check allowed_users.json
cat /www/wwwroot/mitra.cloudnexify.com/mitra/storage/app/allowed_users.json

# Should show:
# {"allowed_users":["731289973"]}
```

## DEBUG JIKA MASIH TIDAK WORK

1. Check logs:
```bash
tail -f /www/wwwroot/mitra.cloudnexify.com/mitra/storage/logs/laravel.log
```

2. Add debug to CommandHandler.php:
```php
\Log::info('Bot Auth Check', [
    'user_id' => $user['id'],
    'allowed' => $this->telegramService->isUserAllowed($user['id']),
    'file' => file_get_contents('storage/app/allowed_users.json')
]);
```

3. Restart services:
```bash
systemctl restart php8.1-fpm
systemctl restart nginx
```

---

**✅ Setelah jalankan salah satu method di atas, bot harusnya langsung bisa diakses!**