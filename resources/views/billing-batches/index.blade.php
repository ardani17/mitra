@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Batch Penagihan</h1>
        <a href="{{ route('billing-batches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Buat Batch Baru</span>
            <span class="sm:hidden">+ Batch</span>
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('billing-batches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Status</label>
                <select name="status" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="area_verification" {{ request('status') == 'area_verification' ? 'selected' : '' }}>Verifikasi Area</option>
                    <option value="regional_verification" {{ request('status') == 'regional_verification' ? 'selected' : '' }}>Verifikasi Regional</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Pencarian</label>
                <div class="flex">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode batch, invoice..." 
                       class="flex-1 px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-r-md text-sm">
                        <span class="hidden sm:inline">Cari</span>
                        <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Batch List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aging</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $batch->batch_code }}</div>
                                @if($batch->sp_number)
                                    <div class="text-xs text-gray-500">SP: {{ $batch->sp_number }}</div>
                                @endif
                                @if($batch->invoice_number)
                                    <div class="text-xs text-gray-500">INV: {{ $batch->invoice_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $batch->billing_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $batch->projectBillings->count() }} proyek
                                </div>
                                <div class="text-xs text-gray-500">
                                    @foreach($batch->projectBillings->take(2) as $billing)
                                        <span class="inline-flex items-center">
                                            {{ $billing->project->code }}
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $billing->project->client_type_badge_color }}">
                                                {{ $billing->project->client_type === 'wapu' ? 'W' : 'N' }}
                                            </span>
                                        </span>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                    @if($batch->projectBillings->count() > 2)
                                        +{{ $batch->projectBillings->count() - 2 }} lainnya
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Rp {{ number_format($batch->total_billing_amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Diterima: Rp {{ number_format($batch->total_received_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($batch->status_color == 'green') bg-green-100 text-green-800
                                    @elseif($batch->status_color == 'blue') bg-blue-100 text-blue-800
                                    @elseif($batch->status_color == 'yellow') bg-yellow-100 text-yellow-800
                                    @elseif($batch->status_color == 'red') bg-red-100 text-red-800
                                    @elseif($batch->status_color == 'purple') bg-purple-100 text-purple-800
                                    @elseif($batch->status_color == 'indigo') bg-indigo-100 text-indigo-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $batch->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $batch->aging_days }} hari
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('billing-batches.show', $batch) }}" 
                                       class="text-blue-600 hover:text-blue-900">Detail</a>
                                    @if($batch->status === 'draft')
                                        <a href="{{ route('billing-batches.edit', $batch) }}" 
                                           class="text-green-600 hover:text-green-900">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada batch penagihan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse($batches as $batch)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
                <!-- Batch Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $batch->batch_code }}</h3>
                        @if($batch->sp_number)
                        <p class="text-xs text-gray-500 mt-1">SP: {{ $batch->sp_number }}</p>
                        @endif
                        @if($batch->invoice_number)
                        <p class="text-xs text-gray-500">INV: {{ $batch->invoice_number }}</p>
                        @endif
                    </div>
                    <div class="ml-3 flex flex-col items-end space-y-1">
                        <div class="text-xs text-gray-500">{{ $batch->billing_date->format('d/m/Y') }}</div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($batch->status_color == 'green') bg-green-100 text-green-800
                            @elseif($batch->status_color == 'blue') bg-blue-100 text-blue-800
                            @elseif($batch->status_color == 'yellow') bg-yellow-100 text-yellow-800
                            @elseif($batch->status_color == 'red') bg-red-100 text-red-800
                            @elseif($batch->status_color == 'purple') bg-purple-100 text-purple-800
                            @elseif($batch->status_color == 'indigo') bg-indigo-100 text-indigo-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $batch->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="mb-3">
                    <label class="text-xs font-medium text-gray-500">Proyek</label>
                    <div class="mt-1">
                        <div class="text-sm text-gray-900">{{ $batch->projectBillings->count() }} proyek</div>
                        <div class="text-xs text-gray-500 mt-1">
                            @foreach($batch->projectBillings->take(3) as $billing)
                                <span class="inline-flex items-center mr-2 mb-1">
                                    {{ $billing->project->code }}
                                    <span class="ml-1 inline-flex items-center px-1 py-0.5 rounded text-xs font-medium {{ $billing->project->client_type_badge_color }}">
                                        {{ $billing->project->client_type === 'wapu' ? 'W' : 'N' }}
                                    </span>
                                </span>
                            @endforeach
                            @if($batch->projectBillings->count() > 3)
                                <span class="text-xs text-gray-400">+{{ $batch->projectBillings->count() - 3 }} lainnya</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Financial Info -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Total Tagihan</label>
                        <div class="text-sm font-semibold text-gray-900 mt-1">
                            Rp {{ number_format($batch->total_billing_amount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Diterima</label>
                        <div class="text-sm font-semibold text-green-600 mt-1">
                            Rp {{ number_format($batch->total_received_amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Aging and Actions -->
                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Aging</label>
                        <div class="text-sm text-gray-900 mt-1">{{ $batch->aging_days }} hari</div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('billing-batches.show', $batch) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                            Detail
                        </a>
                        @if($batch->status === 'draft')
                        <a href="{{ route('billing-batches.edit', $batch) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                            Edit
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm">Tidak ada batch penagihan ditemukan.</p>
            </div>
            @endforelse
        </div>
        
        @if($batches->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mt-4 sm:mt-6">
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Batch</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $batches->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Proses</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">
                        {{ $batches->where('status', '!=', 'paid')->where('status', '!=', 'cancelled')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Lunas</p>
                    <p class="text-lg sm:text-2xl font-semibold text-gray-900">
                        {{ $batches->where('status', 'paid')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Nilai</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">
                        Rp {{ number_format($batches->sum('total_billing_amount'), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
