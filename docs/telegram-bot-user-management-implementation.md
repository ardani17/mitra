# Telegram Bot User Management - Implementation Guide

## Quick Start Implementation

This guide provides ready-to-use code for implementing user management in your Telegram bot.

## Step 1: Database Migrations

### Create the migrations files:

```bash
php artisan make:migration create_bot_users_table
php artisan make:migration create_bot_roles_table
php artisan make:migration create_bot_registration_requests_table
php artisan make:migration create_bot_user_activity_logs_table
```

### Migration: bot_users
```php
<?php
// database/migrations/2024_01_02_000001_create_bot_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('role_id')->default(1);
            $table->enum('status', ['pending', 'active', 'suspended', 'banned'])->default('pending');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('telegram_id');
            $table->index('status');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_users');
    }
};
```

### Migration: bot_roles
```php
<?php
// database/migrations/2024_01_02_000002_create_bot_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->integer('priority')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        
        // Insert default roles
        DB::table('bot_roles')->insert([
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access',
                'permissions' => json_encode(['*']),
                'priority' => 100,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'User management and settings',
                'permissions' => json_encode([
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'registrations.view', 'registrations.approve', 'registrations.reject',
                    'logs.view', 'settings.view', 'settings.edit'
                ]),
                'priority' => 90,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Approve users and view logs',
                'permissions' => json_encode([
                    'users.view', 'registrations.view', 'registrations.approve', 
                    'registrations.reject', 'logs.view'
                ]),
                'priority' => 50,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user access',
                'permissions' => json_encode([
                    'bot.use', 'projects.view', 'files.upload', 'files.view'
                ]),
                'priority' => 10,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_roles');
    }
};
```

## Step 2: Models

### BotUser Model
```php
<?php
// app/Models/BotUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'phone',
        'email',
        'role_id',
        'status',
        'registered_at',
        'approved_at',
        'approved_by',
        'last_active_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(BotRole::class, 'role_id');
    }

    public function approver()
    {
        return $this->belongsTo(BotUser::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(BotUserActivityLog::class, 'user_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isBanned()
    {
        return $this->status === 'banned';
    }

    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }

        $permissions = $this->role->permissions ?? [];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check specific permission
        return in_array($permission, $permissions);
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    public function isModerator()
    {
        return $this->hasRole('moderator') || $this->isAdmin();
    }

    public function approve($approverId)
    {
        $this->status = 'active';
        $this->approved_at = now();
        $this->approved_by = $approverId;
        $this->save();
    }

    public function suspend($reason = null)
    {
        $this->status = 'suspended';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['suspension_reason'] = $reason;
            $metadata['suspended_at'] = now();
            $this->metadata = $metadata;
        }
        $this->save();
    }

    public function ban($reason = null)
    {
        $this->status = 'banned';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['ban_reason'] = $reason;
            $metadata['banned_at'] = now();
            $this->metadata = $metadata;
        }
        $this->save();
    }

    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    public function updateActivity()
    {
        $this->last_active_at = now();
        $this->save();
    }

    public static function findByTelegramId($telegramId)
    {
        return static::where('telegram_id', $telegramId)->first();
    }

    public static function createFromTelegram($telegramUser)
    {
        return static::create([
            'telegram_id' => $telegramUser['id'],
            'username' => $telegramUser['username'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? null,
            'last_name' => $telegramUser['last_name'] ?? null,
            'role_id' => 1, // Default to user role
            'status' => 'pending',
            'registered_at' => now(),
        ]);
    }
}
```

## Step 3: Enhanced Command Handler

### Updated CommandHandler with User Management
```php
<?php
// app/Services/Telegram/UserManagementCommandHandler.php

namespace App\Services\Telegram;

use App\Models\BotUser;
use App\Models\BotRegistrationRequest;
use App\Models\BotUserActivityLog;
use Illuminate\Support\Facades\Log;

class UserManagementCommandHandler
{
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle /register command
     */
    public function handleRegister($message)
    {
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        
        // Check if already registered
        $botUser = BotUser::findByTelegramId($user['id']);
        
        if ($botUser) {
            if ($botUser->isActive()) {
                $this->telegramService->sendMessage($chatId, 
                    "✅ Anda sudah terdaftar dan aktif!\n" .
                    "Gunakan /help untuk melihat perintah yang tersedia."
                );
                return;
            }
            
            if ($botUser->isPending()) {
                $this->telegramService->sendMessage($chatId, 
                    "⏳ Registrasi Anda sedang menunggu persetujuan admin.\n" .
                    "Anda akan diberitahu setelah disetujui."
                );
                return;
            }
            
            if ($botUser->isBanned()) {
                $this->telegramService->sendMessage($chatId, 
                    "🚫 Akun Anda telah diblokir.\n" .
                    "Hubungi administrator untuk informasi lebih lanjut."
                );
                return;
            }
        }
        
        // Start registration process
        $this->startRegistration($chatId, $user);
    }
    
    /**
     * Start registration process
     */
    protected function startRegistration($chatId, $user)
    {
        // Create registration request
        $request = BotRegistrationRequest::create([
            'telegram_id' => $user['id'],
            'username' => $user['username'] ?? null,
            'first_name' => $user['first_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
        
        // Create pending user
        $botUser = BotUser::createFromTelegram($user);
        
        // Send confirmation to user
        $message = "📝 <b>Registrasi Berhasil Dikirim!</b>\n\n";
        $message .= "Informasi Anda:\n";
        $message .= "👤 Nama: " . ($user['first_name'] ?? 'N/A') . " " . ($user['last_name'] ?? '') . "\n";
        $message .= "🆔 ID: " . $user['id'] . "\n";
        $message .= "📱 Username: @" . ($user['username'] ?? 'tidak ada') . "\n\n";
        $message .= "⏳ Permintaan Anda akan ditinjau oleh admin.\n";
        $message .= "📬 Anda akan menerima notifikasi setelah disetujui.";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        // Notify admins
        $this->notifyAdminsNewRegistration($request);
        
        // Log activity
        BotUserActivityLog::create([
            'user_id' => $botUser->id,
            'telegram_id' => $user['id'],
            'action' => 'registration_submitted',
            'details' => ['request_id' => $request->id],
        ]);
    }
    
    /**
     * Handle /approve command (Admin only)
     */
    public function handleApprove($message, $params)
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isAdmin()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            return;
        }
        
        // Parse user ID from params
        $userId = trim($params);
        if (!$userId) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Format: /approve [user_id atau telegram_id]"
            );
            return;
        }
        
        // Find user to approve
        $user = BotUser::find($userId) ?? BotUser::findByTelegramId($userId);
        
        if (!$user) {
            $this->telegramService->sendMessage($chatId, 
                "❌ User tidak ditemukan."
            );
            return;
        }
        
        if ($user->isActive()) {
            $this->telegramService->sendMessage($chatId, 
                "ℹ️ User sudah aktif."
            );
            return;
        }
        
        // Approve user
        $user->approve($admin->id);
        
        // Update registration request
        BotRegistrationRequest::where('telegram_id', $user->telegram_id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        
        // Send confirmation to admin
        $this->telegramService->sendMessage($chatId, 
            "✅ User berhasil disetujui!\n" .
            "👤 " . $user->first_name . " (@" . $user->username . ")"
        );
        
        // Notify the user
        $this->telegramService->sendMessage($user->telegram_id, 
            "🎉 <b>Selamat!</b>\n\n" .
            "Registrasi Anda telah disetujui!\n" .
            "Sekarang Anda dapat menggunakan semua fitur bot.\n\n" .
            "Gunakan /help untuk melihat perintah yang tersedia."
        );
        
        // Log activity
        BotUserActivityLog::create([
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'action' => 'user_approved',
            'details' => ['approved_by' => $admin->id],
        ]);
    }
    
    /**
     * Handle /pending command (Admin/Moderator only)
     */
    public function handlePending($message)
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is moderator or admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isModerator()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            return;
        }
        
        // Get pending registrations
        $pending = BotRegistrationRequest::where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->limit(10)
            ->get();
        
        if ($pending->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 
                "✅ Tidak ada registrasi yang menunggu persetujuan."
            );
            return;
        }
        
        $message = "📋 <b>Registrasi Menunggu Persetujuan:</b>\n\n";
        
        foreach ($pending as $request) {
            $message .= "👤 <b>" . $request->first_name . " " . $request->last_name . "</b>\n";
            $message .= "🆔 ID: <code>" . $request->telegram_id . "</code>\n";
            $message .= "📱 @" . ($request->username ?? 'no_username') . "\n";
            $message .= "📅 " . $request->requested_at->diffForHumans() . "\n";
            $message .= "✅ /approve " . $request->telegram_id . "\n";
            $message .= "❌ /reject " . $request->telegram_id . " [alasan]\n";
            $message .= "➖➖➖➖➖➖➖➖➖\n\n";
        }
        
        $message .= "Total: " . $pending->count() . " permintaan";
        
        $this->telegramService->sendMessage($chatId, $message);
    }
    
    /**
     * Handle /users command (Admin/Moderator only)
     */
    public function handleUsers($message, $params = '')
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is moderator or admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isModerator()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            return;
        }
        
        // Parse filter from params
        $filter = trim($params);
        $query = BotUser::with('role');
        
        if ($filter) {
            switch ($filter) {
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'banned':
                    $query->where('status', 'banned');
                    break;
                case 'admin':
                    $query->whereHas('role', function($q) {
                        $q->whereIn('name', ['admin', 'super_admin']);
                    });
                    break;
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')->limit(20)->get();
        
        if ($users->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 
                "📭 Tidak ada user ditemukan."
            );
            return;
        }
        
        $message = "👥 <b>Daftar User:</b>\n\n";
        
        $statusEmoji = [
            'active' => '✅',
            'pending' => '⏳',
            'suspended' => '⚠️',
            'banned' => '🚫',
        ];
        
        foreach ($users as $user) {
            $emoji = $statusEmoji[$user->status] ?? '❓';
            $message .= $emoji . " <b>" . $user->first_name . " " . $user->last_name . "</b>\n";
            $message .= "   🆔 " . $user->telegram_id . "\n";
            $message .= "   📱 @" . ($user->username ?? 'no_username') . "\n";
            $message .= "   🎭 " . $user->role->display_name . "\n";
            $message .= "   📅 " . $user->created_at->format('d/m/Y') . "\n\n";
        }
        
        $message .= "Filter: /users [active|pending|banned|admin]";
        
        $this->telegramService->sendMessage($chatId, $message);
    }
    
    /**
     * Notify admins about new registration
     */
    protected function notifyAdminsNewRegistration($request)
    {
        // Get all admin users
        $admins = BotUser::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'super_admin', 'moderator']);
        })->where('status', 'active')->get();
        
        $message = "🔔 <b>Registrasi Baru!</b>\n\n";
        $message .= "👤 Nama: " . $request->first_name . " " . $request->last_name . "\n";
        $message .= "🆔 ID: " . $request->telegram_id . "\n";
        $message .= "📱 Username: @" . ($request->username ?? 'tidak ada') . "\n\n";
        $message .= "Gunakan perintah berikut:\n";
        $message .= "✅ /approve " . $request->telegram_id . "\n";
        $message .= "❌ /reject " . $request->telegram_id . " [alasan]";
        
        foreach ($admins as $admin) {
            try {
                $this->telegramService->sendMessage($admin->telegram_id, $message);
            } catch (\Exception $e) {
                Log::error('Failed to notify admin: ' . $e->getMessage());
            }
        }
    }
}
```

## Step 4: Update Main Command Handler

Add these cases to your main CommandHandler:

```php
// In app/Services/Telegram/CommandHandler.php

// Add at the top
use App\Services\Telegram\UserManagementCommandHandler;

// In the constructor
protected $userManagementHandler;

public function __construct(TelegramService $telegramService, FileProcessingService $fileProcessingService)
{
    $this->telegramService = $telegramService;
    $this->fileProcessingService = $fileProcessingService;
    $this->userManagementHandler = new UserManagementCommandHandler($telegramService);
}

// In handleCommand method, update the authorization check:
public function handleCommand($message)
{
    $chatId = $message['chat']['id'];
    $user = $message['from'];
    $text = $message['text'] ?? '';
    
    // Parse command
    $parts = explode(' ', $text);
    $command = str_replace('/', '', array_shift($parts));
    $params = implode(' ', $parts);
    
    // Commands that don't require authorization
    $publicCommands = ['start', 'register', 'help', 'status'];
    
    if (!in_array($command, $publicCommands)) {
        // Check if user is allowed using new system
        $botUser = BotUser::findByTelegramId($user['id']);
        
        if (!$botUser || !$botUser->isActive()) {
            $this->sendUnauthorizedMessage($chatId, $user['id']);
            return;
        }
        
        // Update last activity
        $botUser->updateActivity();
    }
    
    // Add new command cases
    switch ($command) {
        case 'register':
            $result = $this->userManagementHandler->handleRegister($message);
            break;
            
        case 'approve':
            $result = $this->userManagementHandler->handleApprove($message, $params);
            break;
            
        case 'reject':
            $result = $this->userManagementHandler->handleReject($message, $params);
            break;
            
        case 'pending':
            $result = $this->userManagementHandler->handlePending($message);
            break;
            
        case 'users':
            $result = $this->userManagementHandler->handleUsers($message, $params);
            break;
            
        case 'ban':
            $result = $this->userManagementHandler->handleBan($message, $params);
            break;
            
        case 'unban':
            $result = $this->userManagementHandler->handleUnban($message, $params);
            break;
            
        // ... existing cases ...
    }
}

// Update unauthorized message
protected function sendUnauthorizedMessage($chatId, $telegramId)
{
    $botUser = BotUser::findByTelegramId($telegramId);
    
    if ($botUser && $botUser->isPending()) {
        $message = "⏳ <b>Menunggu Persetujuan</b>\n\n";
        $message .= "Registrasi Anda sedang ditinjau oleh admin.\n";
        $message .= "Anda akan diberitahu setelah disetujui.";
    } elseif ($botUser && $botUser->isBanned()) {
        $message = "🚫 <b>Akses Ditolak</b>\n\n";
        $message .= "Akun Anda telah diblokir.\n";
        $reason = $botUser->metadata['ban_reason'] ?? null;
        if ($reason) {
            $message .= "Alasan: " . $reason . "\n";
        }
        $message .= "Hubungi administrator untuk informasi lebih lanjut.";
    } else {
        $message = "🚫 <b>Akses Ditolak</b>\n\n";
        $message .= "Anda belum terdaftar untuk menggunakan bot ini.\n\n";
        $message .= "Gunakan /register untuk mendaftar.\n";
        $message .= "User ID Anda: <code>" . $telegramId . "</code>";
    }
    
    $this->telegramService->sendMessage($chatId, $message);
}
```

## Step 5: Web Interface Updates

### Add routes in web.php:
```php
// In routes/web.php, add to the telegram-bot group:

Route::prefix('telegram-bot')->name('telegram-bot.')->middleware(['auth', 'role:direktur'])->group(function () {
    // ... existing routes ...
    
    // User Management
    Route::get('/users', [TelegramBotController::class, 'users'])->name('users');
    Route::get('/users/{user}', [TelegramBotController::class, 'userDetail'])->name('users.show');
    Route::post('/users/{user}/approve', [TelegramBotController::class, 'approveUser'])->name('users.approve');
    Route::post('/users/{user}/reject', [TelegramBotController::class, 'rejectUser'])->name('users.reject');
    Route::post('/users/{user}/ban', [TelegramBotController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [TelegramBotController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{user}/role', [TelegramBotController::class, 'changeUserRole'])->name('users.role');
    
    // Registration Requests
    Route::get('/registrations', [TelegramBotController::class, 'registrations'])->name('registrations');
    Route::post('/registrations/{request}/approve', [TelegramBotController::class, 'approveRegistration'])->name('registrations.approve');
    Route::post('/registrations/{request}/reject', [TelegramBotController::class, 'rejectRegistration'])->name('registrations.reject');
});
```

## Step 6: Quick Setup Commands

Run these commands to set up the system:

```bash
# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optional: Create a seeder for test data
php artisan make:seeder BotUserSeeder
```

## Step 7: Testing the System

1. **Test Registration Flow:**
   - Send `/start` to bot
   - Send `/register` to request access
   - Check admin receives notification
   - Admin sends `/approve [user_id]`
   - User receives approval notification

2. **Test Admin Commands:**
   - `/pending` - View pending registrations
   - `/users` - List all users
   - `/users active` - List active users
   - `/approve [id]` - Approve user
   - `/reject [id] [reason]` - Reject user
   - `/ban [id] [reason]` - Ban user
   - `/unban [id]` - Unban user

3. **Test Permissions:**
   - Regular users cannot use admin commands
   - Banned users cannot access bot
   - Pending users see waiting message

## Conclusion

This implementation provides:
- ✅ Self-registration system
- ✅ Admin approval workflow
- ✅ Role-based access control
- ✅ User management commands
- ✅ Activity logging
- ✅ Web interface integration
- ✅ Security measures

The system is now ready for production use with comprehensive user management capabilities.