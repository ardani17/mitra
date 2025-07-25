@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $billingBatch->batch_code }}</h1>
            <p class="text-gray-600 mt-1">Detail Batch Penagihan</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('billing-batches.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali ke Batch
            </a>
            @if($billingBatch->status === 'draft')
                <a href="{{ route('billing-batches.edit', $billingBatch) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Batch
                </a>
                <a href="{{ route('billing-batches.confirm-delete', $billingBatch) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Hapus Batch
                </a>
            @endif
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <!-- Informasi Batch -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Batch</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Kode Batch:</span>
                    <p class="font-medium">{{ $billingBatch->batch_code }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Tanggal:</span>
                    <p class="font-medium">{{ $billingBatch->billing_date->format('d M Y') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($billingBatch->status_color == 'green') bg-green-100 text-green-800
                          @elseif($billingBatch->status_color == 'blue') bg-blue-100 text-blue-800
                          @elseif($billingBatch->status_color == 'yellow') bg-yellow-100 text-yellow-800
                          @elseif($billingBatch->status_color == 'red') bg-red-100 text-red-800
                          @elseif($billingBatch->status_color == 'purple') bg-purple-100 text-purple-800
                          @elseif($billingBatch->status_color == 'indigo') bg-indigo-100 text-indigo-800
                          @else bg-gray-100 text-gray-800 @endif">
                        {{ $billingBatch->status_label }}
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Aging:</span>
                    <p class="font-medium">{{ $billingBatch->aging_days }} hari</p>
                </div>
            </div>
        </div>

        <!-- Tipe Klien & Invoice -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Klien</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Tipe Klien:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($billingBatch->client_type == 'wapu') bg-blue-100 text-blue-800
                          @else bg-green-100 text-green-800 @endif">
                        {{ $billingBatch->client_type == 'wapu' ? 'WAPU' : 'Non-WAPU' }}
                    </span>
                </div>
                @if($billingBatch->sp_number)
                <div>
                    <span class="text-sm text-gray-600">Nomor SP:</span>
                    <p class="font-medium">{{ $billingBatch->sp_number }}</p>
                </div>
                @endif
                @if($billingBatch->invoice_number)
                <div>
                    <span class="text-sm text-gray-600">Nomor Faktur Pajak:</span>
                    <p class="font-medium">{{ $billingBatch->invoice_number }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm text-gray-600">Jumlah Proyek:</span>
                    <p class="font-medium">{{ $billingBatch->projectBillings->count() }} proyek</p>
                </div>
            </div>
        </div>

        <!-- Nilai Base & PPN -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Nilai & Pajak</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Total Base:</span>
                    <p class="font-medium text-blue-600">Rp {{ number_format($billingBatch->total_base_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">PPN ({{ $billingBatch->ppn_rate }}%):</span>
                    <p class="font-medium text-green-600">Rp {{ number_format($billingBatch->ppn_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">PPh ({{ $billingBatch->pph_rate }}%):</span>
                    <p class="font-medium text-red-600">Rp {{ number_format($billingBatch->pph_amount, 0, ',', '.') }}</p>
                </div>
                <div class="border-t pt-2">
                    <span class="text-sm text-gray-600">Total Billing:</span>
                    <p class="font-bold text-lg">Rp {{ number_format($billingBatch->total_billing_amount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Nilai Diterima -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Nilai Diterima</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Setelah PPh:</span>
                    <p class="font-bold text-2xl text-purple-600">Rp {{ number_format($billingBatch->total_received_amount, 0, ',', '.') }}</p>
                </div>
                <div class="text-xs text-gray-500">
                    <p>Total Billing - PPh</p>
                    <p>{{ number_format($billingBatch->total_billing_amount, 0, ',', '.') }} - {{ number_format($billingBatch->pph_amount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
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
                        <p class="text-sm text-gray-600">Total Base Amount</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($billingBatch->total_base_amount, 0, ',', '.') }}</p>
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
                        <p class="text-sm text-gray-600">PPN ({{ $billingBatch->ppn_rate }}%)</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($billingBatch->ppn_amount, 0, ',', '.') }}</p>
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
                        <p class="text-sm text-gray-600">PPh ({{ $billingBatch->pph_rate }}%)</p>
                        <p class="text-xl font-bold text-red-600">Rp {{ number_format($billingBatch->pph_amount, 0, ',', '.') }}</p>
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
                        <p class="text-sm text-gray-600">Nilai Diterima</p>
                        <p class="text-xl font-bold text-purple-600">Rp {{ number_format($billingBatch->total_received_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('penagihan')" id="tab-penagihan" class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Penagihan Proyek
                </button>
                <button onclick="showTab('dokumen')" id="tab-dokumen" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Dokumen
                </button>
                <button onclick="showTab('status')" id="tab-status" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Riwayat Status
                </button>
                <button onclick="showTab('aksi')" id="tab-aksi" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Aksi Status
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Tab Penagihan Proyek -->
            <div id="content-penagihan" class="tab-content">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Penagihan Proyek ({{ $billingBatch->projectBillings->count() }})</h3>
                
                @if($billingBatch->projectBillings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Proyek</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Klien</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nilai Jasa</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nilai Material</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">PPN</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($billingBatch->projectBillings as $billing)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $billing->project->code }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($billing->project->name, 30) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ Str::limit($billing->project->client_name, 25) }}</div>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                          @if($billing->project->client_type == 'wapu') bg-blue-100 text-blue-800
                                          @else bg-green-100 text-green-800 @endif">
                                        {{ $billing->project->client_type == 'wapu' ? 'WAPU' : 'Non-WAPU' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($billing->nilai_jasa, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($billing->nilai_material, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($billing->ppn_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ number_format($billing->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                          @if($billing->status == 'draft') bg-gray-100 text-gray-800
                                          @elseif($billing->status == 'sent') bg-blue-100 text-blue-800
                                          @elseif($billing->status == 'paid') bg-green-100 text-green-800
                                          @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($billing->status) }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada penagihan</h3>
                    <p class="mt-1 text-sm text-gray-500">Penagihan proyek akan muncul di sini.</p>
                </div>
                @endif
            </div>

            <!-- Tab Dokumen -->
            <div id="content-dokumen" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Dokumen Batch</h3>
                    @if($billingBatch->status !== 'paid')
                    <button onclick="openUploadModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload Dokumen
                    </button>
                    @endif
                </div>
                
                @if($billingBatch->documents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($billingBatch->documents as $document)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3 flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $document->file_name }}</h4>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($document->stage) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ number_format($document->file_size / 1024, 0) }} KB</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $document->created_at->diffForHumans() }}
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
                                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Lihat
                                        </a>
                                        @if($billingBatch->status !== 'paid')
                                        <button onclick="deleteDocument({{ $document->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Hapus
                                        </button>
                                        @endif
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
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan mengunggah dokumen batch.</p>
                    @if($billingBatch->status !== 'paid')
                    <button onclick="openUploadModal()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload Dokumen Pertama
                    </button>
                    @endif
                </div>
                @endif
            </div>

            <!-- Tab Riwayat Status -->
            <div id="content-status" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Status</h3>
                
                @if($billingBatch->statusLogs->count() > 0)
                <div class="space-y-4">
                    @foreach($billingBatch->statusLogs->sortByDesc('created_at') as $log)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6Z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ $log->status_label }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $log->user->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                            </p>
                            @if($log->notes)
                                <p class="text-xs text-gray-600 mt-1">{{ $log->notes }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat</h3>
                    <p class="mt-1 text-sm text-gray-500">Riwayat status akan muncul di sini.</p>
                </div>
                @endif
            </div>

            <!-- Tab Aksi Status -->
            <div id="content-aksi" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Status</h3>
                
                @if($billingBatch->status === 'draft')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="text-blue-800 font-medium mb-2">Kirim Batch</h4>
                        <p class="text-blue-700 text-sm mb-3">Kirim batch penagihan untuk diproses lebih lanjut.</p>
                        <form action="{{ route('billing-batches.update-status', $billingBatch) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="sent">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Kirim Batch
                            </button>
                        </form>
                    </div>
                @elseif($billingBatch->status === 'sent')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <h4 class="text-yellow-800 font-medium mb-2">Verifikasi Area</h4>
                        <p class="text-yellow-700 text-sm mb-3">Lakukan verifikasi area untuk batch penagihan ini.</p>
                        <form action="{{ route('billing-batches.update-status', $billingBatch) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="area_verification">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Verifikasi Area
                            </button>
                        </form>
                    </div>
                @elseif($billingBatch->status === 'area_verification')
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <h4 class="text-orange-800 font-medium mb-2">Verifikasi Regional</h4>
                        <p class="text-orange-700 text-sm mb-3">Lakukan verifikasi regional untuk batch penagihan ini.</p>
                        <form action="{{ route('billing-batches.update-status', $billingBatch) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="regional_verification">
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                                Verifikasi Regional
                            </button>
                        </form>
                    </div>
                @elseif($billingBatch->status === 'regional_verification')
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <h4 class="text-orange-800 font-medium mb-2">Entry Pembayaran HO</h4>
                        <p class="text-orange-700 text-sm mb-3">Masukkan nomor faktur pajak untuk melanjutkan ke entry pembayaran HO.</p>
                        <form action="{{ route('billing-batches.update-status', $billingBatch) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="payment_entry_ho">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Faktur Pajak</label>
                                <input type="text" name="invoice_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="010.000-25.00000001" required>
                            </div>
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                                Entry Pembayaran HO
                            </button>
                        </form>
                    </div>
                @elseif($billingBatch->status === 'payment_entry_ho')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h4 class="text-green-800 font-medium mb-2">Tandai Lunas</h4>
                        <p class="text-green-700 text-sm mb-3">Tandai batch sebagai lunas setelah pembayaran selesai diproses.</p>
                        <form action="{{ route('billing-batches.update-status', $billingBatch) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="paid">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Tandai Lunas
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-600">Tidak ada aksi yang tersedia untuk status saat ini.</p>
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
            <form id="uploadForm" action="{{ route('billing-batches.upload-document', $billingBatch) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Dokumen</label>
                        <select name="document_type" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Tipe</option>
                            <option value="initial">Dokumen Awal</option>
                            <option value="revision">Revisi</option>
                            <option value="supporting">Pendukung</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File</label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Opsional)</label>
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

// Delete document
function deleteDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
        fetch(`/billing-batches/{{ $billingBatch->id }}/documents/${documentId}`, {
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
