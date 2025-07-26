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

            <!-- Filter Panel untuk Charts -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 mx-4 sm:mx-0">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Analisis Proyek</h3>
                        <p class="text-sm text-gray-600 mt-1">Filter dan analisis data proyek berdasarkan berbagai kriteria</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button id="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded border border-gray-300 hover:border-gray-400 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Advanced Filter Panel -->
                <div class="bg-gray-50 rounded-lg p-4">
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
            </div>

            <!-- Charts Container - Side by Side -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 mx-4 sm:mx-0">
                <!-- Analisis Tipe Proyek -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Analisis Tipe Proyek</h3>
                            <p class="text-sm text-gray-600 mt-1">Distribusi dan performa proyek berdasarkan tipe</p>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-base font-semibold text-gray-900">Distribusi Tipe Proyek</h4>
                            <div class="text-xs text-gray-500" id="chartSubtitle">
                                Semua periode
                            </div>
                        </div>
                        
                        <!-- Loading State -->
                        <div id="chartLoading" class="text-center py-8">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                            <p class="mt-2 text-gray-600 text-xs">Memuat data...</p>
                        </div>

                        <!-- Chart -->
                        <div id="chartContainer" class="relative" style="display: none;">
                            <div class="flex flex-col items-center gap-4">
                                <!-- Chart Canvas -->
                                <div class="w-full h-64">
                                    <canvas id="projectTypesChart"></canvas>
                                </div>
                                
                                <!-- Legend -->
                                <div class="w-full">
                                    <div id="chartLegend" class="grid grid-cols-1 gap-1 text-xs">
                                        <!-- Legend items will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Data State -->
                        <div id="noDataState" class="text-center py-8" style="display: none;">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Tidak ada data untuk filter yang dipilih</p>
                            <button onclick="resetAllFilters()" class="mt-2 text-blue-500 hover:text-blue-600 text-xs">
                                Reset filter untuk melihat semua data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Analisis Status Tagihan -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Analisis Status Tagihan</h3>
                            <p class="text-sm text-gray-600 mt-1">Distribusi proyek berdasarkan status tagihan dan pembayaran</p>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-base font-semibold text-gray-900">Status Tagihan Proyek</h4>
                            <div class="text-xs text-gray-500" id="billingChartSubtitle">
                                Semua periode
                            </div>
                        </div>
                        
                        <!-- Loading State -->
                        <div id="billingChartLoading" class="text-center py-8">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto"></div>
                            <p class="mt-2 text-gray-600 text-xs">Memuat data tagihan...</p>
                        </div>

                        <!-- Chart -->
                        <div id="billingChartContainer" class="relative" style="display: none;">
                            <div class="flex flex-col items-center gap-4">
                                <!-- Chart Canvas -->
                                <div class="w-full h-64">
                                    <canvas id="billingStatusChart"></canvas>
                                </div>
                                
                                <!-- Legend -->
                                <div class="w-full">
                                    <div id="billingChartLegend" class="grid grid-cols-1 gap-1 text-xs">
                                        <!-- Legend items will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Data State -->
                        <div id="billingNoDataState" class="text-center py-8" style="display: none;">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Tidak ada data untuk filter yang dipilih</p>
                            <button onclick="resetAllFilters()" class="mt-2 text-blue-500 hover:text-blue-600 text-xs">
                                Reset filter untuk melihat semua data
                            </button>
                        </div>
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
        let billingStatusChart = null;
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
            loadBillingStatusData();
            
            // Event listeners untuk filter
            document.getElementById('periodFilter').addEventListener('change', handlePeriodChange);
            document.getElementById('valueRangeFilter').addEventListener('change', handleValueRangeChange);
            document.getElementById('applyFilters').addEventListener('click', applyFilters);
            document.getElementById('resetFilters').addEventListener('click', resetAllFilters);
            
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
                
                // Check if there's actual data (not just empty records with 0 values)
                const hasActualData = data.data && data.data.length > 0 && 
                    data.data.some(item => item.count > 0);
                
                if (hasActualData) {
                    // Filter out items with 0 count for chart display
                    const filteredData = data.data.filter(item => item.count > 0);
                    createProjectTypesChart(filteredData);
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

        // Create project types pie chart with external legend
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
                            // Create external legend after animation completes
                            createChartLegend(data, colors, totalCount);
                        }
                    },
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    }
                }
            });
        }

        // Create external legend for chart
        function createChartLegend(data, colors, totalCount) {
            const legendContainer = document.getElementById('chartLegend');
            legendContainer.innerHTML = '';
            
            data.forEach((item, index) => {
                const percentage = ((item.count / totalCount) * 100).toFixed(1);
                
                // Create legend item
                const legendItem = document.createElement('div');
                legendItem.className = 'flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer';
                legendItem.setAttribute('data-index', index);
                
                // Color indicator
                const colorBox = document.createElement('div');
                colorBox.className = 'w-4 h-4 rounded-sm flex-shrink-0 mt-0.5';
                colorBox.style.backgroundColor = colors[index];
                
                // Legend content
                const content = document.createElement('div');
                content.className = 'flex-1 min-w-0';
                
                const title = document.createElement('div');
                title.className = 'font-medium text-gray-900 text-sm';
                title.textContent = item.label;
                
                const stats = document.createElement('div');
                stats.className = 'text-xs text-gray-600 mt-1';
                stats.innerHTML = `
                    <div>${item.count} proyek (${percentage}%)</div>
                    <div class="font-medium">${item.formatted_total_value}</div>
                `;
                
                content.appendChild(title);
                content.appendChild(stats);
                
                legendItem.appendChild(colorBox);
                legendItem.appendChild(content);
                
                // Add click event to highlight chart segment
                legendItem.addEventListener('click', function() {
                    highlightChartSegment(index);
                });
                
                legendContainer.appendChild(legendItem);
            });
        }
        
        // Highlight specific chart segment
        function highlightChartSegment(index) {
            if (projectTypesChart) {
                // Reset all segments
                projectTypesChart.setActiveElements([]);
                
                // Highlight specific segment
                projectTypesChart.setActiveElements([{
                    datasetIndex: 0,
                    index: index
                }]);
                
                projectTypesChart.update('none');
                
                // Show tooltip
                projectTypesChart.tooltip.setActiveElements([{
                    datasetIndex: 0,
                    index: index
                }], {
                    x: projectTypesChart.canvas.width / 2,
                    y: projectTypesChart.canvas.height / 2
                });
                
                projectTypesChart.update('none');
            }
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

        // ========== BILLING STATUS CHART FUNCTIONS ==========

        // Load billing status data with filters
        async function loadBillingStatusData() {
            showBillingChartLoading();
            
            try {
                // Build query parameters
                const params = new URLSearchParams();
                
                Object.keys(currentFilters).forEach(key => {
                    if (currentFilters[key] && currentFilters[key] !== 'all') {
                        params.append(key, currentFilters[key]);
                    }
                });
                
                const response = await fetch(`/api/dashboard/billing-status?${params.toString()}`);
                const data = await response.json();
                
                // Check if there's actual data (not just empty records with 0 values)
                const hasActualData = data.data && data.data.length > 0 && 
                    data.data.some(item => item.count > 0);
                
                if (hasActualData) {
                    // Filter out items with 0 count for chart display
                    const filteredData = data.data.filter(item => item.count > 0);
                    createBillingStatusChart(filteredData);
                    showBillingChart();
                } else {
                    showBillingNoData();
                }
                
            } catch (error) {
                console.error('Error loading billing status data:', error);
                showBillingChartError();
            }
        }

        // Show billing chart loading state
        function showBillingChartLoading() {
            document.getElementById('billingChartLoading').style.display = 'block';
            document.getElementById('billingChartContainer').style.display = 'none';
            document.getElementById('billingNoDataState').style.display = 'none';
        }

        // Show billing chart
        function showBillingChart() {
            document.getElementById('billingChartLoading').style.display = 'none';
            document.getElementById('billingChartContainer').style.display = 'block';
            document.getElementById('billingNoDataState').style.display = 'none';
        }

        // Show billing no data state
        function showBillingNoData() {
            document.getElementById('billingChartLoading').style.display = 'none';
            document.getElementById('billingChartContainer').style.display = 'none';
            document.getElementById('billingNoDataState').style.display = 'block';
        }

        // Show billing chart error
        function showBillingChartError() {
            document.getElementById('billingChartLoading').style.display = 'none';
            document.getElementById('billingChartContainer').style.display = 'none';
            document.getElementById('billingNoDataState').style.display = 'block';
        }

        // Create billing status pie chart with external legend
        function createBillingStatusChart(data) {
            const ctx = document.getElementById('billingStatusChart').getContext('2d');
            
            // Destroy existing chart
            if (billingStatusChart) {
                billingStatusChart.destroy();
            }
            
            const labels = data.map(item => item.label);
            const values = data.map(item => item.count);
            
            // Custom colors untuk billing status
            const billingColors = ['#6B7280', '#3B82F6', '#F59E0B', '#10B981']; // Gray, Blue, Amber, Green
            const colors = billingColors.slice(0, data.length);
            const totalCount = values.reduce((a, b) => a + b, 0);
            
            billingStatusChart = new Chart(ctx, {
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
                                        `Total Nilai: ${item.formatted_total_value}`
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
                            // Create external legend after animation completes
                            createBillingChartLegend(data, colors, totalCount);
                        }
                    },
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    }
                }
            });
        }

        // Create external legend for billing chart
        function createBillingChartLegend(data, colors, totalCount) {
            const legendContainer = document.getElementById('billingChartLegend');
            legendContainer.innerHTML = '';
            
            data.forEach((item, index) => {
                const percentage = ((item.count / totalCount) * 100).toFixed(1);
                
                // Create legend item
                const legendItem = document.createElement('div');
                legendItem.className = 'flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer';
                legendItem.setAttribute('data-index', index);
                
                // Color indicator
                const colorBox = document.createElement('div');
                colorBox.className = 'w-4 h-4 rounded-sm flex-shrink-0 mt-0.5';
                colorBox.style.backgroundColor = colors[index];
                
                // Legend content
                const content = document.createElement('div');
                content.className = 'flex-1 min-w-0';
                
                const title = document.createElement('div');
                title.className = 'font-medium text-gray-900 text-sm';
                title.textContent = item.label;
                
                const stats = document.createElement('div');
                stats.className = 'text-xs text-gray-600 mt-1';
                stats.innerHTML = `
                    <div>${item.count} proyek (${percentage}%)</div>
                    <div class="font-medium">${item.formatted_total_value}</div>
                `;
                
                content.appendChild(title);
                content.appendChild(stats);
                
                legendItem.appendChild(colorBox);
                legendItem.appendChild(content);
                
                // Add click event to highlight chart segment
                legendItem.addEventListener('click', function() {
                    highlightBillingChartSegment(index);
                });
                
                legendContainer.appendChild(legendItem);
            });
        }
        
        // Highlight specific billing chart segment
        function highlightBillingChartSegment(index) {
            if (billingStatusChart) {
                // Reset all segments
                billingStatusChart.setActiveElements([]);
                
                // Highlight specific segment
                billingStatusChart.setActiveElements([{
                    datasetIndex: 0,
                    index: index
                }]);
                
                billingStatusChart.update('none');
                
                // Show tooltip
                billingStatusChart.tooltip.setActiveElements([{
                    datasetIndex: 0,
                    index: index
                }], {
                    x: billingStatusChart.canvas.width / 2,
                    y: billingStatusChart.canvas.height / 2
                });
                
                billingStatusChart.update('none');
            }
        }

        // Reset billing filters
        function resetBillingFilters() {
            // Reset to same filters as project types chart
            resetAllFilters();
            loadBillingStatusData();
        }

        // Export billing chart functionality
        function exportBillingChart() {
            if (billingStatusChart) {
                const url = billingStatusChart.toBase64Image();
                const link = document.createElement('a');
                link.download = 'analisis-status-tagihan.png';
                link.href = url;
                link.click();
            }
        }

        // Update applyFilters function to also reload billing data
        const originalApplyFilters = applyFilters;
        applyFilters = function() {
            originalApplyFilters();
            loadBillingStatusData();
        };

        // Update resetAllFilters function to also reload billing data
        const originalResetAllFilters = resetAllFilters;
        resetAllFilters = function() {
            originalResetAllFilters();
            loadBillingStatusData();
        };
    </script>
</x-app-layout>
