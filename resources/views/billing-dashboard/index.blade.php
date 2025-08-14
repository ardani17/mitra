@extends('layouts.app')

@section('title', 'Dashboard Penagihan')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Simple Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Dashboard Penagihan</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Overview dan statistik sistem penagihan</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <button id="refreshDashboard"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
            <a href="{{ route('billing-dashboard.export') }}"
               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Data
            </a>
            <a href="{{ route('project-billings.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Penagihan
            </a>
        </div>
    </div>

    <!-- Simple Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Periode</h3>
        <form method="GET" action="{{ route('billing-dashboard.index') }}">
            <!-- Quick Date Buttons -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" onclick="setDateRange('today')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                    Hari Ini
                </button>
                <button type="button" onclick="setDateRange('this_week')"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                    Minggu Ini
                </button>
                <button type="button" onclick="setDateRange('this_month')"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm">
                    Bulan Ini
                </button>
                <button type="button" onclick="setDateRange('this_year')"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-sm">
                    Tahun Ini
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div>
                    <label for="start_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                           class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="end_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                           class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="sm:flex sm:items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        Terapkan Filter
                    </button>
                </div>
                
                <div class="sm:flex sm:items-end">
                    <a href="{{ route('billing-dashboard.index') }}"
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm text-center block">
                        Reset
                    </a>
                </div>
            </div>
            
            @if(isset($startDate) && isset($endDate))
            <div class="text-sm text-gray-600 mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                Menampilkan data dari <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                sampai <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
            </div>
            @endif
        </form>
    </div>

    <!-- Simple Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Penagihan</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $overallStats['total_billings'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Nilai</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">Rp {{ number_format($overallStats['total_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Sudah Dibayar</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">Rp {{ number_format($overallStats['paid_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Belum Dibayar</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">Rp {{ number_format($overallStats['unpaid_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Additional KPIs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Tingkat Pembayaran</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $additionalKpis['payment_rate'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Rata-rata</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">Rp {{ number_format($additionalKpis['average_billing_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Efisiensi Koleksi</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $additionalKpis['collection_efficiency'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 {{ ($additionalKpis['growth_rate'] ?? 0) >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 {{ ($additionalKpis['growth_rate'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if(($additionalKpis['growth_rate'] ?? 0) >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Pertumbuhan</p>
                    <p class="text-lg sm:text-2xl font-semibold {{ ($additionalKpis['growth_rate'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($additionalKpis['growth_rate'] ?? 0) >= 0 ? '+' : '' }}{{ $additionalKpis['growth_rate'] ?? 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-violet-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Proyek Aktif</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $additionalKpis['total_projects_with_billing'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Simple Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h2>
                <a href="{{ route('project-billings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="flex items-start space-x-3 p-3 hover:bg-slate-50 rounded-lg transition-colors duration-200">
                        <div class="flex-shrink-0">
                            @if($activity['status'] === 'paid')
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @elseif($activity['status'] === 'sent')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900">{{ $activity['title'] }}</p>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-medium text-slate-900">Rp {{ number_format($activity['amount'], 0, ',', '.') }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                @if($activity['status'] === 'paid') bg-green-100 text-green-800
                                @elseif($activity['status'] === 'sent') bg-blue-100 text-blue-800
                                @else bg-slate-100 text-slate-800 @endif">
                                {{ ucfirst($activity['status']) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-600">Belum ada aktivitas penagihan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Simple Overdue Items -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Penagihan Terlambat</h2>
                @if(isset($overdueItems) && $overdueItems->count() > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ $overdueItems->count() }} Item
                    </span>
                @endif
            </div>
            
            <div class="space-y-3">
                @forelse($overdueItems ?? [] as $item)
                    <div class="flex items-start space-x-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900">{{ $item['title'] }}</p>
                            <p class="text-xs text-red-600">Terlambat {{ \Carbon\Carbon::parse($item['due_date'])->diffForHumans() }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-medium text-slate-900">Rp {{ number_format($item['amount'], 0, ',', '.') }}</p>
                            <a href="{{ $item['url'] }}" 
                               class="text-xs text-blue-600 hover:text-blue-800">Lihat Detail</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-green-600">Tidak ada penagihan yang terlambat</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Simple Upcoming Due Dates -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Jatuh Tempo Mendatang</h2>
                <p class="text-sm text-gray-600">Penagihan dalam 7 hari ke depan</p>
            </div>
            @if(isset($upcomingDueDates) && $upcomingDueDates->count() > 0)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    {{ $upcomingDueDates->count() }} Item
                </span>
            @endif
        </div>
        
        @if(isset($upcomingDueDates) && $upcomingDueDates->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Proyek</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($upcomingDueDates ?? [] as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $item['type'] }}</div>
                                    <div class="text-sm text-slate-500">{{ $item['title'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ $item['title'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y') }}</div>
                                    <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($item['due_date'])->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Upcoming
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ $item['url'] }}"
                                       class="text-blue-600 hover:text-blue-900">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden mobile-table-container p-4">
                @foreach($upcomingDueDates ?? [] as $item)
                    <div class="mobile-table-card mobile-fade-in">
                        <div class="mobile-table-card-header">
                            <div class="mobile-table-card-main">
                                <div class="mobile-table-card-title">{{ $item['type'] }}</div>
                                <div class="mobile-table-card-subtitle">{{ $item['title'] }}</div>
                            </div>
                            <div class="mobile-table-card-meta">
                                <div class="mobile-table-card-amount text-slate-900">
                                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                </div>
                                <div class="mobile-table-card-status">
                                    <span class="mobile-badge-warning">Upcoming</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mobile-table-card-body">
                            <div class="mobile-table-card-row">
                                <span class="mobile-table-card-label">Jatuh Tempo</span>
                                <span class="mobile-table-card-value">
                                    {{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y') }}
                                    <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($item['due_date'])->diffForHumans() }}</div>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mobile-table-card-actions">
                            <a href="{{ $item['url'] }}"
                               class="mobile-table-card-action primary" title="Lihat Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mobile-empty-state">
                <svg class="mobile-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4a2 2 0 00-2-2H10a2 2 0 00-2 2z"/>
                </svg>
                <p class="mobile-empty-state-description">Tidak ada penagihan yang akan jatuh tempo dalam 7 hari ke depan</p>
            </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile date filter toggle functionality
    window.toggleDateFilter = function() {
        const content = document.getElementById('date-filter-content');
        const toggleText = document.getElementById('date-filter-toggle-text');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            toggleText.textContent = 'Sembunyikan Filter';
        } else {
            content.classList.add('hidden');
            toggleText.textContent = 'Tampilkan Filter';
        }
    };

    // Date range quick select functions
    window.setDateRange = function(period) {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const today = new Date();
        let startDate, endDate;

        switch(period) {
            case 'today':
                startDate = endDate = new Date(today);
                break;
            case 'this_week':
                const dayOfWeek = today.getDay();
                startDate = new Date(today);
                startDate.setDate(today.getDate() - dayOfWeek);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'this_year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
                break;
        }

        startDateInput.value = startDate.toISOString().split('T')[0];
        endDateInput.value = endDate.toISOString().split('T')[0];
        
        // Auto-submit form
        startDateInput.closest('form').submit();
    };

    // Refresh dashboard functionality
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        // Show loading state
        button.innerHTML = `
            <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Memuat...
        `;
        button.disabled = true;
        
        // Reload page after short delay
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });

    // Mobile responsive adjustments
    function handleResize() {
        const isMobile = window.innerWidth < 640;
        const dateFilterContent = document.getElementById('date-filter-content');
        
        if (!isMobile) {
            // Show filter content on desktop
            dateFilterContent.classList.remove('hidden');
        } else {
            // Hide filter content on mobile by default
            if (!dateFilterContent.classList.contains('hidden')) {
                // Only hide if it wasn't manually opened
                const hasActiveFilters = document.querySelector('form input[name="start_date"]').value ||
                                        document.querySelector('form input[name="end_date"]').value;
                
                if (!hasActiveFilters) {
                    dateFilterContent.classList.add('hidden');
                    document.getElementById('date-filter-toggle-text').textContent = 'Tampilkan Filter';
                }
            }
        }
    }

    // Initial call and resize listener
    handleResize();
    window.addEventListener('resize', handleResize);

    // Add loading states for better UX
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Memuat...
                `;
                submitBtn.disabled = true;
            }
        });
    });

    // Add notification for overdue items
    const overdueCount = {{ isset($overdueItems) ? $overdueItems->count() : 0 }};
    if (overdueCount > 0) {
        // Show notification after 2 seconds
        setTimeout(() => {
            if (Notification.permission === 'granted') {
                new Notification('Penagihan Terlambat', {
                    body: `Terdapat ${overdueCount} penagihan yang terlambat`,
                    icon: '/favicon.ico'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('Penagihan Terlambat', {
                            body: `Terdapat ${overdueCount} penagihan yang terlambat`,
                            icon: '/favicon.ico'
                        });
                    }
                });
            }
        }, 2000);
    }

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + R for refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            document.getElementById('refreshDashboard').click();
        }
        
        // Ctrl/Cmd + N for new billing
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = "{{ route('project-billings.create') }}";
        }
    });
});
</script>

@endsection