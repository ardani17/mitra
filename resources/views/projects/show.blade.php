@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 truncate">{{ $project->name }}</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Kode: {{ $project->code }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <a href="{{ route('projects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Kembali ke Proyek</span>
                <span class="sm:hidden">Kembali</span>
            </a>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Edit Proyek</span>
                <span class="sm:hidden">Edit</span>
            </a>
            @endcan
            @can('delete', $project)
            <a href="{{ route('projects.confirm-delete', $project) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Hapus Proyek</span>
                <span class="sm:hidden">Hapus</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Informasi Proyek -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Proyek</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Kode:</span>
                    <p class="font-medium">{{ $project->code }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Tipe:</span>
                    <p class="font-medium">{{ ucfirst($project->type) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($project->status == 'completed') bg-green-100 text-green-800
                          @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                          @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                          @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>
                @if($project->location)
                <div>
                    <span class="text-sm text-gray-600">Lokasi:</span>
                    <p class="font-medium">{{ $project->location }}</p>
                </div>
                @endif
                @if($project->client)
                <div>
                    <span class="text-sm text-gray-600">Client:</span>
                    <p class="font-medium">{{ $project->client }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Nilai Plan -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Nilai Plan</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Budget Plan:</span>
                    <p class="font-medium text-blue-600">Rp {{ number_format($project->planned_budget, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Nilai Jasa:</span>
                    <p class="font-medium">Rp {{ number_format($project->planned_service_value, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Nilai Material:</span>
                    <p class="font-medium">Rp {{ number_format($project->planned_material_value, 0, ',', '.') }}</p>
                </div>
                <div class="border-t pt-2">
                    <span class="text-sm text-gray-600">Total Plan:</span>
                    <p class="font-bold text-lg">Rp {{ number_format($project->planned_total_value, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Nilai Akhir -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Nilai Akhir</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Nilai Jasa:</span>
                    <p class="font-medium text-green-600">Rp {{ number_format($project->final_service_value ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Nilai Material:</span>
                    <p class="font-medium">Rp {{ number_format($project->final_material_value ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="border-t pt-2">
                    <span class="text-sm text-gray-600">Total Akhir:</span>
                    <p class="font-bold text-lg text-green-600">Rp {{ number_format($project->final_total_value ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Timeline</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Tanggal Mulai:</span>
                    <p class="font-medium">{{ $project->start_date ? $project->start_date->format('d M Y') : 'Belum ditentukan' }}</p>
                    @if($project->start_date)
                    <p class="text-xs text-gray-500">{{ $project->start_date->diffForHumans() }}</p>
                    @endif
                </div>
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    @if($project->end_date && $project->end_date->isPast())
                        <span class="text-green-600 font-medium">Selesai</span>
                    @elseif($project->end_date)
                        <span class="text-orange-600 font-medium">Tanggal selesai belum ditentukan</span>
                    @else
                        <span class="text-gray-600">Tanggal selesai belum ditentukan</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Tagihan -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 sm:mb-8">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Tagihan</h3>
        
        @php
            $billingInfo = $project->billing_info;
        @endphp
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Status Tagihan -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Status Tagihan</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $project->billing_status_badge_color }}">
                        {{ $project->billing_status_label }}
                    </span>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs text-gray-500">
                            {{ $billingInfo['type'] === 'batch' ? 'Progress Verifikasi' : 'Progress Pembayaran' }}
                        </span>
                        <span class="text-xs font-medium text-gray-700">{{ $project->billing_progress_percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->billing_progress_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $billingInfo['type'] === 'batch' ? 'Tahapan verifikasi billing' : 'Persentase pembayaran termin' }}
                    </p>
                    @if($billingInfo['type'] === 'direct' && $billingInfo['billing_count'] > 0)
                    <p class="text-xs text-gray-400 mt-1">
                        @php
                            $totalToBePaid = $project->final_total_value && $project->final_total_value > 0
                                ? $project->final_total_value
                                : ($project->planned_total_value ?? 0);
                            $totalPaid = $project->total_received_amount; // Total termin yang sudah lunas
                        @endphp
                        Dibayar: Rp {{ number_format($totalPaid, 0, ',', '.') }} dari Rp {{ number_format($totalToBePaid, 0, ',', '.') }}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Total Diterima -->
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-green-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ $project->total_tagihan_label }}</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($project->total_received_amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $billingInfo['type'] === 'batch' ? 'Dari Billing Batch' : 'Dari Tagihan Termin' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dokumen Tagihan -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Dokumen Terakhir</p>
                        @if($project->latest_billing_info['invoice_number'])
                            <p class="text-sm font-medium text-blue-600">{{ $project->latest_billing_info['invoice_number'] }}</p>
                            @if($project->latest_billing_info['billing_date'])
                                <p class="text-xs text-gray-500">{{ $project->latest_billing_info['billing_date']->format('d M Y') }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $project->latest_billing_info['source'] === 'batch' ? 'Billing Batch' : 'Tagihan Termin' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500">Belum ada tagihan</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>



        <!-- Detail Dokumen Tagihan Terakhir -->
        @if($project->latest_billing_info['invoice_number'])
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-md font-semibold text-gray-700 mb-3">Detail Dokumen Tagihan Terakhir</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if($project->latest_billing_info['sp_number'])
                <div>
                    <span class="text-sm text-gray-600">Nomor SP:</span>
                    <p class="font-medium">{{ $project->latest_billing_info['sp_number'] }}</p>
                </div>
                @endif
                @if($project->latest_billing_info['invoice_number'])
                <div>
                    <span class="text-sm text-gray-600">Nomor Faktur:</span>
                    <p class="font-medium">{{ $project->latest_billing_info['invoice_number'] }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm text-gray-600">Status Saat Ini:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $project->billing_status_badge_color }}">
                        {{ $project->latest_billing_info['status_label'] }}
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Sumber:</span>
                    <p class="font-medium">{{ $project->latest_billing_info['source'] === 'batch' ? 'Billing Batch' : 'Tagihan Termin' }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Ringkasan Keuangan -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 sm:mb-8">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Ringkasan Keuangan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Pengeluaran</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($project->total_expenses, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-green-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ $project->total_tagihan_label }}</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($project->total_tagihan_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-red-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Laba Bersih</p>
                        @php
                            $netProfit = $project->total_tagihan_amount - $project->total_expenses;
                        @endphp
                        <p class="text-xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Margin Keuntungan</p>
                        @php
                            $totalTagihan = $project->total_tagihan_amount;
                            $marginPercentage = $totalTagihan > 0 ? ($netProfit / $totalTagihan) * 100 : 0;
                        @endphp
                        <p class="text-xl font-bold text-purple-600">{{ number_format($marginPercentage, 2) }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <!-- Mobile Tab Dropdown -->
            <div class="sm:hidden px-4 py-3">
                <select id="mobileTabSelect" onchange="showTab(this.value)" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="ringkasan">Ringkasan</option>
                    <option value="timeline">Timeline</option>
                    <option value="pengeluaran">Pengeluaran</option>
                    <option value="pembayaran">Jadwal Pembayaran</option>
                    <option value="aktivitas">Aktivitas</option>
                    <option value="dokumen">Dokumen</option>
                </select>
            </div>
            
            <!-- Desktop Tab Navigation -->
            <nav class="hidden sm:flex -mb-px space-x-4 lg:space-x-8 px-4 sm:px-6 overflow-x-auto" aria-label="Tabs">
                <button onclick="showTab('ringkasan')" id="tab-ringkasan" class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Ringkasan
                </button>
                <button onclick="showTab('timeline')" id="tab-timeline" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Timeline
                </button>
                <button onclick="showTab('pengeluaran')" id="tab-pengeluaran" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Pengeluaran
                </button>
                <button onclick="showTab('pembayaran')" id="tab-pembayaran" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Jadwal Pembayaran
                </button>
                <button onclick="showTab('aktivitas')" id="tab-aktivitas" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Aktivitas
                </button>
                <button onclick="showTab('dokumen')" id="tab-dokumen" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Dokumen
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-4 sm:p-6">
            <!-- Tab Ringkasan -->
            <div id="content-ringkasan" class="tab-content">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Progress</h3>
                
                <!-- Progress Keseluruhan -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-blue-700">PROGRESS KESELURUHAN</span>
                        <span class="text-sm font-medium text-blue-700">{{ $project->progress_percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                    </div>
                    @if($project->timelines->count() > 0)
                    <p class="text-xs text-gray-500 mt-1">Berdasarkan {{ $project->timelines->count() }} milestone timeline</p>
                    @endif
                </div>

                <!-- Status Distribusi Timeline -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Status Distribusi Timeline</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Direncanakan</span>
                            <span class="text-sm font-medium">{{ $project->timeline_status_distribution['planned'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Sedang Berjalan</span>
                            <span class="text-sm font-medium">{{ $project->timeline_status_distribution['in_progress'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Selesai</span>
                            <span class="text-sm font-medium">{{ $project->timeline_status_distribution['completed'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Terlambat</span>
                            <span class="text-sm font-medium">{{ $project->timeline_status_distribution['delayed'] }}</span>
                        </div>
                    </div>
                    @if($project->timelines->count() == 0)
                    <p class="text-xs text-gray-500 mt-2">Belum ada timeline yang dibuat</p>
                    @endif
                </div>

                <!-- Deskripsi Proyek -->
                @if($project->description)
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Deskripsi Proyek</h4>
                    <p class="text-gray-600">{{ $project->description }}</p>
                </div>
                @endif
            </div>

            <!-- Tab Timeline -->
            <div id="content-timeline" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Timeline Proyek</h3>
                    @can('create', App\Models\ProjectTimeline::class)
                    <a href="{{ route('timelines.create', ['project' => $project->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Tambah Timeline
                    </a>
                    @endcan
                </div>
                
                @if($project->timelines->count() > 0)
                <div class="space-y-4">
                    @foreach($project->timelines as $timeline)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">{{ $timeline->milestone }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $timeline->description }}</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $timeline->planned_date->format('d M Y') }}
                                        @if($timeline->actual_date)
                                            → {{ $timeline->actual_date->format('d M Y') }}
                                        @endif
                                    </span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                          @if($timeline->status == 'completed') bg-green-100 text-green-800
                                          @elseif($timeline->status == 'in_progress') bg-blue-100 text-blue-800
                                          @elseif($timeline->status == 'delayed') bg-red-100 text-red-800
                                          @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $timeline->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <!-- Progress Display -->
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-700">{{ $timeline->progress_percentage }}%</div>
                                    <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $timeline->progress_percentage }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-2">
                                    <!-- View Button -->
                                    <button onclick="viewTimeline({{ $timeline->id }})" class="text-blue-600 hover:text-blue-800 p-1 rounded" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Edit Button -->
                                    @can('update', $timeline)
                                    <a href="{{ route('timelines.edit', $timeline) }}" class="text-green-600 hover:text-green-800 p-1 rounded" title="Edit Timeline">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endcan
                                    
                                    <!-- Delete Button -->
                                    @can('delete', $timeline)
                                    <button onclick="deleteTimeline({{ $timeline->id }})" class="text-red-600 hover:text-red-800 p-1 rounded" title="Hapus Timeline">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada timeline</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan timeline proyek.</p>
                    <div class="mt-4">
                        <a href="{{ route('timelines.create', ['project' => $project->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Tambah Timeline Pertama
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Tab Pengeluaran -->
            <div id="content-pengeluaran" class="tab-content hidden">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-800">Pengeluaran Proyek</h3>
                    @can('create', App\Models\ProjectExpense::class)
                    <a href="{{ route('expenses.create', ['project' => $project->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                        <span class="hidden sm:inline">Tambah Pengeluaran</span>
                        <span class="sm:hidden">Tambah</span>
                    </a>
                    @endcan
                </div>
                
                @if($project->expenses->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($project->expenses->take(10) as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $expense->expense_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit($expense->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($expense->category) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                              @if($expense->status == 'approved') bg-green-100 text-green-800
                                              @elseif($expense->status == 'rejected') bg-red-100 text-red-800
                                              @elseif($expense->status == 'submitted') bg-yellow-100 text-yellow-800
                                              @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="block sm:hidden space-y-4">
                    @foreach($project->expenses->take(10) as $expense)
                    <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">{{ Str::limit($expense->description, 40) }}</h4>
                                <p class="text-xs text-gray-500">{{ $expense->expense_date->format('d M Y') }}</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2
                                  @if($expense->status == 'approved') bg-green-100 text-green-800
                                  @elseif($expense->status == 'rejected') bg-red-100 text-red-800
                                  @elseif($expense->status == 'submitted') bg-yellow-100 text-yellow-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">Kategori:</span>
                                <p class="font-medium text-gray-900">{{ ucfirst($expense->category) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Jumlah:</span>
                                <p class="font-medium text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        
                        @if(strlen($expense->description) > 40)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-600">{{ $expense->description }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                @if($project->expenses->count() > 10)
                <div class="mt-4 sm:mt-6 text-center">
                    <a href="{{ route('expenses.index', ['project_id' => $project->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Lihat semua pengeluaran ({{ $project->expenses->count() }})
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-8 sm:py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pengeluaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan pengeluaran proyek.</p>
                    @can('create', App\Models\ProjectExpense::class)
                    <div class="mt-4">
                        <a href="{{ route('expenses.create', ['project' => $project->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Tambah Pengeluaran Pertama
                        </a>
                    </div>
                    @endcan
                </div>
                @endif
            </div>

            <!-- Tab Aktivitas -->
            <div id="content-aktivitas" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Proyek</h3>
                
                @if($allActivities->count() > 0)
                <!-- Activities Container with Pagination -->
                <div id="activitiesContainer">
                    <div id="activitiesList" class="space-y-4">
                        <!-- Activities will be populated by JavaScript -->
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Menampilkan <span id="currentRange">1-5</span> dari <span id="totalActivities">{{ $allActivities->count() }}</span> aktivitas
                        </div>
                        <div class="flex items-center space-x-2">
                            <button id="prevBtn" onclick="changePage(-1)" class="px-3 py-1 text-sm bg-gray-200 text-gray-600 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Sebelumnya
                            </button>
                            <span id="pageInfo" class="text-sm text-gray-600">Halaman 1 dari 1</span>
                            <button id="nextBtn" onclick="changePage(1)" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                Selanjutnya
                                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden data for JavaScript -->
                <script type="application/json" id="activitiesData">
                    {!! json_encode($allActivities->values()) !!}
                </script>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas</h3>
                    <p class="mt-1 text-sm text-gray-500">Aktivitas proyek akan muncul di sini secara otomatis.</p>
                </div>
                @endif
            </div>

            <!-- Tab Pembayaran -->
            <div id="content-pembayaran" class="tab-content hidden">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-800">Jadwal Pembayaran Termin</h3>
                    @can('create', App\Models\ProjectBilling::class)
                    <a href="{{ route('project-billings.manage-schedule', $project) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                        <span class="hidden sm:inline">Kelola Jadwal Pembayaran</span>
                        <span class="sm:hidden">Kelola Jadwal</span>
                    </a>
                    @endcan
                </div>

                <!-- Filter Jadwal Pembayaran -->
                <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row gap-3 sm:gap-2">
                    <select id="scheduleStatusFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="billed">Ditagih</option>
                        <option value="paid">Dibayar</option>
                        <option value="overdue">Terlambat</option>
                    </select>
                    <input type="text" id="scheduleSearch" placeholder="Cari jadwal..." class="border border-gray-300 rounded-md px-3 py-2 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Desktop Table View -->
                <div id="schedulesContainer" class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Termin</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Persentase</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="schedulesList" class="bg-white divide-y divide-gray-200">
                                <!-- Jadwal pembayaran akan dimuat melalui AJAX -->
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-2">Memuat jadwal pembayaran...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div id="schedulesContainerMobile" class="block sm:hidden">
                    <div id="schedulesListMobile" class="space-y-4">
                        <!-- Mobile cards will be populated by JavaScript -->
                        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                            <div class="flex items-center justify-center py-8">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Memuat jadwal pembayaran...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Jadwal Pembayaran -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                        <h4 class="text-sm font-medium text-gray-500">Total Termin</h4>
                        <div class="mt-2 flex justify-between items-end">
                            <p class="text-2xl font-bold text-gray-800" id="totalTermin">0</p>
                            <p class="text-sm text-gray-500" id="totalAmount">Rp 0</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                        <h4 class="text-sm font-medium text-gray-500">Pending</h4>
                        <div class="mt-2 flex justify-between items-end">
                            <p class="text-2xl font-bold text-gray-800" id="pendingTermin">0</p>
                            <p class="text-sm text-gray-500" id="pendingAmount">Rp 0</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                        <h4 class="text-sm font-medium text-gray-500">Dibayar</h4>
                        <div class="mt-2 flex justify-between items-end">
                            <p class="text-2xl font-bold text-gray-800" id="paidTermin">0</p>
                            <p class="text-sm text-gray-500" id="paidAmount">Rp 0</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                        <h4 class="text-sm font-medium text-gray-500">Terlambat</h4>
                        <div class="mt-2 flex justify-between items-end">
                            <p class="text-2xl font-bold text-gray-800" id="overdueTermin">0</p>
                            <p class="text-sm text-gray-500" id="overdueAmount">Rp 0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Dokumen -->
            <div id="content-dokumen" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Dokumen Proyek</h3>
                    <button onclick="openUploadModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload Dokumen
                    </button>
                </div>

                <!-- Filter Dokumen -->
                <div class="mb-4 flex space-x-4">
                    <select id="documentTypeFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">Semua Tipe</option>
                        <option value="contract">Kontrak</option>
                        <option value="technical">Teknis</option>
                        <option value="financial">Keuangan</option>
                        <option value="report">Laporan</option>
                        <option value="other">Lainnya</option>
                    </select>
                    <input type="text" id="documentSearch" placeholder="Cari dokumen..." class="border border-gray-300 rounded-md px-3 py-2 text-sm flex-1">
                </div>
                
                @if($project->documents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="documentsGrid">
                    @foreach($project->documents as $document)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow document-item" data-type="{{ $document->document_type }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3 flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 {{ $document->file_icon }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $document->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $document->original_name }}</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $document->document_type_label }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $document->formatted_file_size }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Oleh {{ $document->uploader->name }} • {{ $document->created_at->diffForHumans() }}
                                    </p>
                                    @if($document->description)
                                    <p class="text-xs text-gray-600 mt-2">{{ Str::limit($document->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-2">
                                <div class="relative">
                                    <button onclick="toggleDocumentMenu({{ $document->id }})" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"></path>
                                        </svg>
                                    </button>
                                    <div id="menu-{{ $document->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                        <a href="{{ route('documents.show', $document) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Lihat
                                        </a>
                                        <a href="{{ route('documents.download', $document) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Download
                                        </a>
                                        @can('delete', $document)
                                        <button onclick="deleteDocument({{ $document->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Hapus
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada dokumen</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan mengunggah dokumen proyek.</p>
                    <button onclick="openUploadModal()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload Dokumen Pertama
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Dokumen -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upload Dokumen</h3>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Dokumen</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Dokumen</label>
                        <select name="document_type" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Tipe</option>
                            <option value="contract">Kontrak</option>
                            <option value="technical">Teknis</option>
                            <option value="financial">Keuangan</option>
                            <option value="report">Laporan</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File</label>
                        <input type="file" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Maksimal 10MB. Format: PDF, DOC, XLS, JPG, PNG, ZIP</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeUploadModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

// Document upload modal
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
}

// Document menu toggle
function toggleDocumentMenu(documentId) {
    const menu = document.getElementById('menu-' + documentId);
    menu.classList.toggle('hidden');
}

// Upload form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("documents.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Terjadi kesalahan saat upload dokumen');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat upload dokumen');
    });
});

// Delete document
function deleteDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
        fetch(`/documents/${documentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Terjadi kesalahan saat menghapus dokumen');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus dokumen');
        });
    }
}

// Document filtering
document.getElementById('documentTypeFilter').addEventListener('change', filterDocuments);
document.getElementById('documentSearch').addEventListener('input', filterDocuments);

function filterDocuments() {
    const typeFilter = document.getElementById('documentTypeFilter').value;
    const searchFilter = document.getElementById('documentSearch').value.toLowerCase();
    const documents = document.querySelectorAll('.document-item');
    
    documents.forEach(doc => {
        const type = doc.getAttribute('data-type');
        const text = doc.textContent.toLowerCase();
        
        const typeMatch = !typeFilter || type === typeFilter;
        const searchMatch = !searchFilter || text.includes(searchFilter);
        
        if (typeMatch && searchMatch) {
            doc.style.display = 'block';
        } else {
            doc.style.display = 'none';
        }
    });
}

// Timeline functions
function viewTimeline(timelineId) {
    // Redirect to timeline show page
    window.location.href = `/timelines/${timelineId}`;
}

function deleteTimeline(timelineId) {
    if (confirm('Apakah Anda yakin ingin menghapus timeline ini? Tindakan ini tidak dapat dibatalkan.')) {
        fetch(`/timelines/${timelineId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Timeline berhasil dihapus');
                location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan saat menghapus timeline');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus timeline');
        });
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Activities Pagination
let activitiesData = [];
let currentPage = 1;
const itemsPerPage = 5;

// Initialize activities pagination when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const activitiesDataElement = document.getElementById('activitiesData');
    if (activitiesDataElement) {
        try {
            activitiesData = JSON.parse(activitiesDataElement.textContent);
            displayActivities();
        } catch (e) {
            console.error('Error parsing activities data:', e);
        }
    }
});

function displayActivities() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentActivities = activitiesData.slice(startIndex, endIndex);
    
    const activitiesList = document.getElementById('activitiesList');
    if (!activitiesList) return;
    
    activitiesList.innerHTML = '';
    
    currentActivities.forEach(activity => {
        const activityElement = createActivityElement(activity);
        activitiesList.appendChild(activityElement);
    });
    
    updatePaginationControls();
}

function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'flex items-start space-x-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors';
    
    const iconSvg = getActivityIcon(activity.icon, activity.color);
    const createdAt = new Date(activity.created_at);
    const timeAgo = getTimeAgo(createdAt);
    const formattedDate = createdAt.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let additionalInfo = '';
    
    // Add specific information based on activity type
    if (activity.notes) {
        additionalInfo += `<p class="text-xs text-gray-600 mt-2 italic">Catatan: ${activity.notes}</p>`;
    }
    
    if (activity.type === 'billing_status' && activity.batch_code) {
        additionalInfo += `<div class="mt-2 text-xs text-gray-600"><span class="font-medium">Batch:</span> ${activity.batch_code}</div>`;
    }
    
    if (activity.type === 'expense_approval' && activity.amount) {
        additionalInfo += `<div class="mt-2 text-xs text-gray-600"><span class="font-medium">Jumlah:</span> Rp ${formatNumber(activity.amount)}</div>`;
    }
    
    if (activity.type === 'timeline_update' && activity.progress !== undefined) {
        additionalInfo += `<div class="mt-2 text-xs text-gray-600"><span class="font-medium">Progress:</span> ${activity.progress}%</div>`;
    }
    
    if (activity.type === 'expense_created' && activity.amount) {
        additionalInfo += `<div class="mt-2 flex items-center justify-between text-xs text-gray-600">
            <span><span class="font-medium">Jumlah:</span> Rp ${formatNumber(activity.amount)}</span>
            <span><span class="font-medium">Kategori:</span> ${activity.category ? activity.category.charAt(0).toUpperCase() + activity.category.slice(1) : ''}</span>
        </div>`;
    }
    
    div.innerHTML = `
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-${activity.color}-100 rounded-full flex items-center justify-center">
                ${iconSvg}
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900">${activity.title}</h4>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-${activity.color}-100 text-${activity.color}-800">
                    ${activity.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </span>
            </div>
            <p class="text-sm text-gray-700 mt-1">${activity.description}</p>
            ${additionalInfo}
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-gray-500">
                    <span class="font-medium">${activity.user}</span> • ${timeAgo}
                </p>
                <p class="text-xs text-gray-400">
                    ${formattedDate}
                </p>
            </div>
        </div>
    `;
    
    return div;
}

function getActivityIcon(icon, color) {
    const iconClass = `w-5 h-5 text-${color}-600`;
    
    switch (icon) {
        case 'activity':
            return `<svg class="${iconClass}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6Z"></path>
            </svg>`;
        case 'billing':
            return `<svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`;
        case 'money':
            return `<svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>`;
        case 'calendar':
            return `<svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>`;
        case 'document':
            return `<svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`;
        case 'expense':
            return `<svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>`;
        default:
            return `<svg class="${iconClass}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6Z"></path>
            </svg>`;
    }
}

function updatePaginationControls() {
    const totalPages = Math.ceil(activitiesData.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage + 1;
    const endIndex = Math.min(currentPage * itemsPerPage, activitiesData.length);
    
    // Update range display
    document.getElementById('currentRange').textContent = `${startIndex}-${endIndex}`;
    document.getElementById('totalActivities').textContent = activitiesData.length;
    
    // Update page info
    document.getElementById('pageInfo').textContent = `Halaman ${currentPage} dari ${totalPages}`;
    
    // Update button states
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    
    if (prevBtn.disabled) {
        prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
        prevBtn.classList.remove('hover:bg-gray-300');
    } else {
        prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        prevBtn.classList.add('hover:bg-gray-300');
    }
    
    if (nextBtn.disabled) {
        nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        nextBtn.classList.remove('hover:bg-blue-700');
    } else {
        nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        nextBtn.classList.add('hover:bg-blue-700');
    }
}

function changePage(direction) {
    const totalPages = Math.ceil(activitiesData.length / itemsPerPage);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        displayActivities();
    }
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return `${diffInSeconds} detik yang lalu`;
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} menit yang lalu`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} jam yang lalu`;
    } else if (diffInSeconds < 2592000) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} hari yang lalu`;
    } else if (diffInSeconds < 31536000) {
        const months = Math.floor(diffInSeconds / 2592000);
        return `${months} bulan yang lalu`;
    } else {
        const years = Math.floor(diffInSeconds / 31536000);
        return `${years} tahun yang lalu`;
    }
}

// Jadwal Pembayaran Tab Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load payment schedules when tab is clicked
    const pembayaranTab = document.getElementById('tab-pembayaran');
    if (pembayaranTab) {
        pembayaranTab.addEventListener('click', function() {
            loadPaymentSchedules();
        });
    }
    
    // Load payment schedules if tab is active on page load
    if (window.location.hash === '#pembayaran') {
        showTab('pembayaran');
        loadPaymentSchedules();
    }
    
    // Add event listeners for filters
    const scheduleStatusFilter = document.getElementById('scheduleStatusFilter');
    const scheduleSearch = document.getElementById('scheduleSearch');
    
    if (scheduleStatusFilter) {
        scheduleStatusFilter.addEventListener('change', loadPaymentSchedules);
    }
    
    if (scheduleSearch) {
        scheduleSearch.addEventListener('input', debounce(loadPaymentSchedules, 500));
    }
});

// Debounce function to limit API calls during search
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}

// Load payment schedules from API
function loadPaymentSchedules() {
    const projectId = {{ $project->id }};
    const status = document.getElementById('scheduleStatusFilter').value;
    const search = document.getElementById('scheduleSearch').value;
    
    // Show loading state for desktop
    document.getElementById('schedulesList').innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2">Memuat jadwal pembayaran...</p>
            </td>
        </tr>
    `;
    
    // Show loading state for mobile
    document.getElementById('schedulesListMobile').innerHTML = `
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-500">Memuat jadwal pembayaran...</span>
            </div>
        </div>
    `;
    
    // Fetch payment schedules
    fetch(`/api/projects/${projectId}/schedules?status=${status}&search=${search}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            displayPaymentSchedules(data);
            loadPaymentScheduleStats(projectId);
        })
        .catch(error => {
            console.error('Error fetching payment schedules:', error);
            
            // Show error state for desktop
            document.getElementById('schedulesList').innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-red-500">
                        <svg class="h-6 w-6 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2">Gagal memuat jadwal pembayaran. Silakan coba lagi.</p>
                    </td>
                </tr>
            `;
            
            // Show error state for mobile
            document.getElementById('schedulesListMobile').innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200 text-center">
                    <svg class="h-6 w-6 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-red-500">Gagal memuat jadwal pembayaran. Silakan coba lagi.</p>
                </div>
            `;
        });
}

// Display payment schedules in the table and mobile cards
function displayPaymentSchedules(schedules) {
    const schedulesList = document.getElementById('schedulesList');
    const schedulesListMobile = document.getElementById('schedulesListMobile');
    
    if (schedules.length === 0) {
        // Desktop empty state
        schedulesList.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada jadwal pembayaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan jadwal pembayaran termin.</p>
                </td>
            </tr>
        `;
        
        // Mobile empty state
        schedulesListMobile.innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada jadwal pembayaran</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan jadwal pembayaran termin.</p>
            </div>
        `;
        return;
    }
    
    let desktopHtml = '';
    let mobileHtml = '';
    
    schedules.forEach(schedule => {
        const dueDate = new Date(schedule.due_date);
        const formattedDueDate = dueDate.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        
        // Determine status class and label
        let statusClass = '';
        let statusLabel = '';
        
        switch (schedule.status) {
            case 'pending':
                // Check if overdue
                if (new Date(schedule.due_date) < new Date()) {
                    statusClass = 'bg-red-100 text-red-800';
                    statusLabel = 'Terlambat';
                } else {
                    statusClass = 'bg-yellow-100 text-yellow-800';
                    statusLabel = 'Pending';
                }
                break;
            case 'billed':
                statusClass = 'bg-blue-100 text-blue-800';
                statusLabel = 'Ditagih';
                break;
            case 'paid':
                statusClass = 'bg-green-100 text-green-800';
                statusLabel = 'Dibayar';
                break;
            default:
                statusClass = 'bg-gray-100 text-gray-800';
                statusLabel = schedule.status;
        }
        
        // Desktop table row
        desktopHtml += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${schedule.termin_name}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formattedDueDate}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${schedule.percentage}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    Rp ${formatNumber(schedule.amount)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                        ${statusLabel}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex space-x-2">
                        <button onclick="viewScheduleDetails(${schedule.id})" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        ${schedule.status === 'pending' ? `
                            <button onclick="createBillingFromSchedule(${schedule.id})" class="text-green-600 hover:text-green-900" title="Buat Tagihan">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
        
        // Mobile card
        mobileHtml += `
            <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900 mb-1">${schedule.termin_name}</h4>
                        <p class="text-xs text-gray-500">Jatuh tempo: ${formattedDueDate}</p>
                    </div>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2 ${statusClass}">
                        ${statusLabel}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                    <div>
                        <span class="text-gray-500">Persentase:</span>
                        <p class="font-medium text-gray-900">${schedule.percentage}%</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jumlah:</span>
                        <p class="font-medium text-gray-900">Rp ${formatNumber(schedule.amount)}</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 pt-3 border-t border-gray-100">
                    <button onclick="viewScheduleDetails(${schedule.id})" class="inline-flex items-center px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 text-xs font-medium rounded transition-colors" title="Lihat Detail">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Detail
                    </button>
                    ${schedule.status === 'pending' ? `
                        <button onclick="createBillingFromSchedule(${schedule.id})" class="inline-flex items-center px-3 py-1 bg-green-50 hover:bg-green-100 text-green-600 hover:text-green-700 text-xs font-medium rounded transition-colors" title="Buat Tagihan">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Tagih
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    schedulesList.innerHTML = desktopHtml;
    schedulesListMobile.innerHTML = mobileHtml;
}

// Load payment schedule statistics
function loadPaymentScheduleStats(projectId) {
    // For now, show placeholder data since the API endpoint doesn't exist yet
    document.getElementById('totalTermin').textContent = '0';
    document.getElementById('pendingTermin').textContent = '0';
    document.getElementById('paidTermin').textContent = '0';
    document.getElementById('overdueTermin').textContent = '0';
    
    document.getElementById('totalAmount').textContent = 'Rp 0';
    document.getElementById('pendingAmount').textContent = 'Rp 0';
    document.getElementById('paidAmount').textContent = 'Rp 0';
    document.getElementById('overdueAmount').textContent = 'Rp 0';
}

// View schedule details
function viewScheduleDetails(scheduleId) {
    alert('Fitur detail jadwal pembayaran akan segera tersedia.');
}

// Create billing from schedule
function createBillingFromSchedule(scheduleId) {
    if (confirm('Apakah Anda yakin ingin membuat tagihan dari jadwal pembayaran ini?')) {
        // Redirect to project billing create page
        window.location.href = `{{ route('project-billings.create') }}?project_id={{ $project->id }}&schedule_id=${scheduleId}`;
    }
}
</script>
@endsection
