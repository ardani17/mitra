# 📱 Telegram Bot Integration - Detailed Implementation Plan

## 🏗️ Project Structure Integration

Berdasarkan analisis model `Project.php`, sistem sudah memiliki:
- **Project Code**: Field `code` yang unik untuk identifikasi proyek
- **Project Name**: Field `name` untuk pencarian
- **Documents**: Relasi `documents()` untuk file management
- **Folders**: Relasi `folders()` untuk struktur folder
- **Storage Path**: Menggunakan pattern `storage/app/proyek/{project-code}/`

## 📂 Folder Structure untuk Bot Integration

```
storage/app/proyek/{project-code}/
├── dokumen/
│   ├── kontrak/
│   ├── teknis/
│   ├── keuangan/
│   ├── laporan/
│   └── telegram-uploads/    # NEW - khusus untuk upload dari Telegram
├── gambar/
│   └── telegram-uploads/    # NEW
└── video/
    └── telegram-uploads/    # NEW
```

## 🤖 Bot Conversation Flow Detail

### 1. Search Project Flow
```
User: /cari kampung malang
Bot: 🔍 Mencari proyek "kampung malang"...

📋 Hasil pencarian (3 proyek):

1️⃣ KAMPUNG MALANG FASE 1
   📌 ID: 3sbu-km-fase1
   👤 Client: PT. ABC
   📊 Status: In Progress
   💰 Budget: Rp 500.000.000
   
2️⃣ KAMPUNG MALANG FASE 2  
   📌 ID: 3sbu-km-fase2
   👤 Client: PT. ABC
   📊 Status: Planning
   💰 Budget: Rp 750.000.000

3️⃣ RENOVASI KAMPUNG MALANG
   📌 ID: 3mdr-rkm-2024
   👤 Client: Pemkot
   📊 Status: Completed
   💰 Budget: Rp 300.000.000

Gunakan: /pilih [ID] untuk memilih proyek
Contoh: /pilih 3sbu-km-fase1
```

### 2. Select Project Flow
```
User: /pilih 3sbu-km-fase1
Bot: ✅ Proyek dipilih: KAMPUNG MALANG FASE 1

📁 Folder tersedia:
• dokumen/kontrak
• dokumen/teknis  
• dokumen/keuangan
• dokumen/laporan
• gambar
• video

📤 Silakan kirim file atau gunakan perintah:
• /folder [nama] - Buat folder baru
• /list - Lihat file di proyek ini
• /status - Info proyek saat ini
• /cancel - Batalkan pilihan
```

### 3. File Upload Flow
```
User: [Sends Document/Photo]
Bot: 📥 Menerima file: invoice-123.pdf (2.5 MB)

Pilih folder tujuan:
1️⃣ dokumen/kontrak
2️⃣ dokumen/teknis
3️⃣ dokumen/keuangan
4️⃣ dokumen/laporan
5️⃣ dokumen/lainnya
6️⃣ Auto (sistem tentukan)

Balas dengan angka 1-6
```

```
User: 3
Bot: ✅ File berhasil disimpan!

📁 Lokasi: 3sbu-km-fase1/dokumen/keuangan/
📄 File: invoice-123.pdf
📊 Size: 2.5 MB
🕐 Waktu: 31 Aug 2025, 17:45

File dapat diakses melalui web dashboard.
```

## 💾 Database Schema Updates

### Modify existing tables:

```sql
-- Add telegram fields to users table
ALTER TABLE users ADD COLUMN telegram_user_id VARCHAR(50) UNIQUE;
ALTER TABLE users ADD COLUMN telegram_username VARCHAR(100);
ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(50);
ALTER TABLE users ADD COLUMN telegram_notifications BOOLEAN DEFAULT false;

-- Add telegram upload tracking to project_documents
ALTER TABLE project_documents ADD COLUMN upload_source ENUM('web', 'telegram', 'api') DEFAULT 'web';
ALTER TABLE project_documents ADD COLUMN telegram_file_id VARCHAR(255);
ALTER TABLE project_documents ADD COLUMN telegram_message_id VARCHAR(100);
```

### New tables for bot system:

```sql
-- Bot command history
CREATE TABLE bot_command_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    telegram_user_id VARCHAR(50),
    command VARCHAR(50),
    parameters TEXT,
    response TEXT,
    execution_time FLOAT,
    status ENUM('success', 'failed', 'timeout'),
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bot file upload queue
CREATE TABLE bot_upload_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    telegram_user_id VARCHAR(50),
    telegram_file_id VARCHAR(255),
    file_name VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(100),
    project_id BIGINT,
    target_folder VARCHAR(255),
    status ENUM('pending', 'processing', 'completed', 'failed'),
    error_message TEXT,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP,
    processed_at TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);
```

## 🔧 Service Classes Architecture

```php
// app/Services/Telegram/BotManager.php
class BotManager {
    protected $commandHandlers = [];
    protected $sessionManager;
    protected $fileHandler;
    
    public function registerCommands() {
        $this->commandHandlers = [
            '/start' => StartCommand::class,
            '/help' => HelpCommand::class,
            '/cari' => SearchProjectCommand::class,
            '/pilih' => SelectProjectCommand::class,
            '/folder' => CreateFolderCommand::class,
            '/list' => ListFilesCommand::class,
            '/status' => StatusCommand::class,
            '/cancel' => CancelCommand::class,
        ];
    }
}

// app/Services/Telegram/Commands/SearchProjectCommand.php
class SearchProjectCommand extends BaseCommand {
    public function handle($params) {
        $searchTerm = implode(' ', $params);
        
        $projects = Project::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->orWhere('client_name', 'LIKE', "%{$searchTerm}%")
            ->where('status', '!=', 'cancelled')
            ->limit(10)
            ->get();
        
        return $this->formatProjectList($projects);
    }
    
    private function formatProjectList($projects) {
        if ($projects->isEmpty()) {
            return "❌ Tidak ada proyek ditemukan.";
        }
        
        $message = "🔍 Hasil pencarian ({$projects->count()} proyek):\n\n";
        
        foreach ($projects as $index => $project) {
            $emoji = $this->getStatusEmoji($project->status);
            $number = $this->getNumberEmoji($index + 1);
            
            $message .= "{$number} {$project->name}\n";
            $message .= "   📌 ID: {$project->code}\n";
            $message .= "   👤 Client: {$project->client_name}\n";
            $message .= "   {$emoji} Status: {$project->status}\n";
            $message .= "   💰 Budget: Rp " . number_format($project->planned_total_value, 0, ',', '.') . "\n";
            $message .= "\n";
        }
        
        $message .= "Gunakan: /pilih [ID] untuk memilih proyek\n";
        $message .= "Contoh: /pilih {$projects->first()->code}";
        
        return $message;
    }
}

// app/Services/Telegram/FileUploadHandler.php
class FileUploadHandler {
    public function processUpload($fileData, $projectId, $userId) {
        // 1. Download file from Telegram
        $localPath = $this->downloadFromTelegram($fileData['file_id']);
        
        // 2. Determine folder based on file type
        $folder = $this->determineFolder($fileData['mime_type'], $fileData['file_name']);
        
        // 3. Generate storage path
        $project = Project::find($projectId);
        $storagePath = "proyek/{$project->code}/{$folder}/telegram-uploads/";
        
        // 4. Move file to storage
        $fileName = $this->sanitizeFileName($fileData['file_name']);
        $fullPath = $storagePath . $fileName;
        
        Storage::move($localPath, $fullPath);
        
        // 5. Create document record
        $document = ProjectDocument::create([
            'project_id' => $projectId,
            'name' => $fileName,
            'file_path' => $fullPath,
            'file_size' => $fileData['file_size'],
            'mime_type' => $fileData['mime_type'],
            'document_type' => $this->getDocumentType($folder),
            'uploaded_by' => $userId,
            'upload_source' => 'telegram',
            'telegram_file_id' => $fileData['file_id'],
            'description' => 'Uploaded via Telegram Bot'
        ]);
        
        // 6. Log activity
        ProjectActivity::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'activity_type' => 'document_upload',
            'description' => "File '{$fileName}' uploaded via Telegram Bot",
            'changes' => json_encode([
                'source' => 'telegram',
                'file_name' => $fileName,
                'file_size' => $fileData['file_size']
            ])
        ]);
        
        return $document;
    }
}
```

## 🎨 UI Components for Tools Menu

### 1. Sidebar Navigation Component
```blade
<!-- resources/views/tools/partials/sidebar.blade.php -->
<div class="tools-sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-tools"></i> Tools</h3>
    </div>
    
    <nav class="sidebar-nav">
        <a href="{{ route('tools.bot-config') }}" 
           class="nav-item {{ request()->routeIs('tools.bot-config') ? 'active' : '' }}">
            <i class="fas fa-robot"></i>
            <span>Bot Configuration</span>
            @if($botStatus->is_connected)
                <span class="badge badge-success">Connected</span>
            @else
                <span class="badge badge-danger">Disconnected</span>
            @endif
        </a>
        
        <a href="{{ route('tools.file-explorer') }}"
           class="nav-item {{ request()->routeIs('tools.file-explorer') ? 'active' : '' }}">
            <i class="fas fa-folder-open"></i>
            <span>File Explorer</span>
            @if($recentUploads > 0)
                <span class="badge badge-info">{{ $recentUploads }} new</span>
            @endif
        </a>
        
        <a href="{{ route('tools.bot-activity') }}"
           class="nav-item {{ request()->routeIs('tools.bot-activity') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Bot Activity</span>
        </a>
        
        <a href="{{ route('tools.bot-users') }}"
           class="nav-item {{ request()->routeIs('tools.bot-users') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Bot Users</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="bot-stats">
            <div class="stat-item">
                <span class="stat-label">Today's Uploads</span>
                <span class="stat-value">{{ $todayUploads }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Active Sessions</span>
                <span class="stat-value">{{ $activeSessions }}</span>
            </div>
        </div>
    </div>
</div>
```

### 2. Bot Configuration Vue Component
```vue
<!-- resources/js/components/BotConfiguration.vue -->
<template>
    <div class="bot-configuration">
        <div class="config-section">
            <h4>Connection Settings</h4>
            
            <div class="connection-status" :class="statusClass">
                <i :class="statusIcon"></i>
                <span>{{ statusText }}</span>
                <button @click="testConnection" class="btn btn-sm btn-outline-primary">
                    Test Connection
                </button>
            </div>
            
            <form @submit.prevent="saveConfiguration">
                <div class="form-group">
                    <label>Bot Token</label>
                    <div class="input-group">
                        <input 
                            v-model="config.bot_token" 
                            :type="showToken ? 'text' : 'password'"
                            class="form-control"
                            placeholder="Enter bot token from @BotFather"
                        >
                        <div class="input-group-append">
                            <button @click="toggleToken" type="button" class="btn btn-outline-secondary">
                                <i :class="showToken ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Server Host</label>
                            <input v-model="config.server_host" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Server Port</label>
                            <input v-model="config.server_port" type="number" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Webhook URL</label>
                    <div class="input-group">
                        <input :value="webhookUrl" class="form-control" readonly>
                        <div class="input-group-append">
                            <button @click="copyWebhook" type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button @click="setWebhook" type="button" class="btn btn-outline-success">
                                Set Webhook
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Allowed Users</label>
                    <vue-tags-input
                        v-model="tag"
                        :tags="allowedUsers"
                        @tags-changed="updateAllowedUsers"
                        placeholder="Add Telegram user ID"
                    />
                    <small class="form-text text-muted">
                        Leave empty to allow all users. Add Telegram user IDs to restrict access.
                    </small>
                </div>
                
                <div class="form-group">
                    <label>File Size Limit (MB)</label>
                    <input v-model="config.max_file_size_mb" type="number" class="form-control">
                    <small class="form-text text-muted">
                        Local server supports up to 2000 MB (2 GB)
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </form>
        </div>
        
        <div class="config-section mt-4">
            <h4>Quick Actions</h4>
            
            <div class="quick-actions">
                <button @click="sendTestMessage" class="btn btn-info">
                    <i class="fas fa-paper-plane"></i> Send Test Message
                </button>
                
                <button @click="getUpdates" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Get Updates
                </button>
                
                <button @click="clearWebhook" class="btn btn-danger">
                    <i class="fas fa-times"></i> Clear Webhook
                </button>
            </div>
        </div>
        
        <div class="config-section mt-4">
            <h4>Bot Information</h4>
            
            <div v-if="botInfo" class="bot-info">
                <div class="info-item">
                    <label>Bot Username:</label>
                    <span>@{{ botInfo.username }}</span>
                </div>
                <div class="info-item">
                    <label>Bot Name:</label>
                    <span>{{ botInfo.first_name }}</span>
                </div>
                <div class="info-item">
                    <label>Bot ID:</label>
                    <span>{{ botInfo.id }}</span>
                </div>
                <div class="info-item">
                    <label>Can Join Groups:</label>
                    <span>{{ botInfo.can_join_groups ? 'Yes' : 'No' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            config: {
                bot_token: '',
                server_host: 'localhost',
                server_port: 8081,
                use_local_server: true,
                max_file_size_mb: 2000,
                allowed_users: []
            },
            botInfo: null,
            isConnected: false,
            showToken: false,
            tag: '',
            allowedUsers: []
        }
    },
    
    computed: {
        webhookUrl() {
            return `${window.location.origin}/api/telegram/webhook`;
        },
        
        statusClass() {
            return this.isConnected ? 'status-connected' : 'status-disconnected';
        },
        
        statusIcon() {
            return this.isConnected ? 'fas fa-check-circle' : 'fas fa-times-circle';
        },
        
        statusText() {
            return this.isConnected ? 'Bot Connected' : 'Bot Disconnected';
        }
    },
    
    methods: {
        async loadConfiguration() {
            const response = await axios.get('/api/tools/bot-config');
            this.config = response.data.config;
            this.botInfo = response.data.bot_info;
            this.isConnected = response.data.is_connected;
            this.allowedUsers = this.config.allowed_users.map(id => ({ text: id }));
        },
        
        async saveConfiguration() {
            try {
                await axios.post('/api/tools/bot-config', this.config);
                this.$toast.success('Configuration saved successfully');
                await this.loadConfiguration();
            } catch (error) {
                this.$toast.error('Failed to save configuration');
            }
        },
        
        async testConnection() {
            try {
                const response = await axios.post('/api/tools/bot-config/test');
                this.isConnected = response.data.connected;
                this.botInfo = response.data.bot_info;
                
                if (this.isConnected) {
                    this.$toast.success('Connection successful!');
                } else {
                    this.$toast.error('Connection failed!');
                }
            } catch (error) {
                this.$toast.error('Connection test failed');
            }
        },
        
        async setWebhook() {
            try {
                await axios.post('/api/tools/bot-config/webhook');
                this.$toast.success('Webhook set successfully');
            } catch (error) {
                this.$toast.error('Failed to set webhook');
            }
        },
        
        toggleToken() {
            this.showToken = !this.showToken;
        },
        
        copyWebhook() {
            navigator.clipboard.writeText(this.webhookUrl);
            this.$toast.success('Webhook URL copied to clipboard');
        },
        
        updateAllowedUsers(newTags) {
            this.allowedUsers = newTags;
            this.config.allowed_users = newTags.map(tag => tag.text);
        }
    },
    
    mounted() {
        this.loadConfiguration();
    }
}
</script>
```

## 🔐 Security Implementation

### 1. Middleware for Bot Webhook
```php
// app/Http/Middleware/ValidateTelegramWebhook.php
class ValidateTelegramWebhook
{
    public function handle($request, Closure $next)
    {
        // 1. Validate secret token (if configured)
        $secretToken = config('services.telegram.webhook_secret');
        if ($secretToken) {
            $headerToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($headerToken !== $secretToken) {
                Log::warning('Invalid webhook secret token', [
                    'ip' => $request->ip()
                ]);
                abort(403, 'Unauthorized');
            }
        }
        
        // 2. Validate request structure
        if (!$request->has('update_id')) {
            abort(400, 'Invalid request structure');
        }
        
        // 3. Rate limiting per chat
        $chatId = $request->input('message.chat.id');
        if ($chatId) {
            $key = 'telegram_rate_limit:' . $chatId;
            if (RateLimiter::tooManyAttempts($key, 30)) { // 30 requests per minute
                Log::warning('Rate limit exceeded for chat', ['chat_id' => $chatId]);
                return response()->json(['ok' => false, 'error' => 'Rate limit exceeded'], 429);
            }
            RateLimiter::hit($key);
        }
        
        return $next($request);
    }
}
```

### 2. User Authorization
```php
// app/Services/Telegram/Authorization/UserAuthorizer.php
class UserAuthorizer
{
    public function authorize($telegramUserId): bool
    {
        // 1. Check if user is in allowed list
        $allowedUsers = BotConfiguration::first()->allowed_users ?? [];
        if (!empty($allowedUsers) && !in_array($telegramUserId, $allowedUsers)) {
            return false;
        }
        
        // 2. Check if user is linked to system user
        $user = User::where('telegram_user_id', $telegramUserId)->first();
        if (!$user) {
            return false;
        }
        
        // 3. Check if user has permission
        if (!$user->hasPermissionTo('use_telegram_bot')) {
            return false;
        }
        
        return true;
    }
    
    public function getSystemUser($telegramUserId): ?User
    {
        return User::where('telegram_user_id', $telegramUserId)->first();
    }
}
```

## 📊 Monitoring & Analytics

### Bot Activity Dashboard
```php
// app/Http/Controllers/Tools/BotActivityController.php
class BotActivityController extends Controller
{
    public function index()
    {
        $stats = [
            'today_uploads' => BotActivity::whereDate('created_at', today())
                ->where('message_type', 'file')
                ->count(),
            
            'week_uploads' => BotActivity::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->where('message_type', 'file')
                ->count(),
            
            'total_users' => BotUserSession::distinct('telegram_user_id')->count(),
            
            'active_sessions' => BotUserSession::where('last_activity', '>', now()->subMinutes(30))
                ->count(),
            
            'popular_projects' => BotActivity::select('project_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('project_id')
                ->groupBy('project_id')
                ->orderByDesc('count')
                ->limit(5)
                ->with('project')
                ->get(),
            
            'file_types' => BotActivity::select(
                    DB::raw("SUBSTRING_INDEX(file_name, '.', -1) as extension"),
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('file_name')
                ->groupBy('extension')
                ->get(),
            
            'hourly_activity' => BotActivity::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->whereDate('created_at', today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
        ];
        
        return view('tools.bot-activity', compact('stats'));
    }
}
```

## 🚀 Deployment Checklist

### Phase 1: Foundation (Week 1)
- [ ] Create database migrations
- [ ] Setup Tools menu structure
- [ ] Implement bot configuration UI
- [ ] Create webhook endpoint
- [ ] Setup basic command handlers

### Phase 2: Core Features (Week 2)
- [ ] Implement project search
- [ ] Implement file upload handler
- [ ] Create session management
- [ ] Add folder creation feature
- [ ] Implement file listing

### Phase 3: UI & UX (Week 3)
- [ ] Enhanced file explorer with bot integration
- [ ] Bot activity dashboard
- [ ] Real-time notifications
- [ ] User linking interface
- [ ] Error handling & logging

### Phase 4: Advanced & Polish (Week 4)
- [ ] Batch operations
- [ ] Auto-categorization
- [ ] Advanced permissions
- [ ] Performance optimization
- [ ] Documentation & training

## 📝 Testing Scenarios

1. **Search & Select Project**
   - Search with partial name
   - Search with project code
   - Select valid project
   - Handle invalid project ID

2. **File Upload**
   - Upload document < 20MB
   - Upload document > 20MB (test local server)
   - Upload image
   - Upload video
   - Handle unsupported file types

3. **Security**
   - Unauthorized user access
   - Rate limiting
   - Invalid commands
   - SQL injection attempts

4. **Performance**
   - Concurrent uploads
   - Large file handling
   - Multiple active sessions
   - Database query optimization

---

**Ready for Implementation!** 🚀