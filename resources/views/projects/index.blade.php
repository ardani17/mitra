@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Manajemen Proyek</h1>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <a href="{{ route('projects.export', request()->query()) }}" 
               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 sm:px-4 rounded inline-flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="hidden sm:inline">Export Excel</span>
                <span class="sm:hidden">Export</span>
            </a>
            <a href="{{ route('projects.import.form') }}" 
               class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 sm:px-4 rounded inline-flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span class="hidden sm:inline">Import Excel</span>
                <span class="sm:hidden">Import</span>
            </a>
            <a href="{{ route('projects.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Buat Proyek Baru</span>
                <span class="sm:hidden">+ Proyek</span>
            </a>
        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="card p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Filter & Pencarian</h3>
            <button type="button" id="toggleAdvancedFilter" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <span id="filterToggleText">Tampilkan Filter Lanjutan</span>
                <svg id="filterToggleIcon" class="inline w-4 h-4 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        
        <form method="GET" action="{{ route('projects.index') }}" id="filterForm">
            <!-- Basic Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pencarian Real-time</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-input" placeholder="Cari nama proyek, kode, atau deskripsi..."
                           id="searchInput">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Perencanaan</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipe</label>
                    <select name="type" class="form-select" id="typeFilter">
                        <option value="">Semua Tipe</option>
                        <option value="konstruksi" {{ request('type') == 'konstruksi' ? 'selected' : '' }}>Konstruksi</option>
                        <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary p-3" title="Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn-secondary p-3" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Advanced Filters (Hidden by default) -->
            <div id="advancedFilters" class="hidden border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Range Budget (Rp)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="budget_min" value="{{ request('budget_min') }}" 
                                   class="form-input" placeholder="Min" min="0">
                            <input type="number" name="budget_max" value="{{ request('budget_max') }}" 
                                   class="form-input" placeholder="Max" min="0">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="start_date_from" value="{{ request('start_date_from') }}" 
                                   class="form-input">
                            <input type="date" name="start_date_to" value="{{ request('start_date_to') }}" 
                                   class="form-input">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Urutkan Berdasarkan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort_by" class="form-select">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama Proyek</option>
                                <option value="planned_budget" {{ request('sort_by') == 'planned_budget' ? 'selected' : '' }}>Budget</option>
                                <option value="start_date" {{ request('sort_by') == 'start_date' ? 'selected' : '' }}>Tanggal Mulai</option>
                            </select>
                            <select name="sort_direction" class="form-select">
                                <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Projects Display -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Proyek</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tagihan</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Nilai Plan</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Nilai Akhir</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $index => $project)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-3 py-4 text-sm font-medium text-gray-900">
                            {{ $projects->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                <div class="text-sm font-medium text-gray-900 truncate" title="{{ $project->name }}">
                                    {{ $project->name }}
                                </div>
                                <div class="text-xs text-gray-500 truncate" title="{{ $project->description }}">
                                    {{ Str::limit($project->description, 40) }}
                                </div>
                                <div class="text-xs text-blue-600 font-mono">{{ $project->code }}</div>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                  @if($project->type == 'konstruksi') bg-blue-100 text-blue-800
                                  @elseif($project->type == 'maintenance') bg-green-100 text-green-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                @if($project->type == 'konstruksi') Konst
                                @elseif($project->type == 'maintenance') Maint
                                @else {{ ucfirst($project->type) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="space-y-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                      @if($project->status == 'completed') bg-green-100 text-green-800
                                      @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                                      @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                                      @elseif($project->status == 'cancelled') bg-red-100 text-red-800
                                      @else bg-gray-100 text-gray-800 @endif">
                                    @if($project->status == 'planning') Plan
                                    @elseif($project->status == 'in_progress') Progress
                                    @elseif($project->status == 'completed') Selesai
                                    @elseif($project->status == 'cancelled') Batal
                                    @else {{ ucfirst($project->status) }}
                                    @endif
                                </span>
                                
                                <!-- Progress Bar -->
                                @php
                                    $progressPercentage = 0;
                                    if ($project->status == 'planning') $progressPercentage = 25;
                                    elseif ($project->status == 'in_progress') $progressPercentage = 60;
                                    elseif ($project->status == 'completed') $progressPercentage = 100;
                                    elseif ($project->status == 'cancelled') $progressPercentage = 0;
                                    
                                    // Calculate actual progress from timelines if available
                                    $timelineProgress = $project->timelines()->avg('progress_percentage') ?? $progressPercentage;
                                    $actualProgress = max($progressPercentage, $timelineProgress);
                                @endphp
                                
                                <div class="w-full">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>{{ number_format($actualProgress, 0) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-300 
                                                  @if($project->status == 'completed') bg-green-500
                                                  @elseif($project->status == 'in_progress') bg-blue-500
                                                  @elseif($project->status == 'planning') bg-yellow-500
                                                  @elseif($project->status == 'cancelled') bg-red-500
                                                  @else bg-gray-500 @endif" 
                                             style="width: {{ $actualProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4 text-sm">
                            <div class="space-y-1">
                                <!-- Status Badge -->
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $project->billing_status_badge_color }}">
                                    {{ $project->billing_status_label }}
                                </span>
                                
                                <!-- Progress Bar -->
                                <div class="w-full">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>{{ $project->billing_progress_percentage }}%</span>
                                        <span>{{ \App\Helpers\FormatHelper::formatRupiah($project->total_received_amount) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-300 
                                                  @if($project->current_billing_status == 'paid') bg-green-500
                                                  @elseif(in_array($project->current_billing_status, ['regional_verification', 'payment_entry_ho'])) bg-blue-500
                                                  @elseif(in_array($project->current_billing_status, ['area_verification', 'sent'])) bg-yellow-500
                                                  @elseif(in_array($project->current_billing_status, ['area_revision', 'regional_revision'])) bg-orange-500
                                                  @elseif($project->current_billing_status == 'cancelled') bg-red-500
                                                  @else bg-gray-500 @endif" 
                                             style="width: {{ $project->billing_progress_percentage }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Latest Invoice -->
                                @if($project->latest_billing_info['invoice_number'])
                                <div class="text-xs text-gray-500 truncate" title="{{ $project->latest_billing_info['invoice_number'] }}">
                                    {{ Str::limit($project->latest_billing_info['invoice_number'], 15) }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-gray-900">
                                {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_total_value ?? $project->planned_budget) }}
                            </div>
                            @if($project->planned_service_value || $project->planned_material_value)
                            <div class="text-xs text-gray-500">
                                J: {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_service_value ?? 0) }}<br>
                                M: {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_material_value ?? 0) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($project->final_total_value)
                            <div class="font-medium text-green-600">
                                {{ \App\Helpers\FormatHelper::formatRupiah($project->final_total_value) }}
                            </div>
                            @if($project->final_service_value || $project->final_material_value)
                            <div class="text-xs text-gray-500">
                                J: {{ \App\Helpers\FormatHelper::formatRupiah($project->final_service_value ?? 0) }}<br>
                                M: {{ \App\Helpers\FormatHelper::formatRupiah($project->final_material_value ?? 0) }}
                            </div>
                            @endif
                            @else
                            <div class="text-xs text-gray-400">-</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-xs text-gray-500">
                            @if($project->start_date)
                                <div>{{ $project->start_date->format('d/m/Y') }}</div>
                            @else
                                <div>-</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-xs">
                            <div class="flex flex-col space-y-1">
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="text-blue-600 hover:text-blue-900 font-medium">Lihat</a>
                                <a href="{{ route('projects.edit', $project) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
                            Tidak ada proyek ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse($projects as $index => $project)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
                <!-- Project Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 truncate" title="{{ $project->name }}">
                            {{ $project->name }}
                        </h3>
                        <p class="text-xs text-blue-600 font-mono mt-1">{{ $project->code }}</p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit($project->description, 60) }}</p>
                    </div>
                    <div class="ml-3 flex flex-col items-end space-y-1">
                        <span class="text-xs font-medium text-gray-500">#{{ $projects->firstItem() + $index }}</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                              @if($project->type == 'konstruksi') bg-blue-100 text-blue-800
                              @elseif($project->type == 'maintenance') bg-green-100 text-green-800
                              @else bg-gray-100 text-gray-800 @endif">
                            @if($project->type == 'konstruksi') Konst
                            @elseif($project->type == 'maintenance') Maint
                            @else {{ ucfirst($project->type) }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Status and Progress -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Status Proyek</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                  @if($project->status == 'completed') bg-green-100 text-green-800
                                  @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                                  @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                                  @elseif($project->status == 'cancelled') bg-red-100 text-red-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                @if($project->status == 'planning') Perencanaan
                                @elseif($project->status == 'in_progress') Sedang Berjalan
                                @elseif($project->status == 'completed') Selesai
                                @elseif($project->status == 'cancelled') Dibatalkan
                                @else {{ ucfirst($project->status) }}
                                @endif
                            </span>
                            @php
                                $progressPercentage = 0;
                                if ($project->status == 'planning') $progressPercentage = 25;
                                elseif ($project->status == 'in_progress') $progressPercentage = 60;
                                elseif ($project->status == 'completed') $progressPercentage = 100;
                                elseif ($project->status == 'cancelled') $progressPercentage = 0;
                                
                                $timelineProgress = $project->timelines()->avg('progress_percentage') ?? $progressPercentage;
                                $actualProgress = max($progressPercentage, $timelineProgress);
                            @endphp
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Progress</span>
                                    <span>{{ number_format($actualProgress, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300 
                                              @if($project->status == 'completed') bg-green-500
                                              @elseif($project->status == 'in_progress') bg-blue-500
                                              @elseif($project->status == 'planning') bg-yellow-500
                                              @elseif($project->status == 'cancelled') bg-red-500
                                              @else bg-gray-500 @endif" 
                                         style="width: {{ $actualProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Status Tagihan</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $project->billing_status_badge_color }}">
                                {{ $project->billing_status_label }}
                            </span>
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>{{ $project->billing_progress_percentage }}%</span>
                                    <span>{{ \App\Helpers\FormatHelper::formatRupiah($project->total_received_amount) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300 
                                              @if($project->current_billing_status == 'paid') bg-green-500
                                              @elseif(in_array($project->current_billing_status, ['regional_verification', 'payment_entry_ho'])) bg-blue-500
                                              @elseif(in_array($project->current_billing_status, ['area_verification', 'sent'])) bg-yellow-500
                                              @elseif(in_array($project->current_billing_status, ['area_revision', 'regional_revision'])) bg-orange-500
                                              @elseif($project->current_billing_status == 'cancelled') bg-red-500
                                              @else bg-gray-500 @endif" 
                                         style="width: {{ $project->billing_progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Info -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nilai Rencana</label>
                        <div class="text-sm font-semibold text-gray-900 mt-1">
                            {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_total_value ?? $project->planned_budget) }}
                        </div>
                        @if($project->planned_service_value || $project->planned_material_value)
                        <div class="text-xs text-gray-500 mt-1">
                            J: {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_service_value ?? 0) }}<br>
                            M: {{ \App\Helpers\FormatHelper::formatRupiah($project->planned_material_value ?? 0) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nilai Akhir</label>
                        @if($project->final_total_value)
                        <div class="text-sm font-semibold text-green-600 mt-1">
                            {{ \App\Helpers\FormatHelper::formatRupiah($project->final_total_value) }}
                        </div>
                        @if($project->final_service_value || $project->final_material_value)
                        <div class="text-xs text-gray-500 mt-1">
                            J: {{ \App\Helpers\FormatHelper::formatRupiah($project->final_service_value ?? 0) }}<br>
                            M: {{ \App\Helpers\FormatHelper::formatRupiah($project->final_material_value ?? 0) }}
                        </div>
                        @endif
                        @else
                        <div class="text-sm text-gray-400 mt-1">-</div>
                        @endif
                    </div>
                </div>

                <!-- Date and Actions -->
                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Tanggal Mulai</label>
                        <div class="text-sm text-gray-900 mt-1">
                            @if($project->start_date)
                                {{ $project->start_date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.show', $project) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                            Lihat
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" 
                           class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm">Tidak ada proyek ditemukan.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-t border-blue-200 sm:px-6">
            <div class="pagination-wrapper">
                {{ $projects->links('vendor.pagination.responsive-tailwind') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Advanced Filter Toggle
    const toggleButton = document.getElementById('toggleAdvancedFilter');
    const advancedFilters = document.getElementById('advancedFilters');
    const toggleText = document.getElementById('filterToggleText');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    toggleButton.addEventListener('click', function() {
        if (advancedFilters.classList.contains('hidden')) {
            advancedFilters.classList.remove('hidden');
            toggleText.textContent = 'Sembunyikan Filter Lanjutan';
            toggleIcon.style.transform = 'rotate(180deg)';
        } else {
            advancedFilters.classList.add('hidden');
            toggleText.textContent = 'Tampilkan Filter Lanjutan';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    });
    
    // Real-time Search with Debounce
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    let searchTimeout;
    
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500); // 500ms delay
    }
    
    // Auto-submit on filter changes
    searchInput.addEventListener('input', performSearch);
    statusFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    typeFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    // Show loading indicator during search
    const form = document.getElementById('filterForm');
    form.addEventListener('submit', function() {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mencari...';
        submitButton.disabled = true;
        
        // Re-enable after 3 seconds (fallback)
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }, 3000);
    });
    
    // Format number inputs for budget range
    const budgetInputs = document.querySelectorAll('input[name="budget_min"], input[name="budget_max"]');
    budgetInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        input.addEventListener('blur', function() {
            if (this.value) {
                // Format with thousand separators for display
                const formatted = parseInt(this.value).toLocaleString('id-ID');
                this.setAttribute('data-formatted', formatted);
            }
        });
    });
    
    // Highlight search terms in results
    const searchTerm = searchInput.value.toLowerCase();
    if (searchTerm) {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                const text = cell.textContent;
                if (text.toLowerCase().includes(searchTerm)) {
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    cell.innerHTML = cell.innerHTML.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                }
            });
        });
    }
    
    // Filter lanjutan selalu tersembunyi secara default
    // Tidak ada auto-expand logic - user harus manual klik untuk membuka
});
</script>
@endsection
