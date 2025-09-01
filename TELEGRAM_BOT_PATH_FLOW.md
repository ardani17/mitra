# 📂 Telegram Bot File Path Flow

## 🔄 File Upload Flow Architecture

```mermaid
graph LR
    A[User Telegram] -->|Send File| B[Telegram Server]
    B -->|Forward to| C[Local Bot API Server<br/>localhost:8081]
    C -->|Save to| D[Bot API Path<br/>/var/lib/telegram-bot-api/]
    D -->|Laravel Copy| E[Storage Path<br/>storage/app/proyek/{id}/]
    E -->|Access via| F[Web Dashboard]
```

## 📁 Two-Path System

### 1. Telegram Bot API Path (Configurable)
```
/var/lib/telegram-bot-api/           # Default path (example)
├── {bot_token}/
│   ├── documents/
│   ├── photos/
│   ├── videos/
│   └── temp/
```

**Characteristics:**
- Path dikonfigurasi saat menjalankan telegram-bot-api server
- File otomatis tersimpan di sini saat diterima dari Telegram
- Temporary storage sebelum diproses Laravel
- Path ini bisa dikonfigurasi melalui Bot Configuration UI

### 2. Laravel Storage Path (Final Destination)
```
storage/app/proyek/{project-code}/
├── dokumen/
│   ├── kontrak/
│   ├── teknis/
│   ├── keuangan/
│   └── lainnya/
├── gambar/
└── video/
```

**Characteristics:**
- Permanent storage untuk aplikasi
- Organized by project structure
- Accessible via web interface
- Integrated with existing file explorer

## 🔧 Bot Configuration Schema Update

```sql
CREATE TABLE bot_configurations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bot_name VARCHAR(255) NOT NULL,
    bot_token VARCHAR(255) NOT NULL,
    bot_username VARCHAR(100),
    server_host VARCHAR(100) DEFAULT 'localhost',
    server_port INT DEFAULT 8081,
    
    -- Path configurations
    bot_api_base_path VARCHAR(500) DEFAULT '/var/lib/telegram-bot-api',
    bot_api_temp_path VARCHAR(500),
    bot_api_documents_path VARCHAR(500),
    bot_api_photos_path VARCHAR(500),
    bot_api_videos_path VARCHAR(500),
    
    -- Laravel paths
    laravel_storage_path VARCHAR(500) DEFAULT 'storage/app/proyek',
    
    -- Other configs
    use_local_server BOOLEAN DEFAULT true,
    webhook_url VARCHAR(255),
    max_file_size_mb INT DEFAULT 2000,
    allowed_users JSON,
    auto_cleanup BOOLEAN DEFAULT true,
    cleanup_after_hours INT DEFAULT 24,
    
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 💻 File Processing Service

```php
// app/Services/Telegram/FileProcessingService.php
class FileProcessingService
{
    protected $botConfig;
    protected $storageService;
    
    public function processUploadedFile($fileData, $projectId)
    {
        // 1. Get file from Bot API path
        $botApiPath = $this->getBotApiFilePath($fileData);
        
        // 2. Validate file exists
        if (!file_exists($botApiPath)) {
            throw new FileNotFoundException("File not found in bot API path: {$botApiPath}");
        }
        
        // 3. Determine Laravel storage path
        $project = Project::findOrFail($projectId);
        $targetFolder = $this->determineTargetFolder($fileData['mime_type']);
        $laravelPath = "proyek/{$project->code}/{$targetFolder}/";
        
        // 4. Copy file from Bot API path to Laravel storage
        $fileName = $this->sanitizeFileName($fileData['file_name']);
        $fullLaravelPath = storage_path("app/{$laravelPath}{$fileName}");
        
        // Create directory if not exists
        if (!file_exists(dirname($fullLaravelPath))) {
            mkdir(dirname($fullLaravelPath), 0755, true);
        }
        
        // Copy file
        if (copy($botApiPath, $fullLaravelPath)) {
            // 5. Create database record
            $document = ProjectDocument::create([
                'project_id' => $projectId,
                'name' => $fileName,
                'file_path' => $laravelPath . $fileName,
                'file_size' => filesize($fullLaravelPath),
                'mime_type' => $fileData['mime_type'],
                'telegram_file_id' => $fileData['file_id'],
                'telegram_original_path' => $botApiPath,
                'upload_source' => 'telegram',
                'uploaded_by' => auth()->id()
            ]);
            
            // 6. Optional: Clean up bot API file after successful copy
            if ($this->botConfig->auto_cleanup) {
                $this->scheduleCleanup($botApiPath);
            }
            
            return $document;
        }
        
        throw new \Exception("Failed to copy file from bot API to storage");
    }
    
    protected function getBotApiFilePath($fileData)
    {
        // Build path based on bot configuration
        $basePath = $this->botConfig->bot_api_base_path;
        $token = $this->botConfig->bot_token;
        
        // Path structure depends on file type
        switch ($fileData['type']) {
            case 'document':
                $subPath = $this->botConfig->bot_api_documents_path ?? 'documents';
                break;
            case 'photo':
                $subPath = $this->botConfig->bot_api_photos_path ?? 'photos';
                break;
            case 'video':
                $subPath = $this->botConfig->bot_api_videos_path ?? 'videos';
                break;
            default:
                $subPath = 'temp';
        }
        
        return "{$basePath}/{$token}/{$subPath}/{$fileData['file_path']}";
    }
}
```

## 🎨 Bot Configuration UI - Path Settings

```vue
<!-- Path Configuration Section -->
<div class="config-section mt-4">
    <h4>Path Configuration</h4>
    
    <div class="form-group">
        <label>Bot API Base Path</label>
        <input 
            v-model="config.bot_api_base_path" 
            class="form-control"
            placeholder="/var/lib/telegram-bot-api"
        >
        <small class="form-text text-muted">
            Base directory where telegram-bot-api stores files
        </small>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Documents Path</label>
                <input 
                    v-model="config.bot_api_documents_path" 
                    class="form-control"
                    placeholder="documents"
                >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Photos Path</label>
                <input 
                    v-model="config.bot_api_photos_path" 
                    class="form-control"
                    placeholder="photos"
                >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Videos Path</label>
                <input 
                    v-model="config.bot_api_videos_path" 
                    class="form-control"
                    placeholder="videos"
                >
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label>Laravel Storage Path</label>
        <input 
            v-model="config.laravel_storage_path" 
            class="form-control"
            placeholder="storage/app/proyek"
            readonly
        >
        <small class="form-text text-muted">
            Final destination for files in Laravel storage
        </small>
    </div>
    
    <div class="form-check">
        <input 
            v-model="config.auto_cleanup" 
            type="checkbox" 
            class="form-check-input" 
            id="autoCleanup"
        >
        <label class="form-check-label" for="autoCleanup">
            Auto cleanup Bot API files after copying
        </label>
    </div>
    
    <div v-if="config.auto_cleanup" class="form-group mt-2">
        <label>Cleanup After (hours)</label>
        <input 
            v-model="config.cleanup_after_hours" 
            type="number" 
            class="form-control"
            min="1"
            max="168"
        >
        <small class="form-text text-muted">
            Keep files in Bot API path for this duration before cleanup
        </small>
    </div>
</div>
```

## 🔄 File Sync Process

### Webhook Handler Flow
```php
// app/Http/Controllers/TelegramWebhookController.php
public function handleFileUpload($update)
{
    $message = $update['message'];
    $fileData = null;
    
    // 1. Identify file type
    if (isset($message['document'])) {
        $fileData = $message['document'];
        $fileData['type'] = 'document';
    } elseif (isset($message['photo'])) {
        $fileData = end($message['photo']); // Get highest resolution
        $fileData['type'] = 'photo';
    } elseif (isset($message['video'])) {
        $fileData = $message['video'];
        $fileData['type'] = 'video';
    }
    
    if (!$fileData) {
        return $this->sendReply($message['chat']['id'], "❌ No file detected");
    }
    
    // 2. Get file info from Telegram
    $fileInfo = $this->telegramService->getFile($fileData['file_id']);
    $fileData['file_path'] = $fileInfo['file_path'];
    
    // 3. Get active project from session
    $session = BotUserSession::where('telegram_user_id', $message['from']['id'])->first();
    if (!$session || !$session->current_project_id) {
        return $this->sendReply($message['chat']['id'], 
            "❌ Pilih proyek dulu dengan /pilih [project_id]");
    }
    
    // 4. Process file (copy from Bot API path to Laravel storage)
    try {
        $document = $this->fileProcessingService->processUploadedFile(
            $fileData, 
            $session->current_project_id
        );
        
        // 5. Log activity
        BotActivity::create([
            'telegram_user_id' => $message['from']['id'],
            'telegram_username' => $message['from']['username'] ?? null,
            'chat_id' => $message['chat']['id'],
            'message_type' => 'file',
            'file_name' => $document->name,
            'file_size' => $document->file_size,
            'file_path' => $document->file_path,
            'project_id' => $session->current_project_id,
            'status' => 'success'
        ]);
        
        // 6. Send success message
        $project = Project::find($session->current_project_id);
        return $this->sendReply($message['chat']['id'], 
            "✅ File berhasil disimpan!\n\n" .
            "📁 Proyek: {$project->name}\n" .
            "📄 File: {$document->name}\n" .
            "📊 Ukuran: " . $this->formatBytes($document->file_size) . "\n" .
            "📂 Lokasi: {$document->file_path}\n\n" .
            "File dapat diakses melalui web dashboard."
        );
        
    } catch (\Exception $e) {
        // Log error
        BotActivity::create([
            'telegram_user_id' => $message['from']['id'],
            'chat_id' => $message['chat']['id'],
            'message_type' => 'file',
            'file_name' => $fileData['file_name'] ?? 'unknown',
            'status' => 'failed',
            'error_message' => $e->getMessage()
        ]);
        
        return $this->sendReply($message['chat']['id'], 
            "❌ Gagal menyimpan file: " . $e->getMessage());
    }
}
```

## 🧹 Cleanup Job

```php
// app/Jobs/CleanupBotApiFiles.php
class CleanupBotApiFiles extends Job
{
    public function handle()
    {
        $config = BotConfiguration::first();
        
        if (!$config->auto_cleanup) {
            return;
        }
        
        // Get files older than configured hours
        $cutoffTime = now()->subHours($config->cleanup_after_hours);
        
        $activities = BotActivity::where('message_type', 'file')
            ->where('status', 'success')
            ->where('created_at', '<', $cutoffTime)
            ->whereNotNull('telegram_original_path')
            ->get();
        
        foreach ($activities as $activity) {
            $botApiPath = $activity->telegram_original_path;
            
            if (file_exists($botApiPath)) {
                unlink($botApiPath);
                Log::info("Cleaned up bot API file: {$botApiPath}");
            }
            
            // Clear the path from database
            $activity->telegram_original_path = null;
            $activity->save();
        }
    }
}
```

## 📊 Path Monitoring Dashboard

```blade
<!-- Bot Activity - Path Statistics -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Storage Path Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Bot API Path Usage</h6>
                <div class="progress mb-2">
                    <div class="progress-bar" style="width: {{ $botApiUsagePercent }}%">
                        {{ $botApiUsagePercent }}%
                    </div>
                </div>
                <small>{{ $botApiUsedSpace }} / {{ $botApiTotalSpace }}</small>
            </div>
            <div class="col-md-6">
                <h6>Laravel Storage Usage</h6>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" style="width: {{ $laravelUsagePercent }}%">
                        {{ $laravelUsagePercent }}%
                    </div>
                </div>
                <small>{{ $laravelUsedSpace }} / {{ $laravelTotalSpace }}</small>
            </div>
        </div>
        
        <div class="mt-3">
            <h6>Files Pending Cleanup</h6>
            <p>{{ $pendingCleanupCount }} files ({{ $pendingCleanupSize }})</p>
        </div>
    </div>
</div>
```

## 🔑 Key Points

1. **Two separate paths** - Bot API path (temporary) dan Laravel storage (permanent)
2. **Configurable paths** - Admin bisa set path melalui UI
3. **Auto-cleanup option** - Bersihkan file dari Bot API path setelah berhasil copy
4. **Path monitoring** - Dashboard untuk monitor usage kedua path
5. **Error handling** - Handle jika file tidak ditemukan di Bot API path

Dengan sistem ini, flow menjadi:
1. User kirim file via Telegram
2. File tersimpan di Bot API path (sesuai konfigurasi)
3. Laravel copy file dari Bot API path ke storage/app/proyek/{id}/
4. Optional: Cleanup Bot API path setelah beberapa jam
5. File accessible via web dashboard dari Laravel storage