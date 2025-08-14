<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Staf') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-lg shadow-lg p-4 sm:p-6 mb-6 sm:mb-8 text-white">
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-sm sm:text-base text-indigo-100">Kelola tugas dan pengeluaran proyek Anda</p>
            </div>

            <!-- Staff Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-3 sm:p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Proyek Aktif</h3>
                            <p class="text-2xl sm:text-3xl font-bold">{{ $activeProjects }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Sedang berjalan</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            🚀
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-3 sm:p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Total Pengeluaran</h3>
                            <p class="text-2xl sm:text-3xl font-bold">{{ $myExpenses }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Yang saya buat</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            📝
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-3 sm:p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Menunggu Persetujuan</h3>
                            <p class="text-2xl sm:text-3xl font-bold">{{ $myPendingExpenses }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Menunggu persetujuan</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-3 sm:p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-lg font-semibold opacity-90 truncate">Disetujui</h3>
                            <p class="text-2xl sm:text-3xl font-bold">{{ $myApprovedExpenses }}</p>
                            <p class="text-xs sm:text-sm opacity-75">Sudah disetujui</p>
                        </div>
                        <div class="text-2xl sm:text-4xl opacity-75 ml-2">
                            ✅
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Summary -->
            <div class="card p-4 sm:p-6 mb-6 sm:mb-8">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4">Ringkasan Pengeluaran Saya</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="text-center">
                        <h4 class="text-base sm:text-lg font-semibold text-gray-700">Total Pengajuan</h4>
                        <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $myExpenses }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Semua expense yang dibuat</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-base sm:text-lg font-semibold text-gray-700">Tingkat Keberhasilan</h4>
                        @php
                            $successRate = $myExpenses > 0 ? ($myApprovedExpenses / $myExpenses) * 100 : 0;
                        @endphp
                        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ number_format($successRate, 1) }}%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 sm:h-3 mt-2">
                            <div class="bg-green-500 h-2 sm:h-3 rounded-full transition-all duration-300" style="width: {{ $successRate }}%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <h4 class="text-base sm:text-lg font-semibold text-gray-700">Menunggu Review</h4>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $myPendingExpenses }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Butuh tindak lanjut</p>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="card mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Pengeluaran Terbaru Saya</h3>
                    @if($recentExpenses->count() > 0)
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentExpenses as $expense)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $expense->project->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ Str::limit($expense->description, 30) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($expense->amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($expense->status == 'approved') bg-green-100 text-green-800
                                                @elseif($expense->status == 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($expense->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($expense->created_at)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-3">
                            @foreach($recentExpenses as $expense)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 truncate" title="{{ $expense->project->name }}">
                                            {{ Str::limit($expense->project->name, 20) }}
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1 break-words">{{ Str::limit($expense->description, 40) }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full ml-2 whitespace-nowrap
                                        @if($expense->status == 'approved') bg-green-100 text-green-800
                                        @elseif($expense->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($expense->amount / 1000000, 1) }}M
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($expense->created_at)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4 text-sm">Belum ada pengeluaran yang dibuat.</p>
                    @endif
                </div>
            </div>

            <!-- Charts dan Data -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                <!-- Proyek berdasarkan Status -->
                <div class="card">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Proyek berdasarkan Status</h3>
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

                <!-- Aktivitas Terbaru -->
                <div class="card">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Aktivitas Terbaru Saya</h3>
                        <div class="space-y-2 max-h-40 sm:max-h-48 overflow-y-auto">
                            @foreach($myActivities as $activity)
                                <div class="text-xs sm:text-sm">
                                    <span class="text-gray-600 break-words">{{ Str::limit($activity->description, 50) }}</span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $activity->project_name }} - {{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Section -->
            <div class="card p-4 sm:p-6 mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800">Analisis Visual Proyek</h3>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <select id="yearFilter" class="form-select text-sm">
                            <option value="">Pilih Tahun...</option>
                        </select>
                        <button id="refreshBtn" class="btn-primary text-sm py-2 px-3 sm:px-4">
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Memuat data analitik...</p>
                </div>

                <!-- Charts Grid -->
                <div id="chartsContainer" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" style="display: none;">
                    <!-- Tipe Proyek Chart -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Tipe Proyek</h4>
                        <div class="relative h-64">
                            <canvas id="projectTypesChart"></canvas>
                        </div>
                        <div id="projectTypesLegend" class="mt-4 text-sm"></div>
                    </div>

                    <!-- Lokasi Proyek Chart -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Proyek</h4>
                        <div class="relative h-64">
                            <canvas id="projectLocationsChart"></canvas>
                        </div>
                        <div id="projectLocationsLegend" class="mt-4 text-sm"></div>
                    </div>

                    <!-- Status Penagihan Chart -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Status Penagihan</h4>
                        <div class="relative h-64">
                            <canvas id="billingStatusChart"></canvas>
                        </div>
                        <div id="billingStatusLegend" class="mt-4 text-sm"></div>
                    </div>

                    <!-- Status Proyek Chart -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Status Proyek</h4>
                        <div class="relative h-64">
                            <canvas id="projectStatusChart"></canvas>
                        </div>
                        <div id="projectStatusLegend" class="mt-4 text-sm"></div>
                    </div>

                    <!-- Status Pembayaran Chart -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Status Pembayaran</h4>
                        <div class="relative h-64">
                            <canvas id="paymentStatusChart"></canvas>
                        </div>
                        <div id="paymentStatusLegend" class="mt-4 text-sm"></div>
                    </div>
                </div>

                <!-- Error State -->
                <div id="errorState" class="text-center py-8" style="display: none;">
                    <div class="text-red-500 mb-4">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600">Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
                    <button id="retryBtn" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Coba Lagi
                    </button>
                </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="card">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                        <a href="{{ route('expenses.create') }}" class="btn-primary text-center text-xs sm:text-sm py-2 px-2 sm:px-4">
                            Buat Pengeluaran
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Lihat Pengeluaran
                        </a>
                        <a href="{{ route('projects.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Lihat Proyek
                        </a>
                        <a href="{{ route('timelines.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Lihat Timeline
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
        let charts = {};
        let currentYear = new Date().getFullYear();

        // Color palettes untuk charts
        const colorPalettes = {
            primary: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'],
            secondary: ['#DBEAFE', '#D1FAE5', '#FEF3C7', '#FEE2E2', '#EDE9FE', '#CFFAFE', '#ECFCCB', '#FED7AA']
        };

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadAvailableYears();
            loadDashboardData(currentYear);
            
            // Event listeners
            document.getElementById('yearFilter').addEventListener('change', function() {
                const selectedYear = this.value;
                if (selectedYear) {
                    currentYear = selectedYear;
                    loadDashboardData(selectedYear);
                }
            });

            document.getElementById('refreshBtn').addEventListener('click', function() {
                loadDashboardData(currentYear);
            });

            document.getElementById('retryBtn').addEventListener('click', function() {
                loadDashboardData(currentYear);
            });
        });

        // Load available years
        async function loadAvailableYears() {
            try {
                const response = await fetch('/api/dashboard/years');
                const years = await response.json();
                
                const yearSelect = document.getElementById('yearFilter');
                yearSelect.innerHTML = '<option value="">Pilih Tahun...</option>';
                
                years.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    if (year == currentYear) {
                        option.selected = true;
                    }
                    yearSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading years:', error);
            }
        }

        // Load dashboard data
        async function loadDashboardData(year) {
            showLoading();
            
            try {
                const response = await fetch(`/api/dashboard/analytics?year=${year}`);
                const data = await response.json();
                
                createCharts(data.charts);
                showCharts();
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showError();
            }
        }

        // Show loading state
        function showLoading() {
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('chartsContainer').style.display = 'none';
            document.getElementById('errorState').style.display = 'none';
        }

        // Show charts
        function showCharts() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('chartsContainer').style.display = 'grid';
            document.getElementById('errorState').style.display = 'none';
        }

        // Show error state
        function showError() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('chartsContainer').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
        }

        // Create all charts
        function createCharts(chartsData) {
            // Destroy existing charts
            Object.values(charts).forEach(chart => {
                if (chart) chart.destroy();
            });
            charts = {};

            // Create pie charts
            charts.projectTypes = createPieChart('projectTypesChart', chartsData.project_types, 'Tipe Proyek');
            charts.projectLocations = createPieChart('projectLocationsChart', chartsData.project_locations, 'Lokasi Proyek');
            charts.billingStatus = createPieChart('billingStatusChart', chartsData.billing_status, 'Status Penagihan');
            charts.projectStatus = createPieChart('projectStatusChart', chartsData.project_status, 'Status Proyek');
            charts.paymentStatus = createPieChart('paymentStatusChart', chartsData.payment_status, 'Status Pembayaran');

            // Create legends
            createLegend('projectTypesLegend', chartsData.project_types);
            createLegend('projectLocationsLegend', chartsData.project_locations);
            createLegend('billingStatusLegend', chartsData.billing_status);
            createLegend('projectStatusLegend', chartsData.project_status);
            createLegend('paymentStatusLegend', chartsData.payment_status);
        }

        // Create pie chart
        function createPieChart(canvasId, data, title) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            const labels = data.map(item => item.label);
            const values = data.map(item => item.value);
            const colors = colorPalettes.primary.slice(0, data.length);
            
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: colors.map(color => color + '80'),
                        borderWidth: 2
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
                            callbacks: {
                                label: function(context) {
                                    const item = data[context.dataIndex];
                                    const percentage = ((item.value / values.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                                    return `${item.label}: ${item.value} (${percentage}%) - ${item.formatted_value}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Create legend
        function createLegend(legendId, data) {
            const legendContainer = document.getElementById(legendId);
            const totalValue = data.reduce((sum, item) => sum + item.value, 0);
            
            legendContainer.innerHTML = data.map((item, index) => {
                const percentage = totalValue > 0 ? ((item.value / totalValue) * 100).toFixed(1) : 0;
                const color = colorPalettes.primary[index % colorPalettes.primary.length];
                
                return `
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color}"></div>
                            <span class="text-gray-700">${item.label}</span>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">${item.value} (${percentage}%)</div>
                            <div class="text-xs text-gray-500">${item.formatted_value}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    </script>
</x-app-layout>
