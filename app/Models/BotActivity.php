<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class BotActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'telegram_user_id',
        'telegram_username',
        'chat_id',
        'message_type',
        'message_text',
        'command',
        'command_params',
        'file_name',
        'file_size',
        'file_path',
        'telegram_file_id',
        'telegram_original_path',
        'project_id',
        'status',
        'error_message',
        'response_data',
        'processing_time_ms',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'command_params' => 'array',
        'response_data' => 'array',
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
        'file_size' => 'integer',
        'processing_time_ms' => 'integer',
    ];

    /**
     * Get the project associated with this activity
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope for successful activities
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed activities
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending activities
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for file uploads
     */
    public function scopeFileUploads($query)
    {
        return $query->whereIn('message_type', ['file', 'photo', 'video', 'document']);
    }

    /**
     * Scope for commands
     */
    public function scopeCommands($query)
    {
        return $query->where('message_type', 'command');
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $telegramUserId)
    {
        return $query->where('telegram_user_id', $telegramUserId);
    }

    /**
     * Scope for today's activities
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week's activities
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's activities
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get activity icon based on type
     */
    public function getIconAttribute()
    {
        switch ($this->message_type) {
            case 'command':
                return 'fas fa-terminal';
            case 'document':
            case 'file':
                return 'fas fa-file';
            case 'photo':
                return 'fas fa-image';
            case 'video':
                return 'fas fa-video';
            case 'text':
                return 'fas fa-comment';
            default:
                return 'fas fa-circle';
        }
    }

    /**
     * Get activity color based on status
     */
    public function getColorAttribute()
    {
        switch ($this->status) {
            case 'success':
                return 'success';
            case 'failed':
                return 'danger';
            case 'pending':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    /**
     * Get human-readable activity description
     */
    public function getDescriptionAttribute()
    {
        $user = $this->telegram_username ? '@' . $this->telegram_username : 'User';
        
        switch ($this->message_type) {
            case 'command':
                return "{$user} executed command: {$this->command}";
            case 'document':
            case 'file':
                return "{$user} uploaded file: {$this->file_name}";
            case 'photo':
                return "{$user} uploaded photo: {$this->file_name}";
            case 'video':
                return "{$user} uploaded video: {$this->file_name}";
            case 'text':
                return "{$user} sent message";
            default:
                return "{$user} performed action";
        }
    }

    /**
     * Log a new activity
     */
    public static function log($data)
    {
        return self::create($data);
    }

    /**
     * Log a command execution
     */
    public static function logCommand($user, $chatId, $command, $params = null, $status = 'success', $projectId = null)
    {
        return self::create([
            'telegram_user_id' => $user['id'],
            'telegram_username' => $user['username'] ?? null,
            'chat_id' => $chatId,
            'message_type' => 'command',
            'command' => $command,
            'command_params' => $params,
            'project_id' => $projectId,
            'status' => $status,
        ]);
    }

    /**
     * Log a file upload
     */
    public static function logFileUpload($user, $chatId, $fileData, $projectId, $status = 'success', $error = null)
    {
        return self::create([
            'telegram_user_id' => $user['id'],
            'telegram_username' => $user['username'] ?? null,
            'chat_id' => $chatId,
            'message_type' => $fileData['type'] ?? 'file',
            'file_name' => $fileData['file_name'] ?? null,
            'file_size' => $fileData['file_size'] ?? null,
            'file_path' => $fileData['file_path'] ?? null,
            'telegram_file_id' => $fileData['file_id'] ?? null,
            'telegram_original_path' => $fileData['original_path'] ?? null,
            'project_id' => $projectId,
            'status' => $status,
            'error_message' => $error,
        ]);
    }
}