<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(BotRole::class, 'role_id');
    }

    /**
     * Get the user who approved this user.
     */
    public function approver()
    {
        return $this->belongsTo(BotUser::class, 'approved_by');
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs()
    {
        return $this->hasMany(BotUserActivityLog::class, 'user_id');
    }

    /**
     * Get the registration requests for the user.
     */
    public function registrationRequests()
    {
        return $this->hasMany(BotRegistrationRequest::class, 'telegram_id', 'telegram_id');
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
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

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if the user is an admin (admin or super_admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    /**
     * Check if the user is a moderator or higher.
     */
    public function isModerator(): bool
    {
        return $this->hasRole('moderator') || $this->isAdmin();
    }

    /**
     * Approve the user.
     */
    public function approve($approverId): void
    {
        $this->status = 'active';
        $this->approved_at = now();
        $this->approved_by = $approverId;
        $this->save();
    }

    /**
     * Suspend the user.
     */
    public function suspend(?string $reason = null): void
    {
        $this->status = 'suspended';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['suspension_reason'] = $reason;
            $metadata['suspended_at'] = now()->toIso8601String();
            $this->metadata = $metadata;
        }
        $this->save();
    }

    /**
     * Ban the user.
     */
    public function ban(?string $reason = null): void
    {
        $this->status = 'banned';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['ban_reason'] = $reason;
            $metadata['banned_at'] = now()->toIso8601String();
            $this->metadata = $metadata;
        }
        $this->save();
    }

    /**
     * Activate the user.
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Update the user's last activity timestamp.
     */
    public function updateActivity(): void
    {
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Get the user's display name.
     */
    public function getDisplayName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        
        if ($this->username) {
            return '@' . $this->username;
        }
        
        return 'User #' . $this->telegram_id;
    }

    /**
     * Find a user by Telegram ID.
     */
    public static function findByTelegramId($telegramId): ?self
    {
        return static::where('telegram_id', $telegramId)->first();
    }

    /**
     * Create a user from Telegram data.
     */
    public static function createFromTelegram(array $telegramUser): self
    {
        return static::create([
            'telegram_id' => $telegramUser['id'],
            'username' => $telegramUser['username'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? null,
            'last_name' => $telegramUser['last_name'] ?? null,
            'role_id' => 4, // Default to user role
            'status' => 'pending',
            'registered_at' => now(),
        ]);
    }

    /**
     * Log an activity for this user.
     */
    public function logActivity(string $action, array $details = [], string $status = 'success'): void
    {
        BotUserActivityLog::create([
            'user_id' => $this->id,
            'telegram_id' => $this->telegram_id,
            'action' => $action,
            'details' => $details,
            'status' => $status,
        ]);
    }

    /**
     * Get users by role.
     */
    public static function byRole(string $roleName)
    {
        return static::whereHas('role', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        });
    }

    /**
     * Get active users.
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * Get pending users.
     */
    public static function pending()
    {
        return static::where('status', 'pending');
    }
}