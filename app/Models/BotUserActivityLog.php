<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUserActivityLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'telegram_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'command',
        'command_params',
        'status',
        'error_message',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    /**
     * Get the user that owns the activity log.
     */
    public function user()
    {
        return $this->belongsTo(BotUser::class, 'user_id');
    }

    /**
     * Check if the activity was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if the activity failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the activity is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Log a user activity.
     */
    public static function log(
        $telegramId,
        string $action,
        array $details = [],
        string $status = 'success',
        ?string $errorMessage = null,
        ?int $userId = null
    ): self {
        // Try to find user if not provided
        if (!$userId && $telegramId) {
            $user = BotUser::findByTelegramId($telegramId);
            $userId = $user ? $user->id : null;
        }

        return static::create([
            'user_id' => $userId,
            'telegram_id' => $telegramId,
            'action' => $action,
            'details' => $details,
            'status' => $status,
            'error_message' => $errorMessage,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a command execution.
     */
    public static function logCommand(
        $telegramId,
        string $command,
        ?string $params = null,
        string $status = 'success',
        ?string $errorMessage = null,
        ?int $userId = null
    ): self {
        // Try to find user if not provided
        if (!$userId && $telegramId) {
            $user = BotUser::findByTelegramId($telegramId);
            $userId = $user ? $user->id : null;
        }

        return static::create([
            'user_id' => $userId,
            'telegram_id' => $telegramId,
            'action' => 'command_executed',
            'command' => $command,
            'command_params' => $params,
            'status' => $status,
            'error_message' => $errorMessage,
            'details' => [
                'command' => $command,
                'params' => $params,
            ],
        ]);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by Telegram ID.
     */
    public function scopeForTelegramId($query, $telegramId)
    {
        return $query->where('telegram_id', $telegramId);
    }

    /**
     * Scope for filtering by action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for today's logs.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get logs for a specific command.
     */
    public static function getCommandLogs(string $command, $limit = 100)
    {
        return static::where('command', $command)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed activities.
     */
    public static function getFailed($limit = 100)
    {
        return static::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity statistics for a user.
     */
    public static function getUserStatistics($userId): array
    {
        $query = static::where('user_id', $userId);
        
        return [
            'total_activities' => $query->count(),
            'successful' => (clone $query)->where('status', 'success')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'commands_executed' => (clone $query)->where('action', 'command_executed')->count(),
            'last_activity' => (clone $query)->latest('created_at')->first(),
            'most_used_command' => (clone $query)
                ->where('action', 'command_executed')
                ->whereNotNull('command')
                ->groupBy('command')
                ->selectRaw('command, COUNT(*) as count')
                ->orderBy('count', 'desc')
                ->first(),
        ];
    }

    /**
     * Clean old logs.
     */
    public static function cleanOld($days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Get formatted action name.
     */
    public function getFormattedAction(): string
    {
        $actions = [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'register' => 'Registration',
            'command_executed' => 'Command Executed',
            'file_uploaded' => 'File Uploaded',
            'file_downloaded' => 'File Downloaded',
            'user_approved' => 'User Approved',
            'user_rejected' => 'User Rejected',
            'user_banned' => 'User Banned',
            'user_unbanned' => 'User Unbanned',
            'user_suspended' => 'User Suspended',
            'role_changed' => 'Role Changed',
            'permission_granted' => 'Permission Granted',
            'permission_revoked' => 'Permission Revoked',
            'settings_changed' => 'Settings Changed',
        ];

        return $actions[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }
}