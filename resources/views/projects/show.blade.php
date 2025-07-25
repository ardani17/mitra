@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $project->name }}</h1>
            <p class="text-gray-600 mt-1">Kode: {{ $project->code }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('projects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali ke Proyek
            </a>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Proyek
            </a>
            @endcan
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <!-- Informasi Proyek -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Proyek</h3>
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
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Nilai Plan</h3>
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
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Nilai Akhir</h3>
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
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline</h3>
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
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Tagihan</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                        <span class="text-xs text-gray-500">Progress Tagihan</span>
                        <span class="text-xs font-medium text-gray-700">{{ number_format($project->billing_percentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($project->billing_percentage, 100) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Total Ditagih -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Ditagih</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($project->total_billed_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Sisa Tagihan -->
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Sisa Tagihan</p>
                        <p class="text-xl font-bold text-orange-600">Rp {{ number_format($project->remaining_billable_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Dokumen Tagihan -->
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-2 bg-green-500 rounded-full text-white mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Dokumen Terakhir</p>
                        @if($project->latest_invoice_number)
                            <p class="text-sm font-medium text-green-600">{{ $project->latest_invoice_number }}</p>
                            @if($project->last_billing_date)
                                <p class="text-xs text-gray-500">{{ $project->last_billing_date->format('d M Y') }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-500">Belum ada tagihan</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Dokumen Tagihan -->
        @if($project->latest_billing_documents['invoice_number'])
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-md font-semibold text-gray-700 mb-3">Detail Dokumen Tagihan Terakhir</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($project->latest_po_number)
                <div>
                    <span class="text-sm text-gray-600">Nomor PO/SP:</span>
                    <p class="font-medium">{{ $project->latest_po_number }}</p>
                </div>
                @endif
                @if($project->latest_sp_number)
                <div>
                    <span class="text-sm text-gray-600">Nomor SP:</span>
                    <p class="font-medium">{{ $project->latest_sp_number }}</p>
                </div>
                @endif
                @if($project->latest_invoice_number)
                <div>
                    <span class="text-sm text-gray-600">Nomor Faktur:</span>
                    <p class="font-medium">{{ $project->latest_invoice_number }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Ringkasan Keuangan -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Keuangan</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <p class="text-sm text-gray-600">Total Pendapatan</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($project->revenues->sum('amount'), 0, ',', '.') }}</p>
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
                            $netProfit = $project->revenues->sum('amount') - $project->total_expenses;
                        @endphp
                        <p class="text-xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($netProfit, 0, ',', '.') }} {{ $netProfit < 0 ? '-' : '' }}
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
                            $totalRevenue = $project->revenues->sum('amount');
                            $marginPercentage = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
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
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('ringkasan')" id="tab-ringkasan" class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Ringkasan
                </button>
                <button onclick="showTab('timeline')" id="tab-timeline" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Timeline
                </button>
                <button onclick="showTab('pengeluaran')" id="tab-pengeluaran" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pengeluaran
                </button>
                <button onclick="showTab('penagihan')" id="tab-penagihan" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Penagihan
                </button>
                <button onclick="showTab('aktivitas')" id="tab-aktivitas" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Aktivitas
                </button>
                <button onclick="showTab('pendapatan')" id="tab-pendapatan" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pendapatan
                </button>
                <button onclick="showTab('dokumen')" id="tab-dokumen" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Dokumen
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
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
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Pengeluaran Proyek</h3>
                    @can('create', App\Models\ProjectExpense::class)
                    <a href="{{ route('expenses.create', ['project' => $project->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Tambah Pengeluaran
                    </a>
                    @endcan
                </div>
                
                @if($project->expenses->count() > 0)
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
                
                @if($project->expenses->count() > 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('expenses.index', ['project_id' => $project->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua pengeluaran ({{ $project->expenses->count() }})
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pengeluaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan pengeluaran proyek.</p>
                </div>
                @endif
            </div>

            <!-- Tab Penagihan -->
            <div id="content-penagihan" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Penagihan Proyek</h3>
                    @can('create', App\Models\ProjectBilling::class)
                    <a href="{{ route('billing-batches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Buat Batch Penagihan
                    </a>
                    @endcan
                </div>
                
                @if($project->billings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nomor Faktur Pajak</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($project->billings->take(10) as $billing)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $billing->invoice_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $billing->billing_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ number_format($billing->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                          @if($billing->status == 'paid') bg-green-100 text-green-800
                                          @elseif($billing->status == 'sent') bg-blue-100 text-blue-800
                                          @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($billing->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $billing->due_date ? $billing->due_date->format('d M Y') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($project->billings->count() > 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('billing-batches.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua batch penagihan
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada penagihan</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan penagihan proyek.</p>
                </div>
                @endif
            </div>

            <!-- Tab Aktivitas -->
            <div id="content-aktivitas" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Proyek</h3>
                
                @if($project->activities->count() > 0)
                <div class="space-y-4">
                    @foreach($project->activities->take(20) as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6Z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $activity->user->name }} • {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas</h3>
                    <p class="mt-1 text-sm text-gray-500">Aktivitas proyek akan muncul di sini.</p>
                </div>
                @endif
            </div>

            <!-- Tab Pendapatan -->
            <div id="content-pendapatan" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Pendapatan Proyek</h3>
                </div>
                
                @if($project->revenues->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($project->revenues as $revenue)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $revenue->revenue_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $revenue->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ number_format($revenue->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Diterima
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pendapatan</h3>
                    <p class="mt-1 text-sm text-gray-500">Pendapatan proyek akan muncul di sini.</p>
                </div>
                @endif
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
</script>
@endsection
