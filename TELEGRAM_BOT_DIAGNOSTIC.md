# Telegram Bot Integration - Diagnostic & Testing Guide

## 🔍 Diagnostic Checklist

### 1. Database Verification
Run these commands to verify database setup:

```bash
# Check if tables exist
php artisan tinker
>>> \DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE 'bot_%'");

# Check migration status
php artisan migrate:status | grep bot

# If tables are missing, run:
php artisan migrate
```

### 2. Route Verification
```bash
# List all telegram-bot routes
php artisan route:list | grep telegram

# Expected output should show:
# GET|HEAD  telegram-bot/config
# POST      telegram-bot/config  
# GET|HEAD  telegram-bot/explorer
# GET|HEAD  telegram-bot/activity
# GET|HEAD  telegram-bot/allowed-users
# POST      telegram-bot/allowed-users
# DELETE    telegram-bot/allowed-users
# POST      api/telegram/webhook
```

### 3. Permission Verification
```bash
# Check user role in tinker
php artisan tinker
>>> $user = \App\Models\User::find(1); // Replace 1 with your user ID
>>> $user->roles->pluck('name');
# Should show: ['direktur']
```

### 4. View Files Verification
```bash
# Check if all view files exist
ls -la resources/views/telegram-bot/
# Should show:
# - config.blade.php
# - explorer.blade.php  
# - activity.blade.php
# - allowed-users.blade.php
```

### 5. Service Classes Verification
```bash
# Check if service files exist
ls -la app/Services/Telegram/
# Should show:
# - TelegramService.php
# - FileProcessingService.php
# - CommandHandler.php
```

## 🧪 Testing Steps

### Step 1: Access Bot Configuration
1. Login as direktur role user
2. Navigate to: `/telegram-bot/config`
3. Expected: Bot configuration page should load

**If Error:** 
- Check browser console for JavaScript errors
- Check Laravel log: `tail -f storage/logs/laravel.log`
- Clear caches: `php artisan cache:clear && php artisan view:clear`

### Step 2: Test Database Connection
```bash
php artisan tinker
>>> \App\Models\BotConfiguration::first();
# Should return null or existing config

>>> \App\Models\BotConfiguration::create([
...     'bot_name' => 'Test Bot',
...     'bot_token' => 'test_token',
...     'server_host' => 'localhost',
...     'server_port' => 8081,
...     'bot_api_base_path' => '/tmp/telegram-bot-api',
...     'webhook_url' => 'https://example.com/webhook',
...     'use_local_server' => true,
...     'max_file_size_mb' => 2000,
...     'is_active' => false
... ]);
```

### Step 3: Test Controller Methods
```bash
# Test via artisan tinker
php artisan tinker
>>> $controller = new \App\Http\Controllers\TelegramBotController();
>>> $controller->config();
# Should return view instance
```

### Step 4: Test API Endpoints
```bash
# Test connection endpoint (after configuring bot)
curl -X POST http://localhost/telegram-bot/test-connection \
  -H "X-CSRF-TOKEN: $(grep csrf-token resources/views/layouts/app.blade.php | head -1 | cut -d'"' -f4)" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```

## 🔧 Common Issues & Fixes

### Issue 1: "View not found" Error
**Fix:**
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

### Issue 2: "Call to undefined method" Error
**Fix:**
```bash
composer dump-autoload
php artisan clear-compiled
```

### Issue 3: "Class not found" Error
**Fix:**
```bash
composer install
composer dump-autoload
```

### Issue 4: Permission Denied
**Fix:**
```bash
# Check storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
```

### Issue 5: Route Not Found
**Fix:**
```bash
php artisan route:clear
php artisan route:cache
```

## 📊 Debug Logging

Add these debug logs to identify issues:

### In TelegramBotController.php
```php
public function config()
{
    \Log::info('TelegramBotController@config called');
    
    try {
        $config = BotConfiguration::first();
        \Log::info('Config retrieved', ['config' => $config]);
        
        $webhookInfo = null;
        if ($config && $config->is_active) {
            try {
                $webhookInfo = $this->telegramService->getWebhookInfo();
                \Log::info('Webhook info retrieved', ['webhook' => $webhookInfo]);
            } catch (\Exception $e) {
                \Log::error('Failed to get webhook info: ' . $e->getMessage());
            }
        }
        
        return view('telegram-bot.config', compact('config', 'webhookInfo'));
    } catch (\Exception $e) {
        \Log::error('Error in config method', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
```

### In web.php routes
```php
Route::prefix('telegram-bot')->name('telegram-bot.')->middleware(['auth', 'role:direktur'])->group(function () {
    \Log::info('Telegram bot routes being registered');
    
    Route::get('/config', function() {
        \Log::info('Config route hit');
        return app(TelegramBotController::class)->config();
    })->name('config');
    // ... other routes
});
```

## 🚀 Quick Test Commands

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Regenerate autoload
composer dump-autoload

# 3. Check application health
php artisan about

# 4. Test database connection
php artisan db:show

# 5. Run migrations
php artisan migrate --force

# 6. Check route registration
php artisan route:list --name=telegram
```

## 📝 Verification Script

Create `test-telegram-bot.php` in project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Testing Telegram Bot Integration\n";
echo "=================================\n\n";

// Test 1: Check tables
echo "1. Checking database tables...\n";
$tables = \DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE 'bot_%'");
foreach ($tables as $table) {
    echo "   ✓ Table: {$table->table_name}\n";
}

// Test 2: Check models
echo "\n2. Checking models...\n";
$models = [
    'BotConfiguration',
    'BotActivity', 
    'BotUserSession',
    'BotCommandHistory',
    'BotUploadQueue'
];
foreach ($models as $model) {
    $class = "App\\Models\\{$model}";
    if (class_exists($class)) {
        echo "   ✓ Model: {$model}\n";
    } else {
        echo "   ✗ Model: {$model} NOT FOUND\n";
    }
}

// Test 3: Check services
echo "\n3. Checking services...\n";
$services = [
    'Telegram\\TelegramService',
    'Telegram\\FileProcessingService',
    'Telegram\\CommandHandler'
];
foreach ($services as $service) {
    $class = "App\\Services\\{$service}";
    if (class_exists($class)) {
        echo "   ✓ Service: {$service}\n";
    } else {
        echo "   ✗ Service: {$service} NOT FOUND\n";
    }
}

// Test 4: Check views
echo "\n4. Checking views...\n";
$views = [
    'telegram-bot.config',
    'telegram-bot.explorer',
    'telegram-bot.activity',
    'telegram-bot.allowed-users'
];
foreach ($views as $view) {
    if (view()->exists($view)) {
        echo "   ✓ View: {$view}\n";
    } else {
        echo "   ✗ View: {$view} NOT FOUND\n";
    }
}

// Test 5: Check routes
echo "\n5. Checking routes...\n";
$routes = [
    'telegram-bot.config',
    'telegram-bot.explorer',
    'telegram-bot.activity',
    'telegram-bot.allowed-users',
    'telegram.webhook'
];
foreach ($routes as $route) {
    if (Route::has($route)) {
        echo "   ✓ Route: {$route}\n";
    } else {
        echo "   ✗ Route: {$route} NOT FOUND\n";
    }
}

echo "\n=================================\n";
echo "Test completed!\n";
```

Run with: `php test-telegram-bot.php`

## 📞 Support Information

If issues persist after following this guide:

1. Check Laravel log: `tail -100 storage/logs/laravel.log`
2. Check PHP error log: `tail -100 /var/log/php/error.log`
3. Enable debug mode temporarily: Set `APP_DEBUG=true` in `.env`
4. Check browser console for JavaScript errors
5. Verify all files from implementation are present

## ✅ Success Indicators

When everything is working correctly:
- `/telegram-bot/config` page loads without errors
- Can save bot configuration
- Can test connection to Telegram API
- Can set/delete webhook
- Activity page shows bot interactions
- File explorer shows uploaded files
- Can manage allowed users

## 🔄 Next Steps After Fixing

1. Configure bot token from @BotFather
2. Set local server to `localhost:8081`
3. Configure two-path system paths
4. Test connection
5. Set webhook
6. Start using bot commands