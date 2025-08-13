@extends('layouts.app')

@section('title', 'Laporan Cash Flow')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Laporan Cash Flow</h1>
            <p class="text-slate-600 mt-1">Periode: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" 
                    class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('finance.cashflow.export', ['format' => 'pdf'] + request()->all()) }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('finance.dashboard') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex items-center space-x-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" 
                       value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" 
                       value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="pt-6">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Pemasukan</p>
                    <p class="text-2xl font-bold">{{ $summary['total_income_formatted'] }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Pengeluaran</p>
                    <p class="text-2xl font-bold">{{ $summary['total_expense_formatted'] }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Arus Kas Bersih</p>
                    <p class="text-2xl font-bold">{{ $summary['net_cashflow_formatted'] }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Statement -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Laporan Arus Kas</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Tanggal</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Deskripsi</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Kategori</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Proyek</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-700">Tipe</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700">Jumlah</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700">Saldo Berjalan</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 text-sm">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">{{ Str::limit($transaction->description, 40) }}</div>
                                @if($transaction->notes)
                                    <div class="text-xs text-slate-500 mt-1">{{ Str::limit($transaction->notes, 50) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $transaction->category->name }}</td>
                            <td class="py-3 px-4 text-sm">{{ $transaction->project?->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $transaction->formatted_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-medium {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold {{ $transaction->running_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($transaction->running_balance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p>Tidak ada transaksi untuk periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analysis Section -->
    @if($transactions->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Income Analysis -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Analisis Pemasukan</h3>
                <div class="space-y-3">
                    @foreach($transactions->where('type', 'income')->groupBy('category.name') as $categoryName => $categoryTransactions)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-800">{{ $categoryName }}</p>
                                <p class="text-sm text-slate-600">{{ $categoryTransactions->count() }} transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">
                                    Rp {{ number_format($categoryTransactions->sum('amount'), 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ number_format(($categoryTransactions->sum('amount') / $summary['total_income']) * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Expense Analysis -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Analisis Pengeluaran</h3>
                <div class="space-y-3">
                    @foreach($transactions->where('type', 'expense')->groupBy('category.name') as $categoryName => $categoryTransactions)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-800">{{ $categoryName }}</p>
                                <p class="text-sm text-slate-600">{{ $categoryTransactions->count() }} transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-red-600">
                                    Rp {{ number_format($categoryTransactions->sum('amount'), 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ number_format(($categoryTransactions->sum('amount') / $summary['total_expense']) * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { font-size: 12px; }
    .container { max-width: none; margin: 0; padding: 20px; }
}
</style>

@endsection