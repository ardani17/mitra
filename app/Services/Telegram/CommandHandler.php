<?php

namespace App\Services\Telegram;

use App\Models\BotUserSession;
use App\Models\BotActivity;
use App\Models\BotCommandHistory;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class CommandHandler
{
    protected $telegramService;
    protected $fileProcessingService;
    
    public function __construct(TelegramService $telegramService, FileProcessingService $fileProcessingService)
    {
        $this->telegramService = $telegramService;
        $this->fileProcessingService = $fileProcessingService;
    }

    /**
     * Handle incoming command
     */
    public function handleCommand($message)
    {
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        $text = $message['text'] ?? '';
        
        // Check if user is allowed
        if (!$this->telegramService->isUserAllowed($user['id'])) {
            return $this->sendUnauthorizedMessage($chatId);
        }
        
        // Get or create user session
        $session = BotUserSession::getOrCreate($user, $chatId);
        $session->touchActivity();
        
        // Parse command and parameters
        $parts = explode(' ', $text);
        $command = str_replace('/', '', array_shift($parts));
        $params = implode(' ', $parts);
        
        // Log command
        $logComplete = BotCommandHistory::logCommand($user, $chatId, $command, $params, $session->current_project_id);
        
        try {
            // Route to appropriate handler
            switch ($command) {
                case 'start':
                    $result = $this->handleStart($chatId, $user, $session);
                    break;
                    
                case 'help':
                    $result = $this->handleHelp($chatId);
                    break;
                    
                case 'cari':
                case 'search':
                    $result = $this->handleSearch($chatId, $params, $session);
                    break;
                    
                case 'pilih':
                case 'select':
                    $result = $this->handleSelectProject($chatId, $params, $session);
                    break;
                    
                case 'status':
                    $result = $this->handleStatus($chatId, $session);
                    break;
                    
                case 'list':
                    $result = $this->handleListFiles($chatId, $session);
                    break;
                    
                case 'folder':
                    $result = $this->handleCreateFolder($chatId, $params, $session);
                    break;
                    
                case 'clear':
                    $result = $this->handleClear($chatId, $session);
                    break;
                    
                default:
                    $result = $this->handleUnknownCommand($chatId, $command);
            }
            
            $logComplete('success', $result);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Command handler error', [
                'command' => $command,
                'error' => $e->getMessage()
            ]);
            
            $logComplete('failed', $e->getMessage());
            
            $this->telegramService->sendMessage($chatId, 
                "❌ Terjadi kesalahan saat memproses perintah.\n" .
                "Error: " . $e->getMessage()
            );
            
            return false;
        }
    }

    /**
     * Handle /start command
     */
    protected function handleStart($chatId, $user, $session)
    {
        $name = $session->getFullName();
        
        $message = "👋 Selamat datang, <b>{$this->telegramService->formatHtml($name)}</b>!\n\n";
        $message .= "🤖 Saya adalah Bot Manajemen Proyek yang akan membantu Anda:\n";
        $message .= "• 🔍 Mencari proyek\n";
        $message .= "• 📁 Mengelola file proyek\n";
        $message .= "• 📤 Upload dokumen\n";
        $message .= "• 📊 Melihat status proyek\n\n";
        $message .= "Gunakan /help untuk melihat daftar perintah yang tersedia.";
        
        $keyboard = [
            [
                ['text' => '🔍 Cari Proyek', 'callback_data' => 'search_project'],
                ['text' => '📊 Status', 'callback_data' => 'show_status']
            ],
            [
                ['text' => '❓ Bantuan', 'callback_data' => 'show_help']
            ]
        ];
        
        $this->telegramService->sendMessageWithKeyboard($chatId, $message, $keyboard);
        
        return "Start command executed";
    }

    /**
     * Handle /help command
     */
    protected function handleHelp($chatId)
    {
        $message = "📚 <b>Daftar Perintah Bot</b>\n\n";
        $message .= "/start - Memulai bot\n";
        $message .= "/help - Menampilkan bantuan\n";
        $message .= "/cari [keyword] - Mencari proyek\n";
        $message .= "/pilih [kode_proyek] - Memilih proyek aktif\n";
        $message .= "/status - Melihat status saat ini\n";
        $message .= "/list - Melihat daftar file dalam proyek\n";
        $message .= "/folder [nama] - Membuat folder baru\n";
        $message .= "/clear - Hapus proyek aktif\n\n";
        $message .= "💡 <b>Tips:</b>\n";
        $message .= "• Kirim file untuk upload ke proyek aktif\n";
        $message .= "• Gunakan /pilih untuk memilih proyek sebelum upload\n";
        $message .= "• File akan otomatis diorganisir berdasarkan tipe\n";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        return "Help displayed";
    }

    /**
     * Handle /cari command
     */
    protected function handleSearch($chatId, $keyword, $session)
    {
        if (empty($keyword)) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Silakan masukkan kata kunci pencarian.\n" .
                "Contoh: /cari sarirejo"
            );
            return "Search keyword required";
        }
        
        // Search projects
        $projects = Project::where(function($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('location', 'like', "%{$keyword}%");
        })
        ->with('customer')
        ->limit(10)
        ->get();
        
        if ($projects->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Tidak ditemukan proyek dengan kata kunci: <b>{$this->telegramService->formatHtml($keyword)}</b>\n\n" .
                "Coba kata kunci lain atau gunakan /list untuk melihat semua proyek."
            );
            return "No projects found";
        }
        
        $message = "🔍 <b>Hasil Pencarian:</b> {$this->telegramService->formatHtml($keyword)}\n";
        $message .= "Ditemukan {$projects->count()} proyek:\n\n";
        
        $keyboard = [];
        foreach ($projects as $project) {
            $message .= "📁 <b>{$this->telegramService->formatHtml($project->name)}</b>\n";
            $message .= "   📌 Kode: <code>{$project->code}</code>\n";
            $message .= "   🏢 Customer: {$this->telegramService->formatHtml($project->customer->name ?? 'N/A')}\n";
            $message .= "   📍 Lokasi: {$this->telegramService->formatHtml($project->location ?? 'N/A')}\n\n";
            
            // Add to keyboard
            $keyboard[] = [
                [
                    'text' => "📁 {$project->code} - {$project->name}",
                    'callback_data' => "select_project_{$project->id}"
                ]
            ];
        }
        
        $message .= "Klik salah satu proyek di bawah untuk memilihnya:";
        
        $this->telegramService->sendMessageWithKeyboard($chatId, $message, $keyboard);
        
        $session->setState('searching');
        
        return "Search completed with {$projects->count()} results";
    }

    /**
     * Handle /pilih command
     */
    protected function handleSelectProject($chatId, $projectCode, $session)
    {
        if (empty($projectCode)) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Silakan masukkan kode proyek.\n" .
                "Contoh: /pilih 3SBU-BBE-PT3"
            );
            return "Project code required";
        }
        
        // Find project by code
        $project = Project::where('code', $projectCode)->first();
        
        if (!$project) {
            $this->telegramService->sendMessage($chatId, 
                "❌ Proyek dengan kode <code>{$this->telegramService->formatHtml($projectCode)}</code> tidak ditemukan.\n\n" .
                "Gunakan /cari untuk mencari proyek."
            );
            return "Project not found";
        }
        
        // Set as current project
        $session->setCurrentProject($project->id);
        
        // Send project info
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
        
        $this->telegramService->sendMessageWithKeyboard($chatId, $message, $keyboard);
        
        BotActivity::logCommand($session->toArray(), $chatId, 'select', ['project_id' => $project->id], 'success', $project->id);
        
        return "Project selected: {$project->code}";
    }

    /**
     * Handle /status command
     */
    protected function handleStatus($chatId, $session)
    {
        $message = "📊 <b>Status Saat Ini</b>\n\n";
        
        if ($session->current_project_id) {
            $project = Project::find($session->current_project_id);
            if ($project) {
                $message .= "✅ <b>Proyek Aktif:</b>\n";
                $message .= $this->telegramService->buildProjectInfo($project);
                
                // Get recent uploads
                $recentUploads = BotActivity::where('telegram_user_id', $session->telegram_user_id)
                    ->where('project_id', $project->id)
                    ->where('message_type', 'file')
                    ->where('status', 'success')
                    ->latest()
                    ->limit(5)
                    ->get();
                
                if ($recentUploads->isNotEmpty()) {
                    $message .= "\n📤 <b>Upload Terakhir:</b>\n";
                    foreach ($recentUploads as $upload) {
                        $message .= "• {$upload->file_name} ({$upload->formatted_file_size})\n";
                    }
                }
            } else {
                $message .= "⚠️ Proyek yang dipilih tidak ditemukan.\n";
                $session->clearCurrentProject();
            }
        } else {
            $message .= "❌ Belum ada proyek yang dipilih.\n";
            $message .= "Gunakan /cari untuk mencari proyek atau /pilih untuk memilih proyek.";
        }
        
        $message .= "\n👤 <b>User:</b> {$session->getDisplayName()}\n";
        $message .= "🕐 <b>Last Activity:</b> {$session->last_activity_at->diffForHumans()}\n";
        $message .= "📱 <b>State:</b> {$session->state}";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        return "Status displayed";
    }

    /**
     * Handle /list command
     */
    protected function handleListFiles($chatId, $session)
    {
        if (!$session->current_project_id) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Pilih proyek terlebih dahulu dengan /pilih [kode_proyek]"
            );
            return "No project selected";
        }
        
        $project = Project::find($session->current_project_id);
        if (!$project) {
            $this->telegramService->sendMessage($chatId, "❌ Proyek tidak ditemukan.");
            $session->clearCurrentProject();
            return "Project not found";
        }
        
        // Get files from storage
        $storageService = app(\App\Services\StorageService::class);
        $files = $storageService->getProjectDocuments($project);
        
        if ($files->isEmpty()) {
            $this->telegramService->sendMessage($chatId,
                "📁 <b>Proyek: {$this->telegramService->formatHtml($project->name)}</b>\n\n" .
                "📭 Belum ada file dalam proyek ini.\n" .
                "Kirim file untuk mulai upload."
            );
            return "No files in project";
        }
        
        $message = "📁 <b>File dalam Proyek: {$this->telegramService->formatHtml($project->name)}</b>\n\n";
        $totalSize = 0;
        
        foreach ($files as $file) {
            $icon = $this->getFileIcon($file->mime_type);
            $message .= "{$icon} {$file->name}\n";
            $message .= "   📊 {$this->telegramService->formatFileSize($file->file_size)}\n";
            $message .= "   📅 {$file->created_at->format('d/m/Y H:i')}\n\n";
            $totalSize += $file->file_size;
        }
        
        $message .= "📊 <b>Total:</b> {$files->count()} file ({$this->telegramService->formatFileSize($totalSize)})";
        
        $this->telegramService->sendMessage($chatId, $message);
        
        return "Files listed: {$files->count()}";
    }

    /**
     * Handle /folder command
     */
    protected function handleCreateFolder($chatId, $folderName, $session)
    {
        if (!$session->current_project_id) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Pilih proyek terlebih dahulu dengan /pilih [kode_proyek]"
            );
            return "No project selected";
        }
        
        if (empty($folderName)) {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Silakan masukkan nama folder.\n" .
                "Contoh: /folder laporan-bulanan"
            );
            return "Folder name required";
        }
        
        $project = Project::find($session->current_project_id);
        if (!$project) {
            $this->telegramService->sendMessage($chatId, "❌ Proyek tidak ditemukan.");
            $session->clearCurrentProject();
            return "Project not found";
        }
        
        // Sanitize folder name
        $folderName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $folderName);
        
        // Create folder
        $folderPath = "proyek/{$project->code}/dokumen/{$folderName}";
        $fullPath = storage_path("app/{$folderPath}");
        
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
            
            $this->telegramService->sendMessage($chatId, 
                "✅ Folder berhasil dibuat!\n\n" .
                "📁 <b>{$folderName}</b>\n" .
                "📍 Path: {$folderPath}\n\n" .
                "File yang dikirim akan disimpan dalam folder ini."
            );
            
            $session->setCurrentFolder($folderName);
            
            return "Folder created: {$folderName}";
        } else {
            $this->telegramService->sendMessage($chatId, 
                "⚠️ Folder <b>{$folderName}</b> sudah ada."
            );
            return "Folder already exists";
        }
    }

    /**
     * Handle /clear command
     */
    protected function handleClear($chatId, $session)
    {
        $session->clearCurrentProject();
        
        $this->telegramService->sendMessage($chatId, 
            "✅ Proyek aktif telah dihapus.\n\n" .
            "Gunakan /cari atau /pilih untuk memilih proyek baru."
        );
        
        return "Session cleared";
    }

    /**
     * Handle unknown command
     */
    protected function handleUnknownCommand($chatId, $command)
    {
        $this->telegramService->sendMessage($chatId, 
            "❓ Perintah <code>/{$command}</code> tidak dikenali.\n\n" .
            "Gunakan /help untuk melihat daftar perintah yang tersedia."
        );
        
        return "Unknown command";
    }

    /**
     * Send unauthorized message
     */
    protected function sendUnauthorizedMessage($chatId)
    {
        $this->telegramService->sendMessage($chatId, 
            "🚫 <b>Akses Ditolak</b>\n\n" .
            "Anda tidak memiliki izin untuk menggunakan bot ini.\n" .
            "Silakan hubungi administrator untuk mendapatkan akses."
        );
        
        return false;
    }

    /**
     * Get file icon based on mime type
     */
    protected function getFileIcon($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) return '🖼️';
        if (str_starts_with($mimeType, 'video/')) return '🎥';
        if (str_contains($mimeType, 'pdf')) return '📕';
        if (str_contains($mimeType, 'word')) return '📘';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) return '📗';
        if (str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation')) return '📙';
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, '7z')) return '🗜️';
        return '📄';
    }
}