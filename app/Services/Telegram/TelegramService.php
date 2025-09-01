<?php

namespace App\Services\Telegram;

use App\Models\BotConfiguration;
use App\Models\BotUserSession;
use App\Models\BotActivity;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $config;
    protected $apiUrl;
    protected $client;

    public function __construct()
    {
        $this->config = BotConfiguration::getActive();
        
        if ($this->config) {
            $this->apiUrl = $this->config->getBotApiUrl();
            $this->client = Http::timeout(30);
        }
    }

    /**
     * Send a message to a chat
     */
    public function sendMessage($chatId, $text, $options = [])
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->makeRequest('sendMessage', $params);
    }

    /**
     * Send a message with inline keyboard
     */
    public function sendMessageWithKeyboard($chatId, $text, $keyboard)
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }

    /**
     * Send a message with reply keyboard
     */
    public function sendMessageWithReplyKeyboard($chatId, $text, $keyboard, $oneTime = false)
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => $oneTime,
                'resize_keyboard' => true
            ])
        ]);
    }

    /**
     * Edit a message
     */
    public function editMessage($chatId, $messageId, $text, $options = [])
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->makeRequest('editMessageText', $params);
    }

    /**
     * Delete a message
     */
    public function deleteMessage($chatId, $messageId)
    {
        return $this->makeRequest('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Send a document
     */
    public function sendDocument($chatId, $filePath, $caption = null)
    {
        $params = [
            'chat_id' => $chatId,
        ];

        if ($caption) {
            $params['caption'] = $caption;
        }

        return $this->client->attach(
            'document', 
            fopen($filePath, 'r'), 
            basename($filePath)
        )->post($this->apiUrl . '/sendDocument', $params);
    }

    /**
     * Send a photo
     */
    public function sendPhoto($chatId, $photoPath, $caption = null)
    {
        $params = [
            'chat_id' => $chatId,
        ];

        if ($caption) {
            $params['caption'] = $caption;
        }

        return $this->client->attach(
            'photo', 
            fopen($photoPath, 'r'), 
            basename($photoPath)
        )->post($this->apiUrl . '/sendPhoto', $params);
    }

    /**
     * Get file info
     */
    public function getFile($fileId)
    {
        $response = $this->makeRequest('getFile', [
            'file_id' => $fileId
        ]);

        if ($response && $response['ok']) {
            return $response['result'];
        }

        return null;
    }

    /**
     * Download file from Telegram
     */
    public function downloadFile($filePath)
    {
        $url = $this->config->use_local_server 
            ? "http://{$this->config->server_host}:{$this->config->server_port}/file/bot{$this->config->bot_token}/{$filePath}"
            : "https://api.telegram.org/file/bot{$this->config->bot_token}/{$filePath}";

        return Http::get($url)->body();
    }

    /**
     * Set webhook
     */
    public function setWebhook($url = null)
    {
        $webhookUrl = $url ?: $this->config->getWebhookUrl();
        
        return $this->makeRequest('setWebhook', [
            'url' => $webhookUrl,
            'allowed_updates' => ['message', 'callback_query', 'inline_query'],
            'max_connections' => 100,
        ]);
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook()
    {
        return $this->makeRequest('deleteWebhook');
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo()
    {
        return $this->makeRequest('getWebhookInfo');
    }

    /**
     * Answer callback query
     */
    public function answerCallbackQuery($callbackQueryId, $text = null, $showAlert = false)
    {
        $params = [
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ];

        if ($text) {
            $params['text'] = $text;
        }

        return $this->makeRequest('answerCallbackQuery', $params);
    }

    /**
     * Send chat action (typing, upload_photo, etc.)
     */
    public function sendChatAction($chatId, $action = 'typing')
    {
        return $this->makeRequest('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action,
        ]);
    }

    /**
     * Get bot info
     */
    public function getMe()
    {
        return $this->makeRequest('getMe');
    }

    /**
     * Make API request
     */
    protected function makeRequest($method, $params = [])
    {
        try {
            $response = $this->client->post($this->apiUrl . '/' . $method, $params);
            
            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Telegram API error', [
                'method' => $method,
                'params' => $params,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram API exception', [
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Check if user is allowed
     */
    public function isUserAllowed($telegramUserId)
    {
        if (!$this->config) {
            return false;
        }

        return $this->config->isUserAllowed($telegramUserId);
    }

    /**
     * Format text for Telegram HTML
     */
    public function formatHtml($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build project info message
     */
    public function buildProjectInfo(Project $project)
    {
        $status = $this->getProjectStatusEmoji($project->status);
        
        $message = "<b>📁 Proyek: {$this->formatHtml($project->name)}</b>\n";
        $message .= "📌 Kode: <code>{$project->code}</code>\n";
        $message .= "🏢 Customer: {$this->formatHtml($project->customer->name ?? 'N/A')}\n";
        $message .= "📍 Lokasi: {$this->formatHtml($project->location ?? 'N/A')}\n";
        $message .= "{$status} Status: {$project->status}\n";
        
        if ($project->start_date) {
            $message .= "📅 Mulai: {$project->start_date->format('d/m/Y')}\n";
        }
        
        if ($project->end_date) {
            $message .= "🏁 Selesai: {$project->end_date->format('d/m/Y')}\n";
        }

        return $message;
    }

    /**
     * Get emoji for project status
     */
    protected function getProjectStatusEmoji($status)
    {
        $emojis = [
            'planning' => '📝',
            'in_progress' => '🚧',
            'completed' => '✅',
            'cancelled' => '❌',
            'on_hold' => '⏸️',
        ];

        return $emojis[$status] ?? '📊';
    }

    /**
     * Format file size
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Build pagination keyboard
     */
    public function buildPaginationKeyboard($currentPage, $totalPages, $callbackPrefix)
    {
        $keyboard = [];
        $row = [];

        // Previous button
        if ($currentPage > 1) {
            $row[] = [
                'text' => '⬅️ Previous',
                'callback_data' => "{$callbackPrefix}_page_" . ($currentPage - 1)
            ];
        }

        // Page info
        $row[] = [
            'text' => "📄 {$currentPage}/{$totalPages}",
            'callback_data' => 'noop'
        ];

        // Next button
        if ($currentPage < $totalPages) {
            $row[] = [
                'text' => 'Next ➡️',
                'callback_data' => "{$callbackPrefix}_page_" . ($currentPage + 1)
            ];
        }

        if (!empty($row)) {
            $keyboard[] = $row;
        }

        return $keyboard;
    }
}