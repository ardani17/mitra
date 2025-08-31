
@extends('layouts.app')

@section('title', 'Dashboard Penagihan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Dashboard Penagihan</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Ringkasan dan analisis sistem penagihan</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <select id="period-filter" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_quarter">Kuartal Ini</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Custom</option>
            </select>
            <a href="{{ route('project-billings.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="hidden sm:inline">Buat Penagihan</span>
                <span class="sm:hidden">Tambah</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="billing-dashboard-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total Billings -->
        <div class="billing-summary-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-blue-100 text-xs sm:text-sm font-medium">Total Penagihan</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold">{{ $overallStats['total_billings'] ?? 0 }}</p>
                    <p class="text-blue-100 text-xs mt-1">
                        @if(isset($batchStats['total_batches']) && isset($projectStats['total_billings']))
                            {{ $batchStats['total_batches'] }} batch, {{ $projectStats['total_billings'] }} proyek
                        @else
                            Semua jenis penagihan
                        @endif
                    </p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="billing-summary-card bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-green-100 text-xs sm:text-sm font-medium">Total Nilai</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold">Rp {{ number_format($overallStats['total_amount'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-green-100 text-xs mt-1">
                        @if(isset($overallStats['payment_rate']))
                            {{ $overallStats['payment_rate'] }}% terbayar
                        @else
                            Total nilai penagihan
                        @endif
                    </p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Paid Amount -->
        <div class="billing-summary-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-purple-100 text-xs sm:text-sm font-medium">Sudah Dibayar</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold">Rp {{ number_format($overallStats['paid_amount'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-purple-100 text-xs mt-1">
                        @if(isset($additionalKpis['collection_efficiency']))
                            Efisiensi {{ $additionalKpis['collection_efficiency'] }}%
                        @else
                            Pembayaran diterima
                        @endif
                    </p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Outstanding Amount -->
        <div class="billing-summary-card bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-sm p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="metric-label text-orange-100 text-xs sm:text-sm font-medium">Belum Dibayar</p>
                    <p class="metric-value text-lg sm:text-2xl font-bold">Rp {{ number_format($overallStats['unpaid_amount'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-orange-100 text-xs mt-1">
                        @if(isset($overdueItems) && $overdueItems->count() > 0)
                            {{ $overdueItems->count() }} terlambat
                        @else
                            Menunggu pembayaran
                        @endif
                    </p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-2 sm:p-3 flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Type Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Batch Billing Card -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Penagihan Batch</h3>
                <div class="bg-blue-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Batch</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $batchStats['total_batches'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Nilai Total</span>
                    <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($batchStats['total_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Terbayar</span>
                    <span class="text-sm font-semibold text-green-600">Rp {{ number_format($batchStats['paid_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 border-t border-slate-200">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Pending</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $batchStats['pending_count'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Billing Card -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Penagihan Proyek</h3>
                <div class="bg-green-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Proyek</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $projectStats['active_projects'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Nilai Total</span>
                    <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($projectStats['total_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Terbayar</span>
                    <span class="text-sm font-semibold text-green-600">Rp {{ number_format($projectStats['paid_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 border-t border-slate-200">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Termin Aktif</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $projectStats['termin_payments'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Schedule Card -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Jadwal Pembayaran</h3>
                <div class="bg-purple-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Jadwal</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $terminStats['total_schedules'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Selesai</span>
                    <span class="text-sm font-semibold text-green-600">{{ $terminStats['paid_schedules'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Pending</span>
                    <span class="text-sm font-semibold text-yellow-600">{{ $terminStats['pending_schedules'] ?? 0 }}</span>
                </div>
                <div class="pt-3 border-t border-slate-200">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Terlambat</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $terminStats['overdue_schedules'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Monthly Billing Trend Chart -->
        <div class="mobile-chart-container bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="billing-chart-header flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="billing-chart-title text-base sm:text-lg font-semibold text-slate-800 mb-2 sm:mb-0">Tren Penagihan Bulanan</h3>
                <div class="billing-chart-controls flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-1"></div>
                        Batch
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Proyek
                    </span>
                </div>
            </div>
            <div class="mobile-chart-wrapper h-48 sm:h-64">
                <canvas id="monthlyBillingChart"></canvas>
            </div>
        </div>

        <!-- Billing Status Distribution -->
        <div class="mobile-chart-container bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="billing-chart-header flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="billing-chart-title text-base sm:text-lg font-semibold text-slate-800 mb-2 sm:mb-0">Distribusi Status</h3>
                <select id="status-type-filter" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="all">Semua</option>
                    <option value="batch">Batch</option>
                    <option value="project">Proyek</option>
                </select>
            </div>
            <div class="mobile-chart-wrapper h-48 sm:h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities & Overdue Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Aktivitas Terbaru</h3>
                <a href="{{ route('project-billings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center 
                                {{ $activity['status'] === 'paid' ? 'bg-green-100 text-green-600' : 
                                   ($activity['status'] === 'sent' ? 'bg-blue-100 text-blue-600' : 'bg-yellow-100 text-yellow-600') }}">
                                @if($activity['status'] === 'paid')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($activity['status'] === 'sent')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ Str::limit($activity['title'], 30) }}</p>
                                <p class="text-sm text-slate-600">{{ ucfirst($activity['type']) }}</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($activity['date'])->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">
                                Rp {{ number_format($activity['amount'], 0, ',', '.') }}
                            </p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $activity['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($activity['status'] === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($activity['status']) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>Belum ada aktivitas penagihan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Overdue Items -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Penagihan Terlambat</h3>
                @if(isset($overdueItems) && $overdueItems->count() > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ $overdueItems->count() }} Item
                    </span>
                @endif
            </div>
            <div class="space-y-3">
                @forelse($overdueItems ?? [] as $item)
                    <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ Str::limit($item['title'], 25) }}</p>
                                <p class="text-sm text-red-600">Terlambat {{ $item['days_overdue'] }} hari</p>
                                <p class="text-xs text-slate-500">Jatuh tempo: {{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">
                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                            </p>
                            <a href="{{ $item['url'] }}" class="text-xs text-blue-600 hover:text-blue-800">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Tidak ada penagihan yang terlambat</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Upcoming Due Dates -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Jatuh Tempo Mendatang</h3>
                <p class="text-sm text-slate-600">Penagihan dalam 7 hari ke depan</p>
            </div>
            @if(isset($upcomingDueDates) && $upcomingDueDates->count() > 0)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    {{ $upcomingDueDates->count() }} Item
                </span>
            @endif
        </div>
        
        @if(isset($upcomingDueDates) && $upcomingDueDates->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Penagihan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($upcomingDueDates as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $item['title'] }}</div>
                                    <div class="text-sm text-slate-500">{{ ucfirst($item['type']) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y') }}</div>
                                    <div class="text-sm text-slate-500">{{ $item['days_until_due'] }} hari lagi</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Akan Jatuh Tempo
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p>Tidak ada penagihan yang akan jatuh tempo dalam 7 hari ke depan</p>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('project-billings.create') }}"
               class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">Buat Penagihan</p>
                </div>
            </a>
            
            <a href="{{ route('billing-batches.create') }}"
               class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">Buat Batch</p>
                </div>
            </a>
            
            <a href="{{ route('billing-dashboard.export') }}"
               class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-purple-800">Export Data</p>
                </div>
            </a>
            
            <a href="{{ route('project-billings.index', ['status' => 'overdue']) }}"
               class="flex items-center justify-center p-4 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 text-red-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">Review Terlambat</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if chart elements exist
    const monthlyBillingChartEl = document.getElementById('monthlyBillingChart');
    const statusChartEl = document.getElementById('statusChart');
    
    if (!monthlyBillingChartEl || !statusChartEl) {
        console.error('Chart elements not found');
        return;
    }
    
    // Monthly Billing Trend Chart
    const monthlyCtx = monthlyBillingChartEl.getContext('2d');
    const monthlyTrends = @json($monthlyTrends ?? []);
    
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyTrends.map(item => item.month),
            datasets: [{
                label: 'Batch',
                data: monthlyTrends.map(item => item.batch_amount),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Proyek',
                data: monthlyTrends.map(item => item.project_amount),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    }
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

    // Status Distribution Chart
    const statusCtx = statusChartEl.getContext('2d');
    
    // Calculate status distribution with safe defaults
    const totalBillings = {{ $overallStats['total_billings'] ?? 0 }};
    const paymentRate = {{ $overallStats['payment_rate'] ?? 0 }};
    const paidCount = Math.round(totalBillings * paymentRate / 100);
    
    // Safe calculation for pending and overdue counts
    const batchPending = {{ $batchStats['pending_count'] ?? 0 }};
    const terminPending = {{ $terminStats['pending_schedules'] ?? 0 }};
    const pendingCount = batchPending + terminPending;
    
    const batchOverdue = {{ $batchStats['overdue_count'] ?? 0 }};
    const terminOverdue = {{ $terminStats['overdue_schedules'] ?? 0 }};
    const overdueCount = batchOverdue + terminOverdue;
    
    const sentCount = Math.max(0, totalBillings - paidCount - pendingCount - overdueCount);
    
    let statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Terbayar', 'Terkirim', 'Pending', 'Terlambat'],
            datasets: [{
                data: [paidCount, sentCount, pendingCount, overdueCount],
                backgroundColor: [
                    '#22c55e', '#3b82f6', '#eab308', '#ef4444'
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Status type filter
    const statusTypeFilter = document.getElementById('status-type-filter');
    if (statusTypeFilter) {
        statusTypeFilter.addEventListener('change', function() {
            const type = this.value;
            
            // Update chart based on filter
            // This would normally fetch data via AJAX
            if (statusChart) {
                statusChart.update();
            }
        });
    }

    // Period filter
    const periodFilter = document.getElementById('period-filter');
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            const period = this.value;
            
            if (period === 'custom') {
                // Show date picker modal or redirect to filtered page
                window.location.href = `/billing-dashboard?period=${period}`;
            } else {
                window.location.href = `/billing-dashboard?period=${period}`;
            }
        });
    }

    // Auto refresh every 5 minutes
    setInterval(function() {
        fetch('/billing-dashboard/data')
            .then(response => response.json())
            .then(data => {
                // Update dashboard data
                location.reload();
            });
    }, 300000); // 5 minutes
});
</script>

@endsection