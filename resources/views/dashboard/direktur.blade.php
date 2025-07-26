<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Direktur') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-4 sm:p-6 mb-6 sm:mb-8 text-white mx-4 sm:mx-0">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-sm sm:text-base text-blue-100">Overview lengkap perusahaan dan performa proyek</p>
            </div>

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Total Proyek</h3>
                            <p class="text-2xl sm:text-3xl font-bold">{{ $totalProjects }}</p>
                            <p class="text-xs sm:text-sm opacity-75">{{ $activeProjects }} aktif</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            📊
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Total Pendapatan</h3>
                            <p class="text-lg sm:text-2xl font-bold truncate">{{ \App\Helpers\FormatHelper::formatRupiah($totalRevenue) }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Pendapatan terkonfirmasi</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            💰
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Total Pengeluaran</h3>
                            <p class="text-lg sm:text-2xl font-bold truncate">{{ \App\Helpers\FormatHelper::formatRupiah($totalExpenses) }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Pengeluaran disetujui</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            💸
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 to-{{ $netProfit >= 0 ? 'green' : 'red' }}-600 text-white rounded-xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Laba Bersih</h3>
                            <p class="text-lg sm:text-2xl font-bold truncate">{{ \App\Helpers\FormatHelper::formatRupiah($netProfit) }}</p>
                            <p class="text-xs sm:text-sm opacity-75">
                                @php
                                    $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
                                @endphp
                                Margin: {{ number_format($profitMargin, 1) }}%
                            </p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            {{ $netProfit >= 0 ? '📈' : '📉' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analisis Tipe Proyek dengan Filter Advanced -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 mx-4 sm:mx-0">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Analisis Tipe Proyek</h3>
                        <p class="text-sm text-gray-600 mt-1">Distribusi dan performa proyek berdasarkan tipe</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button id="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded border border-gray-300 hover:border-gray-400 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset
                        </button>
                        <button id="exportChart" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                        </button>
                        <button id="fullscreenChart" class="hidden lg:block bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            Fullscreen
                        </button>
                    </div>
                </div>

                <!-- Advanced Filter Panel -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <!-- Mobile: Collapsible Filter -->
                    <div class="md:hidden mb-4">
                        <button id="toggleMobileFilters" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg flex items-center justify-between">
                            <span>Filter Proyek</span>
                            <svg id="filterToggleIcon" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="filterContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 md:block hidden md:grid">
                        
                        <!-- Periode Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Periode</label>
                            <select id="periodFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all" selected>Semua Periode</option>
                                <option value="today">Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="quarter">3 Bulan</option>
                                <option value="semester">6 Bulan</option>
                                <option value="year">1 Tahun</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Status Proyek</label>
                            <select id="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">Semua Status</option>
                                <option value="planning">Perencanaan</option>
                                <option value="in_progress">Sedang Berjalan</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Nilai Proyek Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Nilai Proyek</label>
                            <select id="valueRangeFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">Semua Nilai</option>
                                <option value="small">< 100 Juta</option>
                                <option value="medium">100 Juta - 1 Miliar</option>
                                <option value="large">> 1 Miliar</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <!-- Lokasi Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                            <select id="locationFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">Semua Lokasi</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>

                        <!-- Client Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Client</label>
                            <select id="clientFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">Semua Client</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>

                        <!-- Apply Button -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Aksi</label>
                            <button id="applyFilters" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm font-medium">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>

                    <!-- Custom Date Range (Hidden by default) -->
                    <div id="customDateRange" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" id="startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>

                    <!-- Custom Value Range (Hidden by default) -->
                    <div id="customValueRange" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Minimum (Rp)</label>
                            <input type="number" id="minValue" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Maksimum (Rp)</label>
                            <input type="number" id="maxValue" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Tidak terbatas">
                        </div>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-lg font-semibold text-gray-900">Distribusi Tipe Proyek</h4>
                        <div class="text-sm text-gray-500" id="chartSubtitle">
                            Semua periode
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="chartLoading" class="text-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-2 text-gray-600 text-sm">Memuat data...</p>
                    </div>

                    <!-- Chart -->
                    <div id="chartContainer" class="relative h-96" style="display: none;">
                        <canvas id="projectTypesChart"></canvas>
                    </div>

                    <!-- No Data State -->
                    <div id="noDataState" class="text-center py-12" style="display: none;">
                        <div class="text-gray-400 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-600">Tidak ada data untuk filter yang dipilih</p>
                        <button onclick="resetAllFilters()" class="mt-2 text-blue-500 hover:text-blue-600 text-sm">
                            Reset filter untuk melihat semua data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts dan Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Proyek berdasarkan Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Proyek berdasarkan Status</h3>
                        <div class="space-y-2">
                            @foreach($projectsByStatus as $status)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $status->status) }}</span>
                                    <span class="font-semibold">{{ $status->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Proyek berdasarkan Tipe -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Proyek berdasarkan Tipe</h3>
                        <div class="space-y-2">
                            @foreach($projectsByType as $type)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $type->type) }}</span>
                                    <span class="font-semibold">{{ $type->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peringatan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Persetujuan Tertunda</h3>
                        <div class="flex justify-between items-center">
                            <span>Pengeluaran Tertunda</span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $pendingExpenses }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span>Invoice Terlambat</span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $overdueInvoices }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Aktivitas Terbaru</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($recentActivities as $activity)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $activity->user_name }}</span>
                                    <span class="text-gray-600">{{ $activity->description }}</span>
                                    <div class="text-xs text-gray-500">{{ $activity->project_name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('projects.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Proyek
                        </a>
                        <a href="{{ route('companies.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Perusahaan
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Pengeluaran
                        </a>
                        <a href="{{ route('billings.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Penagihan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Global variables
        let projectTypesChart = null;
        let currentFilters = {
            period: 'all',
            status: 'all',
            valueRange: 'all',
            location: 'all',
            client: 'all',
            startDate: null,
            endDate: null,
            minValue: null,
            maxValue: null
        };

        // Color palettes untuk charts
        const colorPalettes = {
            primary: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'],
            secondary: ['#DBEAFE', '#D1FAE5', '#FEF3C7', '#FEE2E2', '#EDE9FE', '#CFFAFE', '#ECFCCB', '#FED7AA']
        };

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
            loadLocationOptions();
            loadClientOptions();
            loadProjectTypesData();
            
            // Event listeners untuk filter
            document.getElementById('periodFilter').addEventListener('change', handlePeriodChange);
            document.getElementById('valueRangeFilter').addEventListener('change', handleValueRangeChange);
            document.getElementById('applyFilters').addEventListener('click', applyFilters);
            document.getElementById('resetFilters').addEventListener('click', resetAllFilters);
            document.getElementById('exportChart').addEventListener('click', exportChart);
            
            // Mobile filter toggle
            const toggleButton = document.getElementById('toggleMobileFilters');
            const filterContent = document.getElementById('filterContent');
            const filterIcon = document.getElementById('filterToggleIcon');
            
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const isHidden = filterContent.classList.contains('hidden');
                    
                    if (isHidden) {
                        filterContent.classList.remove('hidden');
                        filterIcon.style.transform = 'rotate(180deg)';
                    } else {
                        filterContent.classList.add('hidden');
                        filterIcon.style.transform = 'rotate(0deg)';
                    }
                });
            }
        });

        // Initialize filter values
        function initializeFilters() {
            // Set default dates
            const today = new Date();
            const oneYearAgo = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
            
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
            document.getElementById('startDate').value = oneYearAgo.toISOString().split('T')[0];
        }

        // Handle period filter change
        function handlePeriodChange() {
            const period = document.getElementById('periodFilter').value;
            const customDateRange = document.getElementById('customDateRange');
            
            if (period === 'custom') {
                customDateRange.style.display = 'grid';
            } else {
                customDateRange.style.display = 'none';
                updateDateRangeFromPeriod(period);
            }
        }

        // Handle value range filter change
        function handleValueRangeChange() {
            const valueRange = document.getElementById('valueRangeFilter').value;
            const customValueRange = document.getElementById('customValueRange');
            
            if (valueRange === 'custom') {
                customValueRange.style.display = 'grid';
            } else {
                customValueRange.style.display = 'none';
            }
        }

        // Update date range based on period selection
        function updateDateRangeFromPeriod(period) {
            const today = new Date();
            let startDate = new Date();
            
            switch(period) {
                case 'today':
                    startDate = new Date(today);
                    break;
                case 'week':
                    startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    break;
                case 'month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
                case 'quarter':
                    startDate = new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000);
                    break;
                case 'semester':
                    startDate = new Date(today.getTime() - 180 * 24 * 60 * 60 * 1000);
                    break;
                case 'year':
                    startDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
                    break;
                case 'all':
                    startDate = null;
                    break;
            }
            
            if (startDate) {
                document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
                document.getElementById('endDate').value = today.toISOString().split('T')[0];
            }
        }

        // Load location options
        async function loadLocationOptions() {
            try {
                const response = await fetch('/api/dashboard/locations');
                const locations = await response.json();
                
                const locationSelect = document.getElementById('locationFilter');
                locationSelect.innerHTML = '<option value="all">Semua Lokasi</option>';
                
                locations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location;
                    option.textContent = location;
                    locationSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading locations:', error);
            }
        }

        // Load client options
        async function loadClientOptions() {
            try {
                const response = await fetch('/api/dashboard/clients');
                const clients = await response.json();
                
                const clientSelect = document.getElementById('clientFilter');
                clientSelect.innerHTML = '<option value="all">Semua Client</option>';
                
                clients.forEach(client => {
                    const option = document.createElement('option');
                    option.value = client;
                    option.textContent = client;
                    clientSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading clients:', error);
            }
        }

        // Apply filters and reload data
        function applyFilters() {
            // Collect filter values
            currentFilters.period = document.getElementById('periodFilter').value;
            currentFilters.status = document.getElementById('statusFilter').value;
            currentFilters.valueRange = document.getElementById('valueRangeFilter').value;
            currentFilters.location = document.getElementById('locationFilter').value;
            currentFilters.client = document.getElementById('clientFilter').value;
            
            // Custom date range
            if (currentFilters.period === 'custom') {
                currentFilters.startDate = document.getElementById('startDate').value;
                currentFilters.endDate = document.getElementById('endDate').value;
            }
            
            // Custom value range
            if (currentFilters.valueRange === 'custom') {
                currentFilters.minValue = document.getElementById('minValue').value;
                currentFilters.maxValue = document.getElementById('maxValue').value;
            }
            
            // Update chart subtitle
            updateChartSubtitle();
            
            // Load data with filters
            loadProjectTypesData();
        }

        // Reset all filters
        function resetAllFilters() {
            document.getElementById('periodFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('valueRangeFilter').value = 'all';
            document.getElementById('locationFilter').value = 'all';
            document.getElementById('clientFilter').value = 'all';
            
            document.getElementById('customDateRange').style.display = 'none';
            document.getElementById('customValueRange').style.display = 'none';
            
            // Reset to default values
            currentFilters = {
                period: 'all',
                status: 'all',
                valueRange: 'all',
                location: 'all',
                client: 'all',
                startDate: null,
                endDate: null,
                minValue: null,
                maxValue: null
            };
            
            updateChartSubtitle();
            loadProjectTypesData();
        }

        // Update chart subtitle based on filters
        function updateChartSubtitle() {
            const period = document.getElementById('periodFilter').value;
            const status = document.getElementById('statusFilter').value;
            const location = document.getElementById('locationFilter').value;
            
            let subtitle = '';
            
            // Period
            switch(period) {
                case 'today': subtitle += 'Hari ini'; break;
                case 'week': subtitle += 'Minggu ini'; break;
                case 'month': subtitle += 'Bulan ini'; break;
                case 'quarter': subtitle += '3 bulan terakhir'; break;
                case 'semester': subtitle += '6 bulan terakhir'; break;
                case 'year': subtitle += '1 tahun terakhir'; break;
                case 'custom': subtitle += 'Periode custom'; break;
                default: subtitle += 'Semua periode'; break;
            }
            
            // Status
            if (status !== 'all') {
                subtitle += ` • Status: ${status.replace('_', ' ')}`;
            }
            
            // Location
            if (location !== 'all') {
                subtitle += ` • Lokasi: ${location}`;
            }
            
            document.getElementById('chartSubtitle').textContent = subtitle;
        }

        // Load project types data with filters
        async function loadProjectTypesData() {
            showChartLoading();
            
            try {
                // Build query parameters
                const params = new URLSearchParams();
                
                Object.keys(currentFilters).forEach(key => {
                    if (currentFilters[key] && currentFilters[key] !== 'all') {
                        params.append(key, currentFilters[key]);
                    }
                });
                
                const response = await fetch(`/api/dashboard/project-types?${params.toString()}`);
                const data = await response.json();
                
                if (data.data && data.data.length > 0) {
                    createProjectTypesChart(data.data);
                    showChart();
                } else {
                    showNoData();
                }
                
            } catch (error) {
                console.error('Error loading project types data:', error);
                showChartError();
            }
        }

        // Show chart loading state
        function showChartLoading() {
            document.getElementById('chartLoading').style.display = 'block';
            document.getElementById('chartContainer').style.display = 'none';
            document.getElementById('noDataState').style.display = 'none';
        }

        // Show chart
        function showChart() {
            document.getElementById('chartLoading').style.display = 'none';
            document.getElementById('chartContainer').style.display = 'block';
            document.getElementById('noDataState').style.display = 'none';
        }

        // Show no data state
        function showNoData() {
            document.getElementById('chartLoading').style.display = 'none';
            document.getElementById('chartContainer').style.display = 'none';
            document.getElementById('noDataState').style.display = 'block';
        }

        // Create project types pie chart with labels inside
        function createProjectTypesChart(data) {
            const ctx = document.getElementById('projectTypesChart').getContext('2d');
            
            // Destroy existing chart
            if (projectTypesChart) {
                projectTypesChart.destroy();
            }
            
            const labels = data.map(item => item.label);
            const values = data.map(item => item.count);
            const colors = colorPalettes.primary.slice(0, data.length);
            const totalCount = values.reduce((a, b) => a + b, 0);
            
            projectTypesChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverBorderWidth: 4,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#ffffff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const item = data[context.dataIndex];
                                    const percentage = ((item.count / totalCount) * 100).toFixed(1);
                                    return [
                                        `${item.label}: ${item.count} proyek (${percentage}%)`,
                                        `Total Nilai: ${item.formatted_total_value}`,
                                        `Rata-rata: ${item.formatted_avg_value}`
                                    ];
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000,
                        onComplete: function() {
                            // Draw labels inside pie slices after animation completes
                            drawLabelsInsidePie(ctx, data, totalCount);
                        }
                    },
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    }
                }
            });
        }

        // Function to draw labels inside pie chart (desktop only)
        function drawLabelsInsidePie(ctx, data, totalCount) {
            const chart = projectTypesChart;
            const meta = chart.getDatasetMeta(0);
            
            // Only draw labels on desktop (screen width >= 768px)
            const isMobile = window.innerWidth < 768;
            if (isMobile) {
                return; // Skip drawing labels on mobile
            }
            
            const fontSize = 12;
            const lineHeight = 16;
            const strokeWidth = 2;
            
            ctx.save();
            ctx.font = `bold ${fontSize}px Arial`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            meta.data.forEach((element, index) => {
                const item = data[index];
                const percentage = ((item.count / totalCount) * 100).toFixed(1);
                
                // Calculate position for label (center of slice)
                const angle = element.startAngle + (element.endAngle - element.startAngle) / 2;
                const radius = element.outerRadius * 0.7; // 70% of radius
                const x = element.x + Math.cos(angle) * radius;
                const y = element.y + Math.sin(angle) * radius;
                
                // Only draw label if slice is large enough (5% threshold for desktop)
                if (percentage > 5) {
                    ctx.fillStyle = '#ffffff';
                    ctx.strokeStyle = '#000000';
                    ctx.lineWidth = strokeWidth;
                    
                    // Full format for desktop
                    const lines = [
                        item.label,
                        `${item.count} (${percentage}%)`,
                        item.formatted_total_value
                    ];
                    
                    lines.forEach((line, lineIndex) => {
                        const lineY = y + (lineIndex - (lines.length - 1) / 2) * lineHeight;
                        ctx.strokeText(line, x, lineY);
                        ctx.fillText(line, x, lineY);
                    });
                }
            });
            
            ctx.restore();
        }

        // Export chart functionality
        function exportChart() {
            if (projectTypesChart) {
                const url = projectTypesChart.toBase64Image();
                const link = document.createElement('a');
                link.download = 'analisis-tipe-proyek.png';
                link.href = url;
                link.click();
            }
        }
    </script>
</x-app-layout>
