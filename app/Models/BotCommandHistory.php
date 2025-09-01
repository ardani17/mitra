<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class BotCommandHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bot_command_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'telegram_user_id',
        'telegram_username',
        'chat_id',
        'command',
        'parameters',
        'project_id',
        'result_status',
        'result_message',
        'execution_time_ms',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parameters' => 'array',
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
        'execution_time_ms' => 'integer',
    ];

    /**
     * Get the project associated with this command
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope for successful commands
     */
    public function scopeSuccessful($query)
    {
        return $query->where('result_status', 'success');
    }

    /**
     * Scope for failed commands
     */
    public function scopeFailed($query)
    {
        return $query->where('result_status', 'failed');
    }

    /**
     * Scope for a specific command
     */
    public function scopeCommand($query, $command)
    {
        return $query->where('command', $command);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $telegramUserId)
    {
        return $query->where('telegram_user_id', $telegramUserId);
    }

    /**
     * Get command statistics for a user
     */
    public static function getUserStats($telegramUserId)
    {
        $stats = self::forUser($telegramUserId)
            ->selectRaw('command, COUNT(*) as count, AVG(execution_time_ms) as avg_time')
            ->groupBy('command')
            ->get();

        return $stats->mapWithKeys(function ($item) {
            return [$item->command => [
                'count' => $item->count,
                'avg_time' => round($item->avg_time, 2)
            ]];
        });
    }

    /**
     * Get most used commands
     */
    public static function getMostUsedCommands($limit = 10)
    {
        return self::selectRaw('command, COUNT(*) as usage_count')
            ->groupBy('command')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent commands for a user
     */
    public static function getRecentForUser($telegramUserId, $limit = 10)
    {
        return self::forUser($telegramUserId)
            ->with('project')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Log a command execution
     */
    public static function logCommand($user, $chatId, $command, $params = null, $projectId = null)
    {
        $startTime = microtime(true);
        
        $history = self::create([
            'telegram_user_id' => $user['id'],
            'telegram_username' => $user['username'] ?? null,
            'chat_id' => $chatId,
            'command' => $command,
            'parameters' => $params,
            'project_id' => $projectId,
            'result_status' => 'pending',
        ]);

        // Return a closure to complete the log
        return function($status, $message = null) use ($history, $startTime) {
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            
            $history->update([
                'result_status' => $status,
                'result_message' => $message,
                'execution_time_ms' => round($executionTime),
            ]);

            return $history;
        };
    }

    /**
     * Get command success rate
     */
    public static function getSuccessRate($command = null)
    {
        $query = self::query();
        
        if ($command) {
            $query->where('command', $command);
        }

        $total = $query->count();
        
        if ($total === 0) {
            return 0;
        }

        $successful = $query->where('result_status', 'success')->count();
        
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average execution time for a command
     */
    public static function getAverageExecutionTime($command)
    {
        $avg = self::where('command', $command)
            ->whereNotNull('execution_time_ms')
            ->avg('execution_time_ms');

        return $avg ? round($avg, 2) : 0;
    }

    /**
     * Clean old history (older than 30 days by default)
     */
    public static function cleanOldHistory($days = 30)
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }
}