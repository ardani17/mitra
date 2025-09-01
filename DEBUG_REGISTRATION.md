# 🔍 DEBUG & FIX REGISTRATION TIDAK MASUK DATABASE

## Masalah
- Command `/register` dan `/start` tidak menyimpan data ke database
- Registration Requests kosong di web interface

## Penyebab Kemungkinan
1. UserManagementCommandHandler tidak terpanggil
2. Webhook tidak mengirim data ke handler yang benar
3. Database connection issue

## SOLUSI LANGKAH DEMI LANGKAH

### 1. Check Webhook Route
```bash
# SSH ke server
ssh user@server
cd /www/wwwroot/mitra.cloudnexify.com/mitra

# Check webhook route
grep -r "telegram/webhook" routes/
```

### 2. Update Webhook Controller
Check file `app/Http/Controllers/TelegramWebhookController.php` atau yang handle webhook.

Pastikan ada kode seperti ini:
```php
public function handle(Request $request)
{
    $update = $request->all();
    
    if (isset($update['message'])) {
        $message = $update['message'];
        
        // Get command handler
        $commandHandler = app(\App\Services\Telegram\CommandHandler::class);
        
        // Handle command
        $commandHandler->handleCommand($message);
    }
    
    return response()->json(['ok' => true]);
}
```

### 3. Fix CommandHandler Integration

**File: `app/Services/Telegram/CommandHandler.php`**

Check line 36-38, pastikan ada:
```php
// Check if it's a user management command
if ($this->userManagementHandler->canHandle($command)) {
    return $this->userManagementHandler->handle($message);
}
```

### 4. Debug dengan Log

Tambahkan logging untuk debug:

**File: `app/Services/Telegram/CommandHandler.php`**
```php
public function handleCommand($message)
{
    $chatId = $message['chat']['id'];
    $user = $message['from'];
    $text = $message['text'] ?? '';
    
    // ADD THIS FOR DEBUG
    \Log::info('Bot Command Received', [
        'text' => $text,
        'user' => $user,
        'chat_id' => $chatId
    ]);
    
    // Parse command
    $parts = explode(' ', $text);
    $command = str_replace('/', '', array_shift($parts));
    $params = implode(' ', $parts);
    
    // ADD THIS FOR DEBUG
    \Log::info('Command Parsed', [
        'command' => $command,
        'params' => $params
    ]);
    
    // Check if it's a user management command
    if ($this->userManagementHandler->canHandle($command)) {
        \Log::info('Handling with UserManagementHandler');
        return $this->userManagementHandler->handle($message);
    }
    
    // ... rest of code
}
```

**File: `app/Services/Telegram/UserManagementCommandHandler.php`**
```php
public function handle($message)
{
    $chatId = $message['chat']['id'];
    $user = $message['from'];
    $text = $message['text'] ?? '';
    
    // ADD THIS FOR DEBUG
    \Log::info('UserManagementHandler called', [
        'text' => $text,
        'user' => $user
    ]);
    
    // ... rest of code
}

protected function handleRegister($chatId, $user)
{
    // ADD THIS FOR DEBUG
    \Log::info('handleRegister called', [
        'chat_id' => $chatId,
        'user' => $user
    ]);
    
    // Check if already registered
    $existingUser = BotUser::findByTelegramId($user['id']);
    
    if ($existingUser) {
        // ... existing code
    }
    
    // Start registration
    return $this->startRegistration($chatId, $user);
}

protected function startRegistration($chatId, $user)
{
    // ADD THIS FOR DEBUG
    \Log::info('startRegistration called', [
        'chat_id' => $chatId,
        'user' => $user
    ]);
    
    try {
        // Create registration request
        $request = BotRegistrationRequest::create([
            'telegram_id' => $user['id'],
            'username' => $user['username'] ?? null,
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'status' => 'pending',
            'reason' => 'Self registration via bot'
        ]);
        
        \Log::info('Registration created', ['request' => $request->toArray()]);
        
        // ... rest of code
    } catch (\Exception $e) {
        \Log::error('Registration failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
```

### 5. Test & Check Logs

Setelah menambahkan logging:
```bash
# Clear logs
> storage/logs/laravel.log

# Test bot
# Kirim /register di Telegram

# Check logs
tail -f storage/logs/laravel.log
```

### 6. Quick Fix - Manual Registration

Jika urgent, buat registration manual:
```bash
php artisan tinker
>>> \App\Models\BotRegistrationRequest::create([
>>>     'telegram_id' => '731289973',
>>>     'username' => 'your_username',
>>>     'first_name' => 'Your',
>>>     'last_name' => 'Name',
>>>     'status' => 'pending',
>>>     'reason' => 'Manual registration'
>>> ]);

>>> \App\Models\BotUser::create([
>>>     'telegram_id' => '731289973',
>>>     'username' => 'your_username',
>>>     'first_name' => 'Your',
>>>     'last_name' => 'Name',
>>>     'role_id' => 4,
>>>     'status' => 'pending'
>>> ]);
```

### 7. Alternative - Direct Database Insert

```sql
-- Insert registration request
INSERT INTO bot_registration_requests (telegram_id, username, first_name, last_name, status, created_at, updated_at)
VALUES ('731289973', 'username', 'First', 'Last', 'pending', NOW(), NOW());

-- Insert bot user
INSERT INTO bot_users (telegram_id, username, first_name, last_name, role_id, status, created_at, updated_at)
VALUES ('731289973', 'username', 'First', 'Last', 4, 'pending', NOW(), NOW());
```

### 8. Check Webhook Connection

```bash
# Test webhook manually
curl -X POST https://mitra.cloudnexify.com/telegram/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {"id": 123456},
      "from": {
        "id": 731289973,
        "username": "testuser",
        "first_name": "Test",
        "last_name": "User"
      },
      "text": "/register"
    }
  }'
```

### 9. Verify Handler Files Exist

```bash
# Check if files exist on server
ls -la app/Services/Telegram/UserManagementCommandHandler.php
ls -la app/Services/Telegram/CommandHandler.php

# Check if classes are loaded
php artisan tinker
>>> class_exists('\App\Services\Telegram\UserManagementCommandHandler');
>>> class_exists('\App\Models\BotRegistrationRequest');
>>> class_exists('\App\Models\BotUser');
```

### 10. Clear All Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan optimize
```

## JIKA SEMUA GAGAL - BYPASS REGISTRATION

Langsung buat user active:
```bash
php artisan tinker
>>> \App\Models\BotUser::updateOrCreate(
>>>     ['telegram_id' => '731289973'],
>>>     [
>>>         'username' => 'your_username',
>>>         'first_name' => 'Your',
>>>         'last_name' => 'Name',
>>>         'role_id' => 1, // Super Admin
>>>         'status' => 'active',
>>>         'approved_at' => now()
>>>     ]
>>> );
```

Setelah ini, bot commands seharusnya langsung berfungsi!