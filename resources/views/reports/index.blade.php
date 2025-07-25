@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Laporan Keuangan</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.export.financial', request()->query()) }}" 
               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('reports.profitability') }}" 
               class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                Analisis Profitabilitas
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Filter Periode</h3>
        <form method="GET" action="{{ route('reports.index') }}" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="form-input" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="form-input" required>
            </div>
            <button type="submit" class="btn-primary">
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Projects -->
        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Proyek</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $projectStats['total_projects'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($financialStats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($financialStats['total_expenses'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $financialStats['net_profit'] >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Net Profit</p>
                    <p class="text-2xl font-semibold {{ $financialStats['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($financialStats['net_profit'], 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Margin: {{ number_format($financialStats['profit_margin'], 2) }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Status Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Project Status Chart -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Status Proyek</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Selesai</span>
                    <span class="text-sm font-semibold text-green-600">{{ $projectStats['completed_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Sedang Berjalan</span>
                    <span class="text-sm font-semibold text-blue-600">{{ $projectStats['in_progress_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Perencanaan</span>
                    <span class="text-sm font-semibold text-yellow-600">{{ $projectStats['planning_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Dibatalkan</span>
                    <span class="text-sm font-semibold text-red-600">{{ $projectStats['cancelled_projects'] }}</span>
                </div>
            </div>
        </div>

        <!-- Value Comparison -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Perbandingan Nilai</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Nilai Plan Total</span>
                    <span class="text-sm font-semibold text-blue-600">
                        Rp {{ number_format($projectStats['total_planned_value'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Nilai Akhir Total</span>
                    <span class="text-sm font-semibold text-green-600">
                        Rp {{ number_format($projectStats['total_final_value'], 0, ',', '.') }}
                    </span>
                </div>
                @php
                    $variance = $projectStats['total_final_value'] - $projectStats['total_planned_value'];
                    $variancePercent = $projectStats['total_planned_value'] > 0 ? ($variance / $projectStats['total_planned_value']) * 100 : 0;
                @endphp
                <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-sm font-medium text-gray-600">Selisih</span>
                    <span class="text-sm font-semibold {{ $variance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($variance, 0, ',', '.') }}
                        ({{ number_format($variancePercent, 2) }}%)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="card p-6 mb-8">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Tren Bulanan (12 Bulan Terakhir)</h3>
        <div class="h-64">
            <canvas id="monthlyTrendsChart"></canvas>
        </div>
    </div>

    <!-- Top Projects -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Top 10 Proyek Berdasarkan Pendapatan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topProjects as $project)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                            <div class="text-sm text-gray-500">{{ $project->code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($project->total_revenue, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($project->total_expenses, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $project->net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($project->net_profit, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if($project->status == 'completed') bg-green-100 text-green-800
                                  @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                                  @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                @if($project->status == 'planning') Perencanaan
                                @elseif($project->status == 'in_progress') Sedang Berjalan
                                @elseif($project->status == 'completed') Selesai
                                @elseif($project->status == 'cancelled') Dibatalkan
                                @else {{ ucfirst($project->status) }}
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data proyek ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    const monthlyTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($monthlyTrends['months']),
            datasets: [{
                label: 'Pendapatan',
                data: @json($monthlyTrends['revenues']),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Pengeluaran',
                data: @json($monthlyTrends['expenses']),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }, {
                label: 'Net Profit',
                data: @json($monthlyTrends['profits']),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
