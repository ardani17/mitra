@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Dashboard Keuangan</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Ringkasan dan analisis keuangan perusahaan</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <select id="period-filter" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_quarter">Kuartal Ini</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Custom</option>
            </select>
            <a href="{{ route('finance.cashflow.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="hidden sm:inline">Tambah Transaksi</span>
                <span class="sm:hidden">Tambah</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="finance-dashboard-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total Income -->
        <div class="finance-summary-card bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-green-100 text-xs sm:text-sm font-medium">Total Pemasukan</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold" id="total-income">{{ $summary['total_income_formatted'] }}</p>
                    <p class="text-green-100 text-xs mt-1">{{ $summary['income_count'] }} transaksi</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Expense -->
        <div class="finance-summary-card bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-red-100 text-xs sm:text-sm font-medium">Total Pengeluaran</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold" id="total-expense">{{ $summary['total_expense_formatted'] }}</p>
                    <p class="text-red-100 text-xs mt-1">{{ $summary['expense_count'] }} transaksi</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Net Cash Flow -->
        <div class="finance-summary-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-blue-100 text-xs sm:text-sm font-medium">Arus Kas Bersih</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold" id="net-cashflow">{{ $summary['net_cashflow_formatted'] }}</p>
                    <p class="text-blue-100 text-xs mt-1">
                        @if($summary['net_cashflow'] > 0)
                            Surplus
                        @elseif($summary['net_cashflow'] < 0)
                            Defisit
                        @else
                            Seimbang
                        @endif
                    </p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Transactions -->
        <div class="finance-summary-card bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-yellow-100 text-xs sm:text-sm font-medium">Transaksi Pending</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold" id="pending-count">{{ $summary['pending_count'] }}</p>
                    <p class="text-yellow-100 text-xs mt-1">Perlu konfirmasi</p>
                </div>
                <div class="bg-yellow-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Monthly Trend Chart -->
        <div class="mobile-chart-container bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="finance-chart-header flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="finance-chart-title text-base sm:text-lg font-semibold text-slate-800 mb-2 sm:mb-0">Tren Bulanan</h3>
                <div class="finance-chart-controls flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Pemasukan
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                        Pengeluaran
                    </span>
                </div>
            </div>
            <div class="mobile-chart-wrapper h-48 sm:h-64">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="mobile-chart-container bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="finance-chart-header flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="finance-chart-title text-base sm:text-lg font-semibold text-slate-800 mb-2 sm:mb-0">Breakdown Kategori</h3>
                <select id="category-type-filter" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="expense">Pengeluaran</option>
                    <option value="income">Pemasukan</option>
                </select>
            </div>
            <div class="mobile-chart-wrapper h-48 sm:h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Top Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Transaksi Terbaru</h3>
                <a href="{{ route('finance.cashflow.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recent_transactions as $transaction)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $transaction->type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($transaction->type === 'income')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ Str::limit($transaction->description, 30) }}</p>
                                <p class="text-sm text-slate-600">{{ $transaction->category->name }}</p>
                                <p class="text-xs text-slate-500">{{ $transaction->transaction_date->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->formatted_amount }}
                            </p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>Belum ada transaksi</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Projects by Revenue -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Proyek Teratas</h3>
                <span class="text-sm text-slate-600">Berdasarkan pendapatan</span>
            </div>
            <div class="space-y-3">
                @forelse($top_projects as $project)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ Str::limit($project->name, 25) }}</p>
                                <p class="text-sm text-slate-600">{{ $project->transactions_count }} transaksi</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">
                                {{ number_format($project->total_income, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Net: {{ number_format($project->net_amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"/>
                        </svg>
                        <p>Belum ada data proyek</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('finance.cashflow.create') }}" 
               class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">Tambah Transaksi</p>
                </div>
            </a>
            
            <a href="{{ route('finance.cashflow.index', ['status' => 'pending']) }}" 
               class="flex items-center justify-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-yellow-800">Review Pending</p>
                </div>
            </a>
            
            <a href="{{ route('finance.reports.cashflow') }}" 
               class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">Laporan</p>
                </div>
            </a>
            
            <a href="{{ route('finance.cashflow.export') }}" 
               class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-purple-800">Export Data</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($monthly_trends->pluck('month')),
            datasets: [{
                label: 'Pemasukan',
                data: @json($monthly_trends->pluck('income')),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Pengeluaran',
                data: @json($monthly_trends->pluck('expense')),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
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
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    let categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($expense_categories->pluck('name')),
            datasets: [{
                data: @json($expense_categories->pluck('total')),
                backgroundColor: [
                    '#ef4444', '#f97316', '#eab308', '#22c55e', 
                    '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Category type filter
    document.getElementById('category-type-filter').addEventListener('change', function() {
        const type = this.value;
        
        fetch(`/finance/dashboard/categories?type=${type}`)
            .then(response => response.json())
            .then(data => {
                categoryChart.data.labels = data.map(item => item.name);
                categoryChart.data.datasets[0].data = data.map(item => item.total);
                categoryChart.update();
            });
    });

    // Period filter
    document.getElementById('period-filter').addEventListener('change', function() {
        const period = this.value;
        
        if (period === 'custom') {
            // Show date picker modal or redirect to filtered page
            window.location.href = `/finance/dashboard?period=${period}`;
        } else {
            window.location.href = `/finance/dashboard?period=${period}`;
        }
    });

    // Auto refresh every 5 minutes
    setInterval(function() {
        fetch('/finance/dashboard/summary')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-income').textContent = data.total_income_formatted;
                document.getElementById('total-expense').textContent = data.total_expense_formatted;
                document.getElementById('net-cashflow').textContent = data.net_cashflow_formatted;
                document.getElementById('pending-count').textContent = data.pending_count;
            });
    }, 300000); // 5 minutes
});
</script>

@endsection