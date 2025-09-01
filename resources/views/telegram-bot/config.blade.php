<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bot Configuration') }}
            </h2>
            <div class="flex space-x-2">
                @if($config && $config->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                        Inactive
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('telegram-bot.config.save') }}" id="botConfigForm">
                @csrf
                
                <!-- Bot Settings Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fab fa-telegram text-blue-500 mr-2"></i>
                            Bot Settings
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bot_name" class="block text-sm font-medium text-gray-700">Bot Name</label>
                                <input type="text" name="bot_name" id="bot_name" 
                                    value="{{ old('bot_name', $config->bot_name ?? '') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>

                            <div>
                                <label for="bot_username" class="block text-sm font-medium text-gray-700">Bot Username</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        @
                                    </span>
                                    <input type="text" name="bot_username" id="bot_username"
                                        value="{{ old('bot_username', $config->bot_username ?? '') }}"
                                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                                        placeholder="your_bot">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="bot_token" class="block text-sm font-medium text-gray-700">Bot Token</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="password" name="bot_token" id="bot_token"
                                        value="{{ old('bot_token', $config->bot_token ?? '') }}"
                                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                                        required>
                                    <button type="button" onclick="toggleTokenVisibility()" 
                                        class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                                        <i class="fas fa-eye" id="tokenToggleIcon"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Get your bot token from @BotFather on Telegram</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Server Configuration Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-server text-gray-600 mr-2"></i>
                            Server Configuration
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="server_host" class="block text-sm font-medium text-gray-700">Server Host</label>
                                <input type="text" name="server_host" id="server_host"
                                    value="{{ old('server_host', $config->server_host ?? 'localhost') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>

                            <div>
                                <label for="server_port" class="block text-sm font-medium text-gray-700">Server Port</label>
                                <input type="number" name="server_port" id="server_port"
                                    value="{{ old('server_port', $config->server_port ?? 8081) }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required min="1" max="65535">
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="use_local_server" id="use_local_server" value="1"
                                        {{ old('use_local_server', $config->use_local_server ?? true) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="use_local_server" class="ml-2 block text-sm text-gray-900">
                                        Use Local Bot API Server (Supports up to 2GB files)
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="max_file_size_mb" class="block text-sm font-medium text-gray-700">Max File Size (MB)</label>
                                <input type="number" name="max_file_size_mb" id="max_file_size_mb"
                                    value="{{ old('max_file_size_mb', $config->max_file_size_mb ?? 2000) }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required min="1" max="2000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Path Configuration Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-folder text-yellow-500 mr-2"></i>
                            Path Configuration
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="bot_api_base_path" class="block text-sm font-medium text-gray-700">Bot API Base Path</label>
                                <input type="text" name="bot_api_base_path" id="bot_api_base_path"
                                    value="{{ old('bot_api_base_path', $config->bot_api_base_path ?? '/var/lib/telegram-bot-api') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required>
                                <p class="mt-1 text-sm text-gray-500">Base directory where telegram-bot-api stores files</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="bot_api_documents_path" class="block text-sm font-medium text-gray-700">Documents Path</label>
                                    <input type="text" name="bot_api_documents_path" id="bot_api_documents_path"
                                        value="{{ old('bot_api_documents_path', $config->bot_api_documents_path ?? 'documents') }}"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="documents">
                                </div>

                                <div>
                                    <label for="bot_api_photos_path" class="block text-sm font-medium text-gray-700">Photos Path</label>
                                    <input type="text" name="bot_api_photos_path" id="bot_api_photos_path"
                                        value="{{ old('bot_api_photos_path', $config->bot_api_photos_path ?? 'photos') }}"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="photos">
                                </div>

                                <div>
                                    <label for="bot_api_videos_path" class="block text-sm font-medium text-gray-700">Videos Path</label>
                                    <input type="text" name="bot_api_videos_path" id="bot_api_videos_path"
                                        value="{{ old('bot_api_videos_path', $config->bot_api_videos_path ?? 'videos') }}"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="videos">
                                </div>
                            </div>

                            <div>
                                <label for="bot_api_temp_path" class="block text-sm font-medium text-gray-700">Temp Path</label>
                                <input type="text" name="bot_api_temp_path" id="bot_api_temp_path"
                                    value="{{ old('bot_api_temp_path', $config->bot_api_temp_path ?? 'temp') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="temp">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cleanup Settings Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-broom text-purple-500 mr-2"></i>
                            Cleanup Settings
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_cleanup" id="auto_cleanup" value="1"
                                    {{ old('auto_cleanup', $config->auto_cleanup ?? true) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    onchange="toggleCleanupHours()">
                                <label for="auto_cleanup" class="ml-2 block text-sm text-gray-900">
                                    Auto cleanup Bot API files after copying to Laravel storage
                                </label>
                            </div>

                            <div id="cleanupHoursDiv" class="{{ old('auto_cleanup', $config->auto_cleanup ?? true) ? '' : 'hidden' }}">
                                <label for="cleanup_after_hours" class="block text-sm font-medium text-gray-700">Cleanup After (hours)</label>
                                <input type="number" name="cleanup_after_hours" id="cleanup_after_hours"
                                    value="{{ old('cleanup_after_hours', $config->cleanup_after_hours ?? 24) }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md md:w-1/3"
                                    min="1" max="168">
                                <p class="mt-1 text-sm text-gray-500">Keep files in Bot API path for this duration before cleanup</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activation & Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $config->is_active ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Activate Bot
                                </label>
                            </div>

                            <div class="flex space-x-3">
                                <button type="button" onclick="testConnection()" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-plug mr-2"></i>
                                    Test Connection
                                </button>
                                
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-save mr-2"></i>
                                    Save Configuration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Webhook Info Card (if config exists) -->
            @if($config && $webhookInfo)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-link text-green-500 mr-2"></i>
                            Webhook Information
                        </h3>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                @if(isset($webhookInfo['result']['url']))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">URL</dt>
                                        <dd class="mt-1 text-sm text-gray-900 break-all">{{ $webhookInfo['result']['url'] }}</dd>
                                    </div>
                                @endif
                                
                                @if(isset($webhookInfo['result']['has_custom_certificate']))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Custom Certificate</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $webhookInfo['result']['has_custom_certificate'] ? 'Yes' : 'No' }}
                                        </dd>
                                    </div>
                                @endif
                                
                                @if(isset($webhookInfo['result']['pending_update_count']))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Pending Updates</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $webhookInfo['result']['pending_update_count'] }}</dd>
                                    </div>
                                @endif
                                
                                @if(isset($webhookInfo['result']['last_error_date']))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Error</dt>
                                        <dd class="mt-1 text-sm text-red-600">
                                            {{ \Carbon\Carbon::createFromTimestamp($webhookInfo['result']['last_error_date'])->diffForHumans() }}
                                            @if(isset($webhookInfo['result']['last_error_message']))
                                                <br><small>{{ $webhookInfo['result']['last_error_message'] }}</small>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleTokenVisibility() {
            const tokenInput = document.getElementById('bot_token');
            const tokenIcon = document.getElementById('tokenToggleIcon');
            
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                tokenIcon.classList.remove('fa-eye');
                tokenIcon.classList.add('fa-eye-slash');
            } else {
                tokenInput.type = 'password';
                tokenIcon.classList.remove('fa-eye-slash');
                tokenIcon.classList.add('fa-eye');
            }
        }

        function toggleCleanupHours() {
            const checkbox = document.getElementById('auto_cleanup');
            const div = document.getElementById('cleanupHoursDiv');
            
            if (checkbox.checked) {
                div.classList.remove('hidden');
            } else {
                div.classList.add('hidden');
            }
        }

        function testConnection() {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
            button.disabled = true;

            fetch('{{ route("telegram-bot.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Connection successful!\n\nBot: @' + data.bot_info.username + '\nName: ' + data.bot_info.first_name);
                } else {
                    alert('❌ Connection failed!\n\n' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error testing connection: ' + error.message);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    </script>
    @endpush
</x-app-layout>