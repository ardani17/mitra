<?php


namespace App\Services\Telegram;

use App\Models\BotUser;
use App\Models\BotRole;
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
                
                BotUserActivityLog::logCommand($user['id'], 'register', null, 'success', 'Already registered');
                return 'Already registered';
            }
            
            if ($botUser->isPending()) {
                $this->telegramService->sendMessage($chatId, 
                    "⏳ Registrasi Anda sedang menunggu persetujuan admin.\n" .
                    "Anda akan diberitahu setelah disetujui."
                );
                
                BotUserActivityLog::logCommand($user['id'], 'register', null, 'success', 'Registration pending');
                return 'Registration pending';
            }
            
            if ($botUser->isBanned()) {
                $this->telegramService->sendMessage($chatId, 
                    "🚫 Akun Anda telah diblokir.\n" .
                    "Hubungi administrator untuk informasi lebih lanjut."
                );
                
                BotUserActivityLog::logCommand($user['id'], 'register', null, 'failed', 'User banned');
                return 'User banned';
            }
            
            if ($botUser->isSuspended()) {
                $this->telegramService->sendMessage($chatId, 
                    "⚠️ Akun Anda sedang disuspend.\n" .
                    "Hubungi administrator untuk informasi lebih lanjut."
                );
                
                BotUserActivityLog::logCommand($user['id'], 'register', null, 'failed', 'User suspended');
                return 'User suspended';
            }
        }
        
        // Start registration process
        return $this->startRegistration($chatId, $user);
    }
    
    /**
     * Start registration process
     */
    protected function startRegistration($chatId, $user)
    {
        // Check if there's already a pending request
        if (BotRegistrationRequest::hasPendingRequest($user['id'])) {
            $this->telegramService->sendMessage($chatId, 
                "⏳ Anda sudah memiliki permintaan registrasi yang sedang diproses.\n" .
                "Mohon tunggu persetujuan dari admin."
            );
            return 'Duplicate request';
        }
        
        // Create registration request
        $request = BotRegistrationRequest::createFromTelegram($user);
        
        // Create pending user
        $botUser = BotUser::createFromTelegram($user);
        
        // Send confirmation to user
        $message = "📝 <b>Registrasi Berhasil Dikirim!</b>\n\n";
        $message .= "Informasi Anda:\n";
        $message .= "👤 Nama: " . $this->telegramService->formatHtml($user['first_name'] ?? 'N/A') . " " . $this->telegramService->formatHtml($user['last_name'] ?? '') . "\n";
        $message .= "🆔 ID: <code>" . $user['id'] . "</code>\n";
        $message .= "📱 Username: @" . $this->telegramService->formatHtml($user['username'] ?? 'tidak ada') . "\n\n";
        $message .= "⏳ Permintaan Anda akan ditinjau oleh admin.\n";
        $message .= "📬 Anda akan menerima notifikasi setelah disetujui.\n\n";
        $message .= "💡 Tips: Anda dapat menggunakan /status untuk memeriksa status registrasi Anda.";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        // Notify admins
        $this->notifyAdminsNewRegistration($request);
        
        // Log activity
        BotUserActivityLog::log(
            $user['id'],
            'registration_submitted',
            ['request_id' => $request->id],
            'success',
            null,
            $botUser->id
        );
        
        return 'Registration submitted';
    }
    
    /**
     * Handle /status command - Check registration status
     */
    public function handleStatus($message)
    {
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        
        $botUser = BotUser::findByTelegramId($user['id']);
        
        if (!$botUser) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Anda belum terdaftar.\n" .
                "Gunakan /register untuk mendaftar."
            );
            return 'Not registered';
        }
        
        $statusEmoji = [
            'active' => '✅',
            'pending' => '⏳',
            'suspended' => '⚠️',
            'banned' => '🚫',
        ];
        
        $emoji = $statusEmoji[$botUser->status] ?? '❓';
        
        $message = "📊 <b>Status Akun Anda</b>\n\n";
        $message .= "👤 Nama: " . $this->telegramService->formatHtml($botUser->getDisplayName()) . "\n";
        $message .= "🆔 ID: <code>" . $botUser->telegram_id . "</code>\n";
        $message .= "📱 Username: @" . $this->telegramService->formatHtml($botUser->username ?? 'tidak ada') . "\n";
        $message .= "🎭 Role: " . $this->telegramService->formatHtml($botUser->role->display_name ?? 'User') . "\n";
        $message .= $emoji . " Status: <b>" . ucfirst($botUser->status) . "</b>\n";
        
        if ($botUser->isPending()) {
            $message .= "\n⏳ Registrasi Anda sedang ditinjau oleh admin.";
        } elseif ($botUser->isActive()) {
            $message .= "\n✅ Akun Anda aktif dan dapat menggunakan semua fitur bot.";
            if ($botUser->last_active_at) {
                $message .= "\n🕐 Aktivitas terakhir: " . $botUser->last_active_at->diffForHumans();
            }
        } elseif ($botUser->isBanned()) {
            $message .= "\n🚫 Akun Anda telah diblokir.";
            if (isset($botUser->metadata['ban_reason'])) {
                $message .= "\nAlasan: " . $this->telegramService->formatHtml($botUser->metadata['ban_reason']);
            }
        } elseif ($botUser->isSuspended()) {
            $message .= "\n⚠️ Akun Anda sedang disuspend.";
            if (isset($botUser->metadata['suspension_reason'])) {
                $message .= "\nAlasan: " . $this->telegramService->formatHtml($botUser->metadata['suspension_reason']);
            }
        }
        
        $this->telegramService->sendMessage($chatId, $message);
        
        BotUserActivityLog::logCommand($user['id'], 'status', null, 'success');
        
        return 'Status displayed';
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
            
            BotUserActivityLog::logCommand($adminTelegramId, 'approve', $params, 'failed', 'Unauthorized');
            return 'Unauthorized';
        }
        
        // Parse user ID from params
        $userId = trim($params);
        if (!$userId) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Format: /approve [user_id atau telegram_id]\n" .
                "Contoh: /approve 123456789"
            );
            return 'Invalid format';
        }
        
        // Find user to approve
        $user = BotUser::find($userId) ?? BotUser::findByTelegramId($userId);
        
        if (!$user) {
            $this->telegramService->sendMessage($chatId, 
                "❌ User tidak ditemukan."
            );
            return 'User not found';
        }
        
        if ($user->isActive()) {
            $this->telegramService->sendMessage($chatId, 
                "ℹ️ User sudah aktif."
            );
            return 'Already active';
        }
        
        // Approve user
        $user->approve($admin->id);
        
        // Update registration request
        $request = BotRegistrationRequest::where('telegram_id', $user->telegram_id)
            ->where('status', 'pending')
            ->first();
            
        if ($request) {
            $request->approve($admin->id, 'Approved by ' . $admin->getDisplayName());
        }
        
        // Send confirmation to admin
        $this->telegramService->sendMessage($chatId, 
            "✅ User berhasil disetujui!\n" .
            "👤 " . $this->telegramService->formatHtml($user->getDisplayName()) . "\n" .
            "🆔 ID: " . $user->telegram_id
        );
        
        // Notify the user
        try {
            $this->telegramService->sendMessage($user->telegram_id, 
                "🎉 <b>Selamat!</b>\n\n" .
                "Registrasi Anda telah disetujui!\n" .
                "Sekarang Anda dapat menggunakan semua fitur bot.\n\n" .
                "Gunakan /help untuk melihat perintah yang tersedia."
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify approved user: ' . $e->getMessage());
        }
        
        // Log activity
        BotUserActivityLog::log(
            $admin->telegram_id,
            'user_approved',
            ['approved_user_id' => $user->id, 'telegram_id' => $user->telegram_id],
            'success',
            null,
            $admin->id
        );
        
        return 'User approved';
    }
    
    /**
     * Handle /reject command (Admin only)
     */
    public function handleReject($message, $params)
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isAdmin()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            
            BotUserActivityLog::logCommand($adminTelegramId, 'reject', $params, 'failed', 'Unauthorized');
            return 'Unauthorized';
        }
        
        // Parse user ID and reason
        $parts = explode(' ', $params, 2);
        $userId = trim($parts[0] ?? '');
        $reason = trim($parts[1] ?? 'Tidak memenuhi syarat');
        
        if (!$userId) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Format: /reject [user_id atau telegram_id] [alasan]\n" .
                "Contoh: /reject 123456789 Tidak memenuhi syarat"
            );
            return 'Invalid format';
        }
        
        // Find user to reject
        $user = BotUser::find($userId) ?? BotUser::findByTelegramId($userId);
        
        if (!$user) {
            // Try to find in registration requests
            $request = BotRegistrationRequest::where('telegram_id', $userId)
                ->where('status', 'pending')
                ->first();
                
            if ($request) {
                $request->reject($admin->id, $reason);
                
                // Notify the user
                try {
                    $this->telegramService->sendMessage($request->telegram_id, 
                        "❌ <b>Registrasi Ditolak</b>\n\n" .
                        "Maaf, registrasi Anda telah ditolak.\n" .
                        "Alasan: " . $this->telegramService->formatHtml($reason) . "\n\n" .
                        "Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator."
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to notify rejected user: ' . $e->getMessage());
                }
                
                $this->telegramService->sendMessage($chatId, 
                    "✅ Registrasi berhasil ditolak.\n" .
                    "👤 " . $this->telegramService->formatHtml($request->getDisplayName())
                );
                
                return 'Registration rejected';
            }
            
            $this->telegramService->sendMessage($chatId, 
                "❌ User tidak ditemukan."
            );
            return 'User not found';
        }
        
        // Update user status
        $user->status = 'rejected';
        $metadata = $user->metadata ?? [];
        $metadata['rejection_reason'] = $reason;
        $metadata['rejected_at'] = now()->toIso8601String();
        $metadata['rejected_by'] = $admin->id;
        $user->metadata = $metadata;
        $user->save();
        
        // Update registration request
        $request = BotRegistrationRequest::where('telegram_id', $user->telegram_id)
            ->where('status', 'pending')
            ->first();
            
        if ($request) {
            $request->reject($admin->id, $reason);
        }
        
        // Send confirmation to admin
        $this->telegramService->sendMessage($chatId, 
            "✅ User berhasil ditolak!\n" .
            "👤 " . $this->telegramService->formatHtml($user->getDisplayName()) . "\n" .
            "🆔 ID: " . $user->telegram_id . "\n" .
            "📝 Alasan: " . $this->telegramService->formatHtml($reason)
        );
        
        // Notify the user
        try {
            $this->telegramService->sendMessage($user->telegram_id, 
                "❌ <b>Registrasi Ditolak</b>\n\n" .
                "Maaf, registrasi Anda telah ditolak.\n" .
                "Alasan: " . $this->telegramService->formatHtml($reason) . "\n\n" .
                "Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator."
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify rejected user: ' . $e->getMessage());
        }
        
        // Log activity
        BotUserActivityLog::log(
            $admin->telegram_id,
            'user_rejected',
            ['rejected_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'reason' => $reason],
            'success',
            null,
            $admin->id
        );
        
        return 'User rejected';
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
            
            BotUserActivityLog::logCommand($adminTelegramId, 'pending', null, 'failed', 'Unauthorized');
            return 'Unauthorized';
        }
        
        // Get pending registrations
        $pending = BotRegistrationRequest::pending()
            ->orderBy('requested_at', 'desc')
            ->limit(10)
            ->get();
        
        if ($pending->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 
                "✅ Tidak ada registrasi yang menunggu persetujuan."
            );
            return 'No pending registrations';
        }
        
        $message = "📋 <b>Registrasi Menunggu Persetujuan:</b>\n\n";
        
        foreach ($pending as $request) {
            $message .= "👤 <b>" . $this->telegramService->formatHtml($request->getDisplayName()) . "</b>\n";
            $message .= "🆔 ID: <code>" . $request->telegram_id . "</code>\n";
            $message .= "📱 @" . $this->telegramService->formatHtml($request->username ?? 'no_username') . "\n";
            $message .= "📅 " . $request->requested_at->diffForHumans() . "\n";
            $message .= "✅ /approve " . $request->telegram_id . "\n";
            $message .= "❌ /reject " . $request->telegram_id . " [alasan]\n";
            $message .= "➖➖➖➖➖➖➖➖➖\n\n";
        }
        
        $message .= "Total: " . $pending->count() . " permintaan";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        BotUserActivityLog::logCommand($adminTelegramId, 'pending', null, 'success');
        
        return 'Pending list displayed';
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
            
            BotUserActivityLog::logCommand($adminTelegramId, 'users', $params, 'failed', 'Unauthorized');
            return 'Unauthorized';
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
                case 'suspended':
                    $query->where('status', 'suspended');
                    break;
                case 'admin':
                    $query->whereHas('role', function($q) {
                        $q->whereIn('name', ['admin', 'super_admin']);
                    });
                    break;
                case 'moderator':
                    $query->whereHas('role', function($q) {
                        $q->where('name', 'moderator');
                    });
                    break;
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')->limit(20)->get();
        
        if ($users->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 
                "📭 Tidak ada user ditemukan."
            );
            return 'No users found';
        }
        
        $message = "👥 <b>Daftar User</b>";
        if ($filter) {
            $message .= " (Filter: " . $filter . ")";
        }
        $message .= "\n\n";
        
        $statusEmoji = [
            'active' => '✅',
            'pending' => '⏳',
            'suspended' => '⚠️',
            'banned' => '🚫',
        ];
        
        foreach ($users as $user) {
            $emoji = $statusEmoji[$user->status] ?? '❓';
            $message .= $emoji . " <b>" . $this->telegramService->formatHtml($user->getDisplayName()) . "</b>\n";
            $message .= "   🆔 <code>" . $user->telegram_id . "</code>\n";
            $message .= "   📱 @" . $this->telegramService->formatHtml($user->username ?? 'no_username') . "\n";
            $message .= "   🎭 " . $this->telegramService->formatHtml($user->role->display_name ?? 'User') . "\n";
            $message .= "   📅 " . $user->created_at->format('d/m/Y') . "\n\n";
        }
        
        $message .= "Filter: /users [active|pending|banned|suspended|admin|moderator]";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        BotUserActivityLog::logCommand($adminTelegramId, 'users', $params, 'success');
        
        return 'Users list displayed';
    }
    
    /**
     * Handle /ban command (Admin only)
     */
    public function handleBan($message, $params)
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isAdmin()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            
            BotUserActivityLog::logCommand($adminTelegramId, 'ban', $params, 'failed', 'Unauthorized');
            return 'Unauthorized';
        }
        
        // Parse user ID and reason
        $parts = explode(' ', $params, 2);
        $userId = trim($parts[0] ?? '');
        $reason = trim($parts[1] ?? 'Melanggar aturan');
        
        if (!$userId) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Format: /ban [user_id atau telegram_id] [alasan]\n" .
                "Contoh: /ban 123456789 Spam"
            );
            return 'Invalid format';
        }
        
        // Find user to ban
        $user = BotUser::find($userId) ?? BotUser::findByTelegramId($userId);
        
        if (!$user) {
            $this->telegramService->sendMessage($chatId, 
                "❌ User tidak ditemukan."
            );
            return 'User not found';
        }
        
        if ($user->isBanned()) {
            $this->telegramService->sendMessage($chatId, 
                "ℹ️ User sudah diblokir."
            );
            return 'Already banned';
        }
        
        // Prevent banning admins
        if ($user->isAdmin()) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Tidak dapat memblokir administrator."
            );
            return 'Cannot ban admin';
        }
        
        // Ban user
        $user->ban($reason);
        
        // Send confirmation to admin
        $this->telegramService->sendMessage($chatId, 
            "✅ User berhasil diblokir!\n" .
            "👤 " . $this->telegramService->formatHtml($user->getDisplayName()) . "\n" .
            "🆔 ID: " . $user->telegram_id . "\n" .
            "📝 Alasan: " . $this->telegramService->formatHtml($reason)
        );
        
        // Notify the user
        try {
            $this->telegramService->sendMessage($user->telegram_id, 
                "🚫 <b>Akun Diblokir</b>\n\n" .
                "Akun Anda telah diblokir dari menggunakan bot ini.\n" .
                "Alasan: " . $this->telegramService->formatHtml($reason) . "\n\n" .
                "Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator."
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify banned user: ' . $e->getMessage());
        }
        
        // Log activity
        BotUserActivityLog::log(
            $admin->telegram_id,
            'user_banned',
            ['banned_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'reason' => $reason],
            'success',
            null,
            $admin->id
        );
        
        return 'User banned';
    }
    
    /**
     * Handle /unban command (Admin only)
     */
    public function handleUnban($message, $params)
    {
        $chatId = $message['chat']['id'];
        $adminTelegramId = $message['from']['id'];
        
        // Check if user is admin
        $admin = BotUser::findByTelegramId($adminTelegramId);
        if (!$admin || !$admin->isAdmin()) {
            $this->telegramService->sendMessage($chatId, 
                "🚫 Anda tidak memiliki izin untuk menggunakan perintah ini."
            );
            
            BotUserActivityLog::logCommand($adminTelegramId, 'unban', $params, 'failed', 'Unauthorized');
            return 'Unauthorized';
        }
        
        // Parse user ID
        $userId = trim($params);
        
        if (!$userId) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Format: /unban [user_id atau telegram_id]\n" .
                "Contoh: /unban 123456789"
            );
            return 'Invalid format';
        }
        
        // Find user to unban
        $user = BotUser::find($userId) ?? BotUser::findByTelegramId($userId);
        
        if (!$user) {
            $this->telegramService->sendMessage($chatId, 
                "❌ User tidak ditemukan."
            );
            return 'User not found';
        }
        
        if (!$user->isBanned()) {
            $this->telegramService->sendMessage($chatId, 
                "ℹ️ User tidak dalam status diblokir."
            );
            return 'Not banned';
        }
        
        // Unban user
        $user->activate();
        
        // Send confirmation to admin
        $this->telegramService->sendMessage($chatId, 
            "✅ User berhasil di-unban!\n" .
            "👤 " . $this->telegramService->formatHtml($user->getDisplayName()) . "\n" .
            "🆔 ID: " . $user->telegram_id
        );
        
        // Notify the user
        try {
            $this->telegramService->sendMessage($user->telegram_id, 
                "✅ <b>Akun Diaktifkan Kembali</b>\n\n" .
                "Akun Anda telah diaktifkan kembali.\n" .
                "Sekarang Anda dapat menggunakan bot ini lagi.\n\n" .
                "Gunakan /help untuk melihat perintah yang tersedia."
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify unbanned user: ' . $e->getMessage());
        }
        
        // Log activity
        BotUserActivityLog::log(
            $admin->telegram_id,
            'user_unbanned',
            ['unbanned_user_id' => $user->id, 'telegram_id' => $user->telegram_id],
            'success',
            null,
            $admin->id
        );
        
        return 'User unbanned';
    }
    
    /**
     * Notify admins about new registration
     */
    protected function notifyAdminsNewRegistration($request)
    {
        // Get all admin and moderator users
        $admins = BotUser::active()
            ->whereHas('role', function($query) {
                $query->whereIn('name', ['admin', 'super_admin', 'moderator']);
            })
            ->get();
        
        $message = "🔔 <b>Registrasi Baru!</b>\n\n";
        $message .= "👤 Nama: " . $this->telegramService->formatHtml($request->getDisplayName()) . "\n";
        $message .= "🆔 ID: <code>" . $request->telegram_id . "</code>\n";
        $message .= "📱 Username: @" . $this->telegramService->formatHtml($request->username ?? 'tidak ada') . "\n";
        $message .= "📅 Waktu: " . $request->requested_at->format('d/m/Y H:i') . "\n\n";
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

