<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Karyawan') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.create') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Tambah Karyawan
                </a>
                <a href="{{ route('finance.employees.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-list mr-2"></i>Daftar Karyawan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Karyawan</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-employees">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-check text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Karyawan Aktif</p>
                                <p class="text-2xl font-semibold text-gray-900" id="active-employees">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Kontrak Berakhir</p>
                                <p class="text-2xl font-semibold text-gray-900" id="contract-expiring">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-plus text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Karyawan Baru (30 hari)</p>
                                <p class="text-2xl font-semibold text-gray-900" id="recent-hires">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Department Distribution -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi per Departemen</h3>
                        <div class="h-64">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Employment Type Distribution -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipe Karyawan</h3>
                        <div class="h-64">
                            <canvas id="employmentTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Average Daily Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Rata-rata Gaji Harian</p>
                                <p class="text-2xl font-semibold text-gray-900" id="average-daily-rate">-</p>
                            </div>
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-money-bill text-green-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performers</h3>
                        <div id="top-performers" class="space-y-2">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Contract Expiring Soon -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Kontrak Berakhir Segera</h3>
                        <div id="expiring-contracts" class="space-y-2">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Aktivitas Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Karyawan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Departemen
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Masuk
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="recent-employees" class="bg-white divide-y divide-gray-200">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let departmentChart, employmentTypeChart;

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const response = await fetch('{{ route("finance.employees.analytics") }}');
                const data = await response.json();
                
                // Update metrics
                document.getElementById('total-employees').textContent = data.total_employees;
                document.getElementById('active-employees').textContent = data.active_employees;
                document.getElementById('contract-expiring').textContent = data.contract_expiring;
                document.getElementById('recent-hires').textContent = data.recent_hires;
                document.getElementById('average-daily-rate').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.average_daily_rate);

                // Create charts
                createDepartmentChart(data.by_department);
                createEmploymentTypeChart(data.by_employment_type);

                // Load additional data
                loadTopPerformers();
                loadExpiringContracts();
                loadRecentEmployees();

            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        function createDepartmentChart(data) {
            const ctx = document.getElementById('departmentChart').getContext('2d');
            
            if (departmentChart) {
                departmentChart.destroy();
            }

            departmentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createEmploymentTypeChart(data) {
            const ctx = document.getElementById('employmentTypeChart').getContext('2d');
            
            if (employmentTypeChart) {
                employmentTypeChart.destroy();
            }

            const labels = Object.keys(data).map(key => {
                switch(key) {
                    case 'permanent': return 'Tetap';
                    case 'contract': return 'Kontrak';
                    case 'freelance': return 'Freelance';
                    default: return key;
                }
            });

            employmentTypeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Karyawan',
                        data: Object.values(data),
                        backgroundColor: ['#10B981', '#F59E0B', '#8B5CF6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        async function loadTopPerformers() {
            // This would typically fetch from an API endpoint
            // For now, we'll show a placeholder
            const container = document.getElementById('top-performers');
            container.innerHTML = `
                <div class="text-center text-gray-500 text-sm py-4">
                    <i class="fas fa-chart-line text-2xl mb-2"></i>
                    <p>Data performa akan tersedia setelah<br>sistem tracking performa diaktifkan</p>
                </div>
            `;
        }

        async function loadExpiringContracts() {
            // This would typically fetch from an API endpoint
            const container = document.getElementById('expiring-contracts');
            container.innerHTML = `
                <div class="text-center text-gray-500 text-sm py-4">
                    <i class="fas fa-calendar-times text-2xl mb-2"></i>
                    <p>Tidak ada kontrak yang berakhir<br>dalam 30 hari ke depan</p>
                </div>
            `;
        }

        async function loadRecentEmployees() {
            try {
                const response = await fetch('{{ route("finance.employees.index") }}?sort_by=hire_date&sort_order=desc&limit=5');
                // This would need to be implemented as an API endpoint
                // For now, show placeholder
                const tbody = document.getElementById('recent-employees');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p>Memuat data karyawan terbaru...</p>
                        </td>
                    </tr>
                `;
            } catch (error) {
                console.error('Error loading recent employees:', error);
            }
        }

        // Auto-refresh dashboard every 5 minutes
        setInterval(loadDashboardData, 300000);

        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
    @endpush
</x-app-layout>