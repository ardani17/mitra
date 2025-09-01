# 🚀 INSTANT ADMIN FIX - Bypass Registration

## Quick Solution (Copy & Paste ke Terminal)

### Step 1: SSH ke Server
```bash
ssh user@your-server
cd /www/wwwroot/mitra.cloudnexify.com/mitra
```

### Step 2: Jalankan Command Ini (Ganti dengan ID Telegram Anda)
```bash
php artisan tinker
```

Kemudian paste ini (ganti 731289973 dengan Telegram ID Anda):
```php
\App\Models\BotUser::updateOrCreate(
    ['telegram_id' => '731289973'],
    [
        'username' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role_id' => 1,
        'status' => 'active',
        'approved_at' => now()
    ]
);
exit;
```

### Step 3: Verifikasi
```bash
php artisan tinker
>>> \App\Models\BotUser::where('telegram_id', '731289973')->first();
>>> exit;
```

## Alternative: One-Line Command

Jalankan ini di terminal (ganti ID):
```bash
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

\App\Models\BotUser::updateOrCreate(
    ['telegram_id' => '731289973'],
    [
        'username' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role_id' => 1,
        'status' => 'active',
        'approved_at' => now()
    ]
);

echo 'Admin created successfully!';
"
```

## Setelah Berhasil

Test bot dengan kirim command:
- `/start` - Seharusnya welcome message
- `/help` - Seharusnya tampil semua command
- `/users` - List users (admin only)
- `/pending` - Pending registrations

## Jika Masih "Akses Ditolak"

### 1. Check User Exists
```bash
php artisan tinker
>>> \App\Models\BotUser::all();
```

### 2. Check Roles Exist
```bash
php artisan tinker
>>> \App\Models\BotRole::all();
```

### 3. Force Create Super Admin Role
```bash
php artisan tinker
>>> \App\Models\BotRole::firstOrCreate(
>>>     ['name' => 'super_admin'],
>>>     [
>>>         'display_name' => 'Super Administrator',
>>>         'description' => 'Full system access',
>>>         'permissions' => json_encode(['*']),
>>>         'priority' => 1
>>>     ]
>>> );
```

### 4. Direct SQL Insert (Last Resort)
```sql
-- Connect to database
psql -U postgres -d your_database

-- Insert role if not exists
INSERT INTO bot_roles (id, name, display_name, description, permissions, priority, created_at, updated_at)
VALUES (1, 'super_admin', 'Super Administrator', 'Full system access', '["*"]', 1, NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- Insert user
INSERT INTO bot_users (telegram_id, username, first_name, last_name, role_id, status, approved_at, created_at, updated_at)
VALUES ('731289973', 'admin', 'Admin', 'User', 1, 'active', NOW(), NOW(), NOW())
ON CONFLICT (telegram_id) 
DO UPDATE SET 
    role_id = 1,
    status = 'active',
    approved_at = NOW();
```

## Debug: Why Registration Not Working

### Check Webhook
```bash
# Check webhook URL
curl https://api.telegram.org/botYOUR_BOT_TOKEN/getWebhookInfo
```

### Check Handler Loading
```bash
php artisan tinker
>>> class_exists('\App\Services\Telegram\UserManagementCommandHandler');
>>> class_exists('\App\Services\Telegram\CommandHandler');
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

## WORKING NOW?

Setelah jalankan Step 2, bot seharusnya langsung berfungsi!
Kirim `/help` ke bot untuk test.