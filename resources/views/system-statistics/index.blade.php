
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Statistik Sistem') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <span id="last-update" class="text-xs sm:text-sm text-gray-600">
                    <svg class="inline w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Terakhir diperbarui: <span id="update-time">{{ now()->format('H:i:s') }}</span>
                </span>
                <button onclick="exportStatistics()" class="text-xs sm:text-sm bg-green-500 hover:bg-green-600 text-white px-2 sm:px-3 py-1 rounded transition-colors">
                    <svg class="inline w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Alert for mobile users about refresh rate -->
            <div id="mobile-alert" class="lg:hidden bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded-lg mb-4 text-xs sm:text-sm">
                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Data akan diperbarui otomatis setiap <span id="refresh-rate">5</span> detik
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="hidden">
                <div class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                </div>
            </div>

            <!-- Error State -->
            <div id="error-state" class="hidden">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <p class="font-semibold">Error memuat data sistem</p>
                    <p class="text-sm mt-1">Silakan refresh halaman atau hubungi administrator.</p>
                </div>
            </div>

            <!-- Main Content -->
            <div id="main-content">
                <!-- Mobile Collapsible Sections -->
                <div class="lg:hidden space-y-3 mb-6">
                    <!-- System Resources Section -->
                    <div x-data="{ open: true }" class="bg-white rounded-lg shadow">
                        <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                            <span class="font-semibold text-gray-800">Sumber Daya Sistem</span>
                            <svg :class="{'rotate-180': open}" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="px-4 pb-4">
                            <div class="grid grid-cols-1 gap-3">
                                <!-- CPU Card Mobile -->
                                <div id="cpu-card-mobile" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-3">
                                    <!-- Content will be populated by JavaScript -->
                                </div>
                                <!-- Memory Card Mobile -->
                                <div id="memory-card-mobile" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-lg p-3">
                                    <!-- Content will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Section -->
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                        <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                            <span class="font-semibold text-gray-800">Penyimpanan</span>
                            <svg :class="{'rotate-180': open}" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="px-4 pb-4">
                            <div id="disk-cards-mobile" class="space-y-3">
                                <!-- Disk cards will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Application Section -->
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                        <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                            <span class="font-semibold text-gray-800">Aplikasi & Database</span>
                            <svg :class="{'rotate-180': open}" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="px-4 pb-4">
                            <div class="grid grid-cols-1 gap-3">
                                <!-- PHP Memory Card Mobile -->
                                <div id="php-memory-card-mobile" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-3">
                                    <!-- Content will be populated by JavaScript -->
                                </div>
                                <!-- Database Card Mobile -->
                                <div id="database-card-mobile" class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3">
                                    <!-- Content will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Grid Layout -->
                <div class="hidden lg:grid lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
                    <!-- CPU Usage Card -->
                    <div id="cpu-card" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>

                    <!-- Memory Usage Card -->
                    <div id="memory-card" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>

                    <!-- PHP Memory Card -->
                    <div id="php-memory-card" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>

                    <!-- Database Card -->
                    <div id="database-card" class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>

                    <!-- Disk Usage Cards (Dynamic) -->
                    <div id="disk-cards" class="contents">
                        <!-- Disk cards will be populated by JavaScript -->
                    </div>

                    <!-- Cache Stats Card -->
                    <div id="cache-card" class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>

                    <!-- System Uptime Card -->
                    <div id="uptime-card" class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>

                <!-- System Information Panel -->
                <div class="mt-6 bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Sistem</h3>
                    <div id="system-info" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <!-- System info will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Real-time Updates -->
    <script>
        // Global variables
        let metricsInterval;
        let refreshRate = 5000; // Default 5 seconds
        let isUpdating = false;

        // Detect if mobile and adjust refresh rate
        function isMobile() {
            return window.innerWidth < 768;
        }

        // Adjust refresh rate based on device and connection
        function getOptimalRefreshRate() {
            if (isMobile()) {
                const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                if (connection) {
                    if (connection.effectiveType === '4g') {
                        return 5000; // 5 seconds for good connection
                    } else if (connection.effectiveType === '3g') {
                        return 10000; // 10 seconds for moderate connection
                    } else {
                        return 15000; // 15 seconds for slow connection
                    }
                }
                return 10000; // Default 10 seconds for mobile
            }
            return 5000; // 5 seconds for desktop
        }

        // Load system metrics
        async function loadSystemMetrics() {
            if (isUpdating) return; // Prevent multiple simultaneous updates
            isUpdating = true;

            try {
                const response = await fetch('/api/system-statistics/metrics');
                const result = await response.json();
                
                if (result.success) {
                    updateMetricsDisplay(result.data);
                    document.getElementById('error-state').classList.add('hidden');
                    document.getElementById('main-content').classList.remove('hidden');
                    
                    // Update last update time
                    document.getElementById('update-time').textContent = new Date().toLocaleTimeString('id-ID');
                } else {
                    showError();
                }
            } catch (error) {
                console.error('Error loading metrics:', error);
                showError();
            } finally {
                isUpdating = false;
            }
        }

        // Update metrics display
        function updateMetricsDisplay(data) {
            // Update CPU Card
            updateCpuCard(data.cpu);
            
            // Update Memory Card
            updateMemoryCard(data.memory);
            
            // Update PHP Memory Card
            updatePhpMemoryCard(data.php_memory);
            
            // Update Database Card
            updateDatabaseCard(data.database);
            
            // Update Disk Cards
            updateDiskCards(data.disk);
            
            // Update Cache Card
            updateCacheCard(data.cache);
            
            // Update Uptime Card
            updateUptimeCard(data.uptime);
            
            // Update System Info
            updateSystemInfo(data.system_info);
        }

        // Update CPU Card
        function updateCpuCard(cpu) {
            const statusColor = getStatusBadgeColor(cpu.status);
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">CPU Usage</h3>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${cpu.usage}%</p>
                        <div class="w-full bg-blue-400 rounded-full h-1.5 sm:h-2 mt-2">
                            <div class="bg-white h-1.5 sm:h-2 rounded-full transition-all duration-500" style="width: ${cpu.usage}%"></div>
                        </div>
                        <p class="text-xs sm:text-sm opacity-75 mt-1">${cpu.cores} cores, ${cpu.threads} threads</p>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        💻
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-200 text-${statusColor}-800">
                        ${cpu.status}
                    </span>
                </div>
            `;
            
            // Update desktop card
            const desktopCard = document.getElementById('cpu-card');
            if (desktopCard) desktopCard.innerHTML = content;
            
            // Update mobile card
            const mobileCard = document.getElementById('cpu-card-mobile');
            if (mobileCard) mobileCard.innerHTML = content;
        }

        // Update Memory Card
        function updateMemoryCard(memory) {
            const statusColor = getStatusBadgeColor(memory.status);
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">RAM Usage</h3>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${memory.percentage}%</p>
                        <div class="w-full bg-emerald-400 rounded-full h-1.5 sm:h-2 mt-2">
                            <div class="bg-white h-1.5 sm:h-2 rounded-full transition-all duration-500" style="width: ${memory.percentage}%"></div>
                        </div>
                        <p class="text-xs sm:text-sm opacity-75 mt-1">${memory.used_formatted} / ${memory.total_formatted}</p>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        🧠
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-200 text-${statusColor}-800">
                        ${memory.status}
                    </span>
                </div>
            `;
            
            // Update desktop card
            const desktopCard = document.getElementById('memory-card');
            if (desktopCard) desktopCard.innerHTML = content;
            
            // Update mobile card
            const mobileCard = document.getElementById('memory-card-mobile');
            if (mobileCard) mobileCard.innerHTML = content;
        }

        // Update PHP Memory Card
        function updatePhpMemoryCard(phpMemory) {
            const statusColor = getStatusBadgeColor(phpMemory.status);
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">PHP Memory</h3>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${phpMemory.percentage}%</p>
                        <div class="w-full bg-purple-400 rounded-full h-1.5 sm:h-2 mt-2">
                            <div class="bg-white h-1.5 sm:h-2 rounded-full transition-all duration-500" style="width: ${phpMemory.percentage}%"></div>
                        </div>
                        <p class="text-xs sm:text-sm opacity-75 mt-1">${phpMemory.current_formatted} / ${phpMemory.limit_formatted}</p>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        🐘
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-200 text-${statusColor}-800">
                        ${phpMemory.status}
                    </span>
                </div>
            `;
            
            // Update desktop card
            const desktopCard = document.getElementById('php-memory-card');
            if (desktopCard) desktopCard.innerHTML = content;
            
            // Update mobile card
            const mobileCard = document.getElementById('php-memory-card-mobile');
            if (mobileCard) mobileCard.innerHTML = content;
        }

        // Update Database Card
        function updateDatabaseCard(database) {
            const statusColor = getStatusBadgeColor(database.status);
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">Database</h3>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${database.size_formatted}</p>
                        <div class="text-xs sm:text-sm opacity-75 mt-2">
                            <p>${database.tables} tables</p>
                            <p>${database.active_connections} / ${database.max_connections} connections</p>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        🗄️
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-200 text-${statusColor}-800">
                        ${database.type}
                    </span>
                </div>
            `;
            
            // Update desktop card
            const desktopCard = document.getElementById('database-card');
            if (desktopCard) desktopCard.innerHTML = content;
            
            // Update mobile card
            const mobileCard = document.getElementById('database-card-mobile');
            if (mobileCard) mobileCard.innerHTML = content;
        }

        // Update Disk Cards
        function updateDiskCards(disks) {
            // Desktop cards
            const desktopContainer = document.getElementById('disk-cards');
            if (desktopContainer) {
                desktopContainer.innerHTML = '';
                disks.forEach((disk, index) => {
                    const statusColor = getStatusBadgeColor(disk.status);
                    const gradientColors = ['from-red-500 to-red-600', 'from-orange-500 to-orange-600', 'from-teal-500 to-teal-600'];
                    const gradient = gradientColors[index % gradientColors.length];
                    
                    const card = document.createElement('div');
                    card.className = `bg-gradient-to-br ${gradient} text-white rounded-xl p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300`;
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">Disk ${disk.mount}</h3>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${disk.percentage}%</p>
                                <div class="w-full bg-gray-400 rounded-full h-1.5 sm:h-2 mt-2">
                                    <div class="bg-white h-1.5 sm:h-2 rounded-full transition-all duration-500" style="width: ${disk.percentage}%"></div>
                                </div>
                                <p class="text-xs sm:text-sm opacity-75 mt-1">${disk.used_formatted} / ${disk.total_formatted}</p>
                            </div>
                            <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                                💾
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-200 text-${statusColor}-800">
                                ${disk.status}
                            </span>
                        </div>
                    `;
                    desktopContainer.appendChild(card);
                });
            }
            
            // Mobile cards
            const mobileContainer = document.getElementById('disk-cards-mobile');
            if (mobileContainer) {
                mobileContainer.innerHTML = '';
                disks.forEach((disk, index) => {
                    const statusColor = getStatusBadgeColor(disk.status);
                    const gradientColors = ['from-red-500 to-red-600', 'from-orange-500 to-orange-600', 'from-teal-500 to-teal-600'];
                    const gradient = gradientColors[index % gradientColors.length];
                    
                    const card = document.createElement('div');
                    card.className = `bg-gradient-to-br ${gradient} text-white rounded-lg p-3`;
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xs font-semibold opacity-90 truncate">Disk ${disk.mount}</h3>
                                <p class="text-xl font-bold">${disk.percentage}%</p>
                                <div class="w-full bg-gray-400 rounded-full h-1.5 mt-2">
                                    <div class="bg-white h-1.5 rounded-full transition-all duration-500" style="width: ${disk.percentage}%"></div>
                                </div>
                                <p class="text-xs opacity-75 mt-1">${disk.used_formatted} / ${disk.total_formatted}</p>
                            </div>
                            <div class="text-xl opacity-75 ml-2">
                                💾
                            </div>
                        </div>
                    `;
                    mobileContainer.appendChild(card);
                });
            }
        }

        // Update Cache Card
        function updateCacheCard(cache) {
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">Cache</h3>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold">${cache.driver}</p>
                        <div class="text-xs sm:text-sm opacity-75 mt-2">
                            <p>Status: ${cache.status}</p>
                            ${cache.size_formatted ? `<p>Size: ${cache.size_formatted}</p>` : ''}
                            ${cache.files ? `<p>Files: ${cache.files}</p>` : ''}
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        ⚡
                    </div>
                </div>
            `;
            
            const card = document.getElementById('cache-card');
            if (card) card.innerHTML = content;
        }

        // Update Uptime Card
        function updateUptimeCard(uptime) {
            const content = `
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">System Uptime</h3>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold">${uptime.formatted || 'N/A'}</p>
                        <div class="text-xs sm:text-sm opacity-75 mt-2">
                            <p>Boot: ${uptime.boot_time || 'N/A'}</p>
                            <p>App: ${uptime.app_uptime || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                        ⏱️
                    </div>
                </div>
            `;
            
            const card = document.getElementById('uptime-card');
            if (card) card.innerHTML = content;
        }

        // Update System Info
        function updateSystemInfo(info) {
            const container = document.getElementById('system-info');
            if (container) {
                container.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Operating System</p>
                            <p class="text-sm font-medium text-gray-900">${info.os}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">PHP Version</p>
                            <p class="text-sm font-medium text-gray-900">${info.php_version}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Laravel Version</p>
                            <p class="text-sm font-medium text-gray-900">${info.laravel_version}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Server Software</p>
                            <p class="text-sm font-medium text-gray-900">${info.server_software}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Timezone</p>
                            <p class="text-sm font-medium text-gray-900">${info.timezone}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Hostname</p>
                            <p class="text-sm font-medium text-gray-900">${info.hostname}</p>
                        </div>
                    </div>
                `;
            }
        }

        // Get status badge color
        function getStatusBadgeColor(status) {
            switch (status) {
                case 'critical':
                    return 'red';
                case 'warning':
                    return 'yellow';
                case 'good':
                default:
                    return 'green';
            }
        }

        // Show error state
        function showError() {
            document.getElementById('error-state').classList.remove('hidden');
            document.getElementById('main-content').classList.add('hidden');
        }

        // Start metrics refresh
        function startMetricsRefresh() {
            refreshRate = getOptimalRefreshRate();
            document.getElementById('refresh-rate').textContent = Math.round(refreshRate / 1000);
            
            loadSystemMetrics(); // Initial load
            metricsInterval = setInterval(loadSystemMetrics, refreshRate);
            
            // Adjust refresh rate when screen size changes
            window.addEventListener('resize', () => {
                const newRate = getOptimalRefreshRate();
                if (newRate !== refreshRate) {
                    refreshRate = newRate;
                    document.getElementById('refresh-rate').textContent = Math.round(refreshRate / 1000);
                    stopMetricsRefresh();
                    startMetricsRefresh();
                }
            });
        }

        // Stop metrics refresh
        function stopMetricsRefresh() {
            if (metricsInterval) {
                clearInterval(metricsInterval);
            }
        }

        // Export statistics
        function exportStatistics() {
            window.location.href = '/system-statistics/export';
        }

        // Visibility API to pause updates when tab is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopMetricsRefresh();
            } else {
                startMetricsRefresh();
            }
        });

        // Start on page load
        document.addEventListener('DOMContentLoaded', startMetricsRefresh);

        // Stop when leaving page
        window.addEventListener('beforeunload', stopMetricsRefresh);

        // Add smooth number animation
        function animateValue(element, start, end, duration = 500) {
            const startNum = parseFloat(start) || 0;
            const endNum = parseFloat(end) || 0;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = startNum + (endNum - startNum) * progress;
                element.textContent = current.toFixed(1);
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }

        // Add pull-to-refresh for mobile
        let touchStartY = 0;
        let touchEndY = 0;
        
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.changedTouches[0].screenY;
        });
        
        document.addEventListener('touchend', (e) => {
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        });
        
        function handleSwipe() {
            if (touchEndY > touchStartY + 100 && window.scrollY === 0) {
                // Pull down to refresh
                loadSystemMetrics();
            }
        }
    </script>
</x-app-layout>