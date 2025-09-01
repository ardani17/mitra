<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramService;
use App\Services\Telegram\CommandHandler;
use App\Services\Telegram\FileProcessingService;
use App\Models\BotUserSession;
use App\Models\BotActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;
    protected $commandHandler;
    protected $fileProcessingService;

    public function __construct(
        TelegramService $telegramService,
        CommandHandler $commandHandler,
        FileProcessingService $fileProcessingService
    ) {
        $this->telegramService = $telegramService;
        $this->commandHandler = $commandHandler;
        $this->fileProcessingService = $fileProcessingService;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            
            Log::info('Telegram webhook received', ['update' => $update]);
            
            // Handle different update types
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            } elseif (isset($update['inline_query'])) {
                $this->handleInlineQuery($update['inline_query']);
            }
            
            return response()->json(['ok' => true]);
            
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle regular messages
     */
    protected function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        
        // Check if user is allowed
        if (!$this->telegramService->isUserAllowed($user['id'])) {
            $this->sendUnauthorizedMessage($chatId);
            return;
        }
        
        // Get or create user session
        $session = BotUserSession::getOrCreate($user, $chatId);
        $session->touchActivity();
        
        // Handle different message types
        if (isset($message['text'])) {
            // Check if it's a command
            if (str_starts_with($message['text'], '/')) {
                $this->commandHandler->handleCommand($message);
            } else {
                // Handle regular text message
                $this->handleTextMessage($message, $session);
            }
        } elseif (isset($message['document'])) {
            $this->handleDocument($message, $session);
        } elseif (isset($message['photo'])) {
            $this->handlePhoto($message, $session);
        } elseif (isset($message['video'])) {
            $this->handleVideo($message, $session);
        } else {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Tipe pesan ini belum didukung.\n" .
                "Silakan kirim dokumen, foto, atau video."
            );
        }
    }

    /**
     * Handle text messages (non-command)
     */
    protected function handleTextMessage($message, $session)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'];
        
        // Check if user is in search mode
        if ($session->state === 'searching') {
            // Treat as search query
            $this->commandHandler->handleCommand([
                'chat' => $message['chat'],
                'from' => $message['from'],
                'text' => '/cari ' . $text
            ]);
        } else {
            // Default response for non-command text
            $this->telegramService->sendMessage($chatId, 
                "💬 Pesan diterima: \"{$text}\"\n\n" .
                "Gunakan /help untuk melihat daftar perintah yang tersedia."
            );
        }
    }

    /**
     * Handle document uploads
     */
    protected function handleDocument($message, $session)
    {
        $this->handleFileUpload($message, $session, 'document', $message['document']);
    }

    /**
     * Handle photo uploads
     */
    protected function handlePhoto($message, $session)
    {
        // Get the highest resolution photo
        $photo = end($message['photo']);
        $this->handleFileUpload($message, $session, 'photo', $photo);
    }

    /**
     * Handle video uploads
     */
    protected function handleVideo($message, $session)
    {
        $this->handleFileUpload($message, $session, 'video', $message['video']);
    }

    /**
     * Generic file upload handler
     */
    protected function handleFileUpload($message, $session, $type, $fileData)
    {
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        
        // Check if project is selected
        if (!$session->current_project_id) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ <b>Pilih proyek terlebih dahulu!</b>\n\n" .
                "Gunakan /cari untuk mencari proyek atau /pilih [kode_proyek] untuk memilih proyek.\n\n" .
                "File tidak akan disimpan tanpa proyek yang dipilih."
            );
            return;
        }
        
        // Send typing indicator
        $this->telegramService->sendChatAction($chatId, 'upload_document');
        
        // Prepare file data
        $fileData['type'] = $type;
        $fileData['file_name'] = $fileData['file_name'] ?? 
            ($type === 'photo' ? 'photo_' . time() . '.jpg' : 
            ($type === 'video' ? 'video_' . time() . '.mp4' : 'file_' . time()));
        
        // Get file info from Telegram
        $fileInfo = $this->telegramService->getFile($fileData['file_id']);
        if ($fileInfo) {
            $fileData['file_path'] = $fileInfo['file_path'];
        }
        
        try {
            // Queue the file for processing
            $queueItem = $this->fileProcessingService->queueFileUpload(
                $user,
                $chatId,
                $fileData,
                $session->current_project_id
            );
            
            // Try to process immediately
            $document = $this->fileProcessingService->processUploadedFile(
                $fileData,
                $session->current_project_id
            );
            
            // Mark queue item as completed
            $queueItem->markAsCompleted();
            
            // Log activity
            BotActivity::logFileUpload(
                $user,
                $chatId,
                array_merge($fileData, [
                    'file_path' => $document->file_path,
                    'file_size' => $document->file_size,
                ]),
                $session->current_project_id,
                'success'
            );
            
            // Send success message
            $project = \App\Models\Project::find($session->current_project_id);
            $this->telegramService->sendMessage($chatId, 
                "✅ <b>File berhasil disimpan!</b>\n\n" .
                "📁 Proyek: {$this->telegramService->formatHtml($project->name)}\n" .
                "📄 File: {$document->name}\n" .
                "📊 Ukuran: {$this->telegramService->formatFileSize($document->file_size)}\n" .
                "📂 Lokasi: {$document->file_path}\n\n" .
                "File dapat diakses melalui web dashboard."
            );
            
        } catch (\Exception $e) {
            // Log error
            BotActivity::logFileUpload(
                $user,
                $chatId,
                $fileData,
                $session->current_project_id,
                'failed',
                $e->getMessage()
            );
            
            $this->telegramService->sendMessage($chatId, 
                "❌ <b>Gagal menyimpan file!</b>\n\n" .
                "Error: {$e->getMessage()}\n\n" .
                "Silakan coba lagi atau hubungi administrator."
            );
        }
    }

    /**
     * Handle callback queries (inline keyboard buttons)
     */
    protected function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $user = $callbackQuery['from'];
        $data = $callbackQuery['data'];
        
        // Answer the callback query to remove loading state
        $this->telegramService->answerCallbackQuery($callbackQuery['id']);
        
        // Check if user is allowed
        if (!$this->telegramService->isUserAllowed($user['id'])) {
            $this->sendUnauthorizedMessage($chatId);
            return;
        }
        
        // Get user session
        $session = BotUserSession::getOrCreate($user, $chatId);
        $session->touchActivity();
        
        // Parse callback data
        if (str_starts_with($data, 'select_project_')) {
            $projectId = str_replace('select_project_', '', $data);
            $this->handleProjectSelection($chatId, $messageId, $projectId, $session);
        } elseif ($data === 'search_project') {
            $this->telegramService->editMessage($chatId, $messageId, 
                "🔍 <b>Pencarian Proyek</b>\n\n" .
                "Ketik kata kunci untuk mencari proyek.\n" .
                "Contoh: sarirejo, BBE, PT3"
            );
            $session->setState('searching');
        } elseif ($data === 'show_status') {
            $this->commandHandler->handleCommand([
                'chat' => ['id' => $chatId],
                'from' => $user,
                'text' => '/status'
            ]);
        } elseif ($data === 'show_help') {
            $this->commandHandler->handleCommand([
                'chat' => ['id' => $chatId],
                'from' => $user,
                'text' => '/help'
            ]);
        } elseif ($data === 'list_files') {
            $this->commandHandler->handleCommand([
                'chat' => ['id' => $chatId],
                'from' => $user,
                'text' => '/list'
            ]);
        } elseif ($data === 'upload_guide') {
            $this->telegramService->sendMessage($chatId, 
                "📤 <b>Panduan Upload File</b>\n\n" .
                "1. Pastikan proyek sudah dipilih (gunakan /pilih)\n" .
                "2. Kirim file langsung ke bot:\n" .
                "   • Dokumen (PDF, Word, Excel, dll)\n" .
                "   • Foto/Gambar\n" .
                "   • Video\n" .
                "3. File akan otomatis disimpan ke proyek aktif\n" .
                "4. File diorganisir berdasarkan tipe\n\n" .
                "💡 <b>Tips:</b>\n" .
                "• Maksimal ukuran file: 2GB (local server)\n" .
                "• Kirim multiple files sekaligus\n" .
                "• Gunakan /list untuk melihat file yang sudah diupload"
            );
        } elseif ($data === 'project_info') {
            if ($session->current_project_id) {
                $project = \App\Models\Project::find($session->current_project_id);
                if ($project) {
                    $message = $this->telegramService->buildProjectInfo($project);
                    $this->telegramService->sendMessage($chatId, $message);
                }
            }
        } elseif ($data === 'change_project') {
            $session->clearCurrentProject();
            $this->telegramService->editMessage($chatId, $messageId, 
                "🔄 <b>Ganti Proyek</b>\n\n" .
                "Proyek aktif telah dihapus.\n" .
                "Gunakan /cari atau /pilih untuk memilih proyek baru."
            );
        }
    }

    /**
     * Handle project selection from callback
     */
    protected function handleProjectSelection($chatId, $messageId, $projectId, $session)
    {
        $project = \App\Models\Project::find($projectId);
        
        if (!$project) {
            $this->telegramService->editMessage($chatId, $messageId, 
                "❌ Proyek tidak ditemukan."
            );
            return;
        }
        
        // Set as current project
        $session->setCurrentProject($project->id);
        
        // Update message
        $message = "✅ <b>Proyek berhasil dipilih!</b>\n\n";
        $message .= $this->telegramService->buildProjectInfo($project);
        $message .= "\n📤 Sekarang Anda dapat mengirim file untuk diupload ke proyek ini.";
        
        $keyboard = [
            [
                ['text' => '📁 Lihat File', 'callback_data' => 'list_files'],
                ['text' => '📤 Upload File', 'callback_data' => 'upload_guide']
            ],
            [
                ['text' => '📊 Info Proyek', 'callback_data' => 'project_info'],
                ['text' => '🔄 Ganti Proyek', 'callback_data' => 'change_project']
            ]
        ];
        
        $this->telegramService->editMessage($chatId, $messageId, $message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
        
        // Log activity
        BotActivity::logCommand($session->toArray(), $chatId, 'select', ['project_id' => $project->id], 'success', $project->id);
    }

    /**
     * Handle inline queries
     */
    protected function handleInlineQuery($inlineQuery)
    {
        // Not implemented yet
        // This would handle @bot_username queries in any chat
    }

    /**
     * Send unauthorized message
     */
    protected function sendUnauthorizedMessage($chatId)
    {
        $this->telegramService->sendMessage($chatId, 
            "🚫 <b>Akses Ditolak</b>\n\n" .
            "Anda tidak memiliki izin untuk menggunakan bot ini.\n" .
            "Silakan hubungi administrator untuk mendapatkan akses.\n\n" .
            "User ID Anda: <code>{$chatId}</code>"
        );
    }
}