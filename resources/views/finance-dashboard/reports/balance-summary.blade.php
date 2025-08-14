@extends('layouts.app')

@section('title', 'Ringkasan Saldo')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Ringkasan Saldo</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Periode: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <button onclick="window.print()"
                    class="bg-slate-600 hover:bg-slate-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('finance.dashboard') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-end space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date"
                       value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
            </div>
            <div class="flex-1">
                <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date"
                       value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
            </div>
            <div class="flex-shrink-0">
                <button type="submit"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 text-sm sm:text-base">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="text-center">
                <p class="text-green-100 text-xs sm:text-sm font-medium">Total Pemasukan</p>
                <p class="text-lg sm:text-2xl font-bold break-words">{{ $summary['total_income_formatted'] }}</p>
                <p class="text-green-100 text-xs mt-1">{{ $summary['income_count'] }} transaksi</p>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="text-center">
                <p class="text-red-100 text-xs sm:text-sm font-medium">Total Pengeluaran</p>
                <p class="text-lg sm:text-2xl font-bold break-words">{{ $summary['total_expense_formatted'] }}</p>
                <p class="text-red-100 text-xs mt-1">{{ $summary['expense_count'] }} transaksi</p>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="text-center">
                <p class="text-blue-100 text-xs sm:text-sm font-medium">Saldo Bersih</p>
                <p class="text-lg sm:text-2xl font-bold break-words">{{ $summary['net_cashflow_formatted'] }}</p>
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
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="text-center">
                <p class="text-purple-100 text-xs sm:text-sm font-medium">Margin Keuntungan</p>
                <p class="text-lg sm:text-2xl font-bold">
                    @if($summary['total_income'] > 0)
                        {{ number_format((($summary['net_cashflow'] / $summary['total_income']) * 100), 1) }}%
                    @else
                        0%
                    @endif
                </p>
                <p class="text-purple-100 text-xs mt-1">Dari total pemasukan</p>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Income Categories -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4 flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
                Breakdown Pemasukan per Kategori
            </h3>
            @if(isset($categoryBreakdown['income']) && $categoryBreakdown['income']->count() > 0)
                <div class="space-y-3">
                    @foreach($categoryBreakdown['income'] as $category)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-800">{{ $category['category'] }}</p>
                                <p class="text-sm text-slate-600">{{ $category['count'] }} transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">
                                    Rp {{ number_format($category['total'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ number_format(($category['total'] / $summary['total_income']) * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>Tidak ada data pemasukan</p>
                </div>
            @endif
        </div>

        <!-- Expense Categories -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4 flex items-center">
                <div class="w-4 h-4 bg-red-500 rounded-full mr-2"></div>
                Breakdown Pengeluaran per Kategori
            </h3>
            @if(isset($categoryBreakdown['expense']) && $categoryBreakdown['expense']->count() > 0)
                <div class="space-y-3">
                    @foreach($categoryBreakdown['expense'] as $category)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-800">{{ $category['category'] }}</p>
                                <p class="text-sm text-slate-600">{{ $category['count'] }} transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-red-600">
                                    Rp {{ number_format($category['total'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ number_format(($category['total'] / $summary['total_expense']) * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>Tidak ada data pengeluaran</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Project Performance -->
    @if($projectBreakdown->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Performa Proyek</h3>
            
            <!-- Mobile Cards View -->
            <div class="lg:hidden space-y-3">
                @foreach($projectBreakdown as $project)
                    <div class="card p-4 border border-slate-200 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-slate-800 text-sm break-words flex-1 mr-2">{{ $project['name'] }}</h4>
                            <span class="text-xs px-2 py-1 rounded-full {{ $project['margin'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ number_format($project['margin'], 1) }}%
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-slate-600">Pemasukan:</span>
                                <div class="font-medium text-green-600 break-words">Rp {{ number_format($project['income'], 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <span class="text-slate-600">Pengeluaran:</span>
                                <div class="font-medium text-red-600 break-words">Rp {{ number_format($project['expense'], 0, ',', '.') }}</div>
                            </div>
                            <div class="col-span-2">
                                <span class="text-slate-600">Keuntungan:</span>
                                <div class="font-semibold {{ $project['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }} break-words">
                                    Rp {{ number_format($project['profit'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 font-semibold text-slate-700 text-sm">Nama Proyek</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Pemasukan</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Pengeluaran</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Keuntungan</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectBreakdown as $project)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4 font-medium text-slate-800 text-sm">{{ $project['name'] }}</td>
                                <td class="py-3 px-4 text-right text-green-600 font-medium text-sm">
                                    Rp {{ number_format($project['income'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-red-600 font-medium text-sm">
                                    Rp {{ number_format($project['expense'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold {{ $project['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm">
                                    Rp {{ number_format($project['profit'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium {{ $project['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm">
                                    {{ number_format($project['margin'], 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Monthly Comparison -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6 mb-6 sm:mb-8">
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Perbandingan Bulanan (6 Bulan Terakhir)</h3>
        
        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-3">
            @foreach($monthlyComparison as $index => $month)
                <div class="card p-4 border border-slate-200 rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-slate-800 text-sm">{{ $month['month'] }}</h4>
                        <div class="text-right">
                            @if($index > 0)
                                @php
                                    $prevBalance = $monthlyComparison[$index - 1]['balance'];
                                    $currentBalance = $month['balance'];
                                    $trend = $currentBalance - $prevBalance;
                                @endphp
                                @if($trend > 0)
                                    <span class="inline-flex items-center text-green-600 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                        </svg>
                                        Naik
                                    </span>
                                @elseif($trend < 0)
                                    <span class="inline-flex items-center text-red-600 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                        </svg>
                                        Turun
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-slate-600 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                        </svg>
                                        Stabil
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-600">Pemasukan:</span>
                            <div class="font-medium text-green-600 break-words">Rp {{ number_format($month['income'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-slate-600">Pengeluaran:</span>
                            <div class="font-medium text-red-600 break-words">Rp {{ number_format($month['expense'], 0, ',', '.') }}</div>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-600">Saldo:</span>
                            <div class="font-semibold {{ $month['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }} break-words">
                                Rp {{ number_format($month['balance'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700 text-sm">Bulan</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Pemasukan</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Pengeluaran</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700 text-sm">Saldo</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-700 text-sm">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyComparison as $index => $month)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 font-medium text-slate-800 text-sm">{{ $month['month'] }}</td>
                            <td class="py-3 px-4 text-right text-green-600 font-medium text-sm">
                                Rp {{ number_format($month['income'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-red-600 font-medium text-sm">
                                Rp {{ number_format($month['expense'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold {{ $month['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm">
                                Rp {{ number_format($month['balance'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($index > 0)
                                    @php
                                        $prevBalance = $monthlyComparison[$index - 1]['balance'];
                                        $currentBalance = $month['balance'];
                                        $trend = $currentBalance - $prevBalance;
                                    @endphp
                                    @if($trend > 0)
                                        <span class="inline-flex items-center text-green-600 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                            Naik
                                        </span>
                                    @elseif($trend < 0)
                                        <span class="inline-flex items-center text-red-600 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                            Turun
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-slate-600 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                            </svg>
                                            Stabil
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Key Insights -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Insight Keuangan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Cash Flow Health -->
            <div class="p-4 rounded-lg {{ $summary['net_cashflow'] >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center mb-2">
                    <div class="w-3 h-3 {{ $summary['net_cashflow'] >= 0 ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-2"></div>
                    <h4 class="font-medium {{ $summary['net_cashflow'] >= 0 ? 'text-green-800' : 'text-red-800' }}">Kesehatan Arus Kas</h4>
                </div>
                <p class="text-sm {{ $summary['net_cashflow'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    @if($summary['net_cashflow'] >= 0)
                        Arus kas positif menunjukkan kondisi keuangan yang sehat
                    @else
                        Arus kas negatif memerlukan perhatian khusus
                    @endif
                </p>
            </div>

            <!-- Expense Ratio -->
            <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                <div class="flex items-center mb-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <h4 class="font-medium text-blue-800">Rasio Pengeluaran</h4>
                </div>
                <p class="text-sm text-blue-700">
                    @if($summary['total_income'] > 0)
                        {{ number_format(($summary['total_expense'] / $summary['total_income']) * 100, 1) }}% dari total pemasukan
                    @else
                        Tidak ada pemasukan untuk perbandingan
                    @endif
                </p>
            </div>

            <!-- Transaction Volume -->
            <div class="p-4 rounded-lg bg-purple-50 border border-purple-200">
                <div class="flex items-center mb-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                    <h4 class="font-medium text-purple-800">Volume Transaksi</h4>
                </div>
                <p class="text-sm text-purple-700">
                    {{ $summary['income_count'] + $summary['expense_count'] }} total transaksi dalam periode ini
                </p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { font-size: 12px; }
    .container { max-width: none; margin: 0; padding: 20px; }
}
</style>

@endsection