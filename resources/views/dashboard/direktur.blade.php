<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Direktur') }}
        </h2>
    </x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card">
            <div class="p-6 text-slate-900">
                <h2 class="text-2xl font-bold mb-6 text-slate-800">Dashboard Direktur</h2>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="dashboard-stat">
                        <h3 class="text-lg font-semibold">Total Proyek</h3>
                        <p class="text-3xl font-bold">{{ $totalProjects }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold">Proyek Selesai</h3>
                        <p class="text-3xl font-bold">{{ $completedProjects }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold">Total Revenue</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold">Total Expenses</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Statistik Keuangan -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="dashboard-card">
                        <h3 class="text-lg font-semibold text-slate-700">Total Budget</h3>
                        <p class="text-2xl font-bold text-sky-600">Rp {{ number_format($totalBudget) }}</p>
                    </div>

                    <div class="dashboard-card">
                        <h3 class="text-lg font-semibold text-slate-700">Total Pengeluaran</h3>
                        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpenses) }}</p>
                    </div>

                    <div class="dashboard-card">
                        <h3 class="text-lg font-semibold text-slate-700">Total Revenue</h3>
                        <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalRevenue) }}</p>
                    </div>

                    <div class="dashboard-card">
                        <h3 class="text-lg font-semibold text-slate-700">Net Profit</h3>
                        <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            Rp {{ number_format($netProfit) }}
                        </p>
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

            <!-- Alerts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Pending Approvals</h3>
                        <div class="flex justify-between items-center">
                            <span>Pengeluaran Pending</span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $pendingExpenses }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span>Invoice Overdue</span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $overdueInvoices }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
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

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
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
</x-app-layout>
