<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bot_name',
        'bot_token',
        'bot_username',
        'server_host',
        'server_port',
        'bot_api_base_path',
        'bot_api_temp_path',
        'bot_api_documents_path',
        'bot_api_photos_path',
        'bot_api_videos_path',
        'laravel_storage_path',
        'use_local_server',
        'webhook_url',
        'max_file_size_mb',
        'allowed_users',
        'auto_cleanup',
        'cleanup_after_hours',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allowed_users' => 'array',
        'use_local_server' => 'boolean',
        'auto_cleanup' => 'boolean',
        'is_active' => 'boolean',
        'server_port' => 'integer',
        'max_file_size_mb' => 'integer',
        'cleanup_after_hours' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'bot_token',
    ];

    /**
     * Get the active bot configuration
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Check if a Telegram user is allowed
     */
    public function isUserAllowed($telegramUserId)
    {
        if (empty($this->allowed_users)) {
            return false;
        }

        return in_array($telegramUserId, $this->allowed_users);
    }

    /**
     * Add a user to the allowed list
     */
    public function addAllowedUser($telegramUserId, $username = null)
    {
        $users = $this->allowed_users ?? [];
        
        if (!in_array($telegramUserId, array_column($users, 'id'))) {
            $users[] = [
                'id' => $telegramUserId,
                'username' => $username,
                'added_at' => now()->toIso8601String()
            ];
            
            $this->allowed_users = $users;
            $this->save();
        }
    }

    /**
     * Remove a user from the allowed list
     */
    public function removeAllowedUser($telegramUserId)
    {
        $users = $this->allowed_users ?? [];
        
        $this->allowed_users = array_values(array_filter($users, function($user) use ($telegramUserId) {
            return $user['id'] != $telegramUserId;
        }));
        
        $this->save();
    }

    /**
     * Get the full Bot API URL
     */
    public function getBotApiUrl()
    {
        $protocol = $this->use_local_server ? 'http' : 'https';
        return "{$protocol}://{$this->server_host}:{$this->server_port}/bot{$this->bot_token}";
    }

    /**
     * Get the webhook URL
     */
    public function getWebhookUrl()
    {
        if ($this->webhook_url) {
            return $this->webhook_url;
        }

        return url('/api/telegram/webhook');
    }

    /**
     * Build the Bot API file path
     */
    public function getBotApiFilePath($fileType, $fileName)
    {
        $basePath = $this->bot_api_base_path;
        $token = $this->bot_token;
        
        switch ($fileType) {
            case 'document':
                $subPath = $this->bot_api_documents_path ?? 'documents';
                break;
            case 'photo':
                $subPath = $this->bot_api_photos_path ?? 'photos';
                break;
            case 'video':
                $subPath = $this->bot_api_videos_path ?? 'videos';
                break;
            default:
                $subPath = $this->bot_api_temp_path ?? 'temp';
        }
        
        return "{$basePath}/{$token}/{$subPath}/{$fileName}";
    }
}