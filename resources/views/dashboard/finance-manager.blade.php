<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Finance Manager') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-emerald-100">Kelola keuangan dan cash flow perusahaan</p>
            </div>

            <!-- Financial KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Pendapatan</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($totalRevenue) }}</p>
                            <p class="text-sm opacity-75">Pendapatan terkonfirmasi</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            💰
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Invoice Tertunda</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($pendingInvoices) }}</p>
                            <p class="text-sm opacity-75">Menunggu pembayaran</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Invoice Terlambat</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($overdueInvoices) }}</p>
                            <p class="text-sm opacity-75">Perlu tindak lanjut</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            🚨
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Pengeluaran</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($totalExpenses) }}</p>
                            <p class="text-sm opacity-75">Pengeluaran disetujui</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            💸
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Flow Bulanan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Revenue Bulan Ini</h3>
                        <p class="text-3xl font-bold text-green-600">Rp {{ number_format($monthlyRevenue) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Expenses Bulan Ini</h3>
                        <p class="text-3xl font-bold text-red-600">Rp {{ number_format($monthlyExpenses) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Persetujuan Tertunda</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                            <h4 class="font-semibold text-yellow-800">Pengeluaran Pending</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $pendingExpenses }}</p>
                            <p class="text-sm text-yellow-600">Total: Rp {{ number_format($pendingExpensesAmount) }}</p>
                            <a href="{{ route('expenses.index', ['status' => 'pending']) }}" class="text-yellow-700 hover:text-yellow-900 text-sm font-medium">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <h4 class="font-semibold text-blue-800">Net Cash Flow Bulan Ini</h4>
                            <p class="text-2xl font-bold {{ ($monthlyRevenue - $monthlyExpenses) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($monthlyRevenue - $monthlyExpenses) }}
                            </p>
                            <p class="text-sm text-blue-600">Revenue - Expenses</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts dan Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Penagihan berdasarkan Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Penagihan berdasarkan Status</h3>
                        <div class="space-y-2">
                            @foreach($billingsByStatus as $billing)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $billing->status) }}</span>
                                    <span class="font-semibold">{{ $billing->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Top Proyek berdasarkan Revenue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Top Proyek (Revenue)</h3>
                        <div class="space-y-2">
                            @foreach($topProjects as $project)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">{{ Str::limit($project->name, 20) }}</span>
                                    <span class="font-semibold text-green-600">Rp {{ number_format($project->total_revenue) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Analisis Visual Proyek</h3>
                    <div class="flex items-center space-x-4">
                        <select id="yearFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih Tahun...</option>
                        </select>
                        <button id="refreshBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
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

            <!-- Quick Actions -->
            <div class="card">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                        <a href="{{ route('billings.index') }}" class="btn-primary text-center text-xs sm:text-sm py-2 px-2 sm:px-4">
                            Lihat Penagihan
                        </a>
                        <a href="{{ route('billings.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Buat Invoice
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Review Expenses
                        </a>
                        <a href="{{ route('projects.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-2 sm:px-4 rounded text-center text-xs sm:text-sm">
                            Lihat Proyek
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
