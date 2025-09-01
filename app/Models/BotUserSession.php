<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class BotUserSession extends Model
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
        'first_name',
        'last_name',
        'chat_id',
        'current_project_id',
        'current_folder',
        'last_command',
        'session_data',
        'state',
        'last_activity_at',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'session_data' => 'array',
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
    ];

    /**
     * Get the current project
     */
    public function currentProject()
    {
        return $this->belongsTo(Project::class, 'current_project_id');
    }

    /**
     * Get or create session for a Telegram user
     */
    public static function getOrCreate($telegramUser, $chatId)
    {
        return self::firstOrCreate(
            ['telegram_user_id' => $telegramUser['id']],
            [
                'telegram_username' => $telegramUser['username'] ?? null,
                'first_name' => $telegramUser['first_name'] ?? null,
                'last_name' => $telegramUser['last_name'] ?? null,
                'chat_id' => $chatId,
                'last_activity_at' => now(),
                'is_active' => true,
            ]
        );
    }

    /**
     * Update last activity
     */
    public function touchActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Set the current project
     */
    public function setCurrentProject($projectId)
    {
        $this->update([
            'current_project_id' => $projectId,
            'current_folder' => null,
            'state' => 'project_selected'
        ]);
    }

    /**
     * Clear the current project
     */
    public function clearCurrentProject()
    {
        $this->update([
            'current_project_id' => null,
            'current_folder' => null,
            'state' => 'idle'
        ]);
    }

    /**
     * Set the current folder
     */
    public function setCurrentFolder($folder)
    {
        $this->update(['current_folder' => $folder]);
    }

    /**
     * Update session state
     */
    public function setState($state)
    {
        $this->update(['state' => $state]);
    }

    /**
     * Store session data
     */
    public function setSessionData($key, $value)
    {
        $data = $this->session_data ?? [];
        $data[$key] = $value;
        $this->update(['session_data' => $data]);
    }

    /**
     * Get session data
     */
    public function getSessionData($key, $default = null)
    {
        $data = $this->session_data ?? [];
        return $data[$key] ?? $default;
    }

    /**
     * Clear session data
     */
    public function clearSessionData($key = null)
    {
        if ($key) {
            $data = $this->session_data ?? [];
            unset($data[$key]);
            $this->update(['session_data' => $data]);
        } else {
            $this->update(['session_data' => null]);
        }
    }

    /**
     * Check if session is expired (inactive for more than 24 hours)
     */
    public function isExpired()
    {
        return $this->last_activity_at->diffInHours(now()) > 24;
    }

    /**
     * Get full name of the user
     */
    public function getFullName()
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: $this->telegram_username ?: 'User';
    }

    /**
     * Get display name (username or full name)
     */
    public function getDisplayName()
    {
        return $this->telegram_username ? '@' . $this->telegram_username : $this->getFullName();
    }
}