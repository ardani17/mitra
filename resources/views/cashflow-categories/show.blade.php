@extends('layouts.app')

@section('title', 'Detail Kategori: ' . $cashflowCategory->name)

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ $cashflowCategory->name }}</h1>
            <div class="flex items-center mt-2 space-x-3">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cashflowCategory->type_badge_class }}">
                    {{ $cashflowCategory->formatted_type }}
                </span>
                <span class="text-sm text-gray-600">
                    <i class="fas {{ $cashflowCategory->group_icon }} mr-1"></i>
                    {{ $cashflowCategory->formatted_group }}
                </span>
                @if($cashflowCategory->is_system)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                    Kategori Sistem
                </span>
                @endif
                @if($cashflowCategory->is_active)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Aktif
                </span>
                @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    Nonaktif
                </span>
                @endif
            </div>
        </div>
        <div class="flex space-x-2">
            @if(!$cashflowCategory->is_system)
            <a href="{{ route('finance.cashflow-categories.edit', $cashflowCategory) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                <i class="fas fa-edit mr-2"></i>
                <span class="hidden sm:inline">Edit</span>
            </a>
            @endif
            <a href="{{ route('finance.cashflow-categories.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Kembali</span>
            </a>
        </div>
    </div>

    <!-- Category Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Category Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kategori</h2>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Kode</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $cashflowCategory->code }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $cashflowCategory->description ?: '-' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Urutan Tampil</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $cashflowCategory->sort_order }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Transaksi</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $cashflowCategory->cashflow_entries_count ?? 0 }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dibuat Pada</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $cashflowCategory->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $cashflowCategory->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Monthly Statistics -->
            @if($monthlyStats && $monthlyStats->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Statistik Bulanan</h2>
                
                <div class="space-y-2">
                    @foreach($monthlyStats->take(6) as $stat)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::createFromDate($stat->year, $stat->month, 1)->format('M Y') }}
                        </span>
                        <span class="text-sm font-semibold {{ $cashflowCategory->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($stat->total, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Recent Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Transaksi Terbaru</h2>
                    <a href="{{ route('finance.cashflow.index', ['category_id' => $cashflowCategory->id]) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                @if($recentEntries && $recentEntries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentEntries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->transaction_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="max-w-xs truncate">{{ $entry->description }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->project ? $entry->project->name : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium {{ $entry->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($entry->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $entry->status_badge_class }}">
                                        {{ $entry->formatted_status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                    <p class="text-gray-500">Belum ada transaksi untuk kategori ini</p>
                </div>
                @endif
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Bulan Ini</p>
                            <p class="text-xl font-semibold {{ $cashflowCategory->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                @php
                                    $thisMonth = $cashflowCategory->cashflowEntries()
                                        ->whereMonth('transaction_date', now()->month)
                                        ->whereYear('transaction_date', now()->year)
                                        ->where('status', 'confirmed')
                                        ->sum('amount');
                                @endphp
                                Rp {{ number_format($thisMonth, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Tahun Ini</p>
                            <p class="text-xl font-semibold {{ $cashflowCategory->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                @php
                                    $thisYear = $cashflowCategory->cashflowEntries()
                                        ->whereYear('transaction_date', now()->year)
                                        ->where('status', 'confirmed')
                                        ->sum('amount');
                                @endphp
                                Rp {{ number_format($thisYear, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Keseluruhan</p>
                            <p class="text-xl font-semibold {{ $cashflowCategory->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                @php
                                    $total = $cashflowCategory->cashflowEntries()
                                        ->where('status', 'confirmed')
                                        ->sum('amount');
                                @endphp
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-calculator text-gray-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection