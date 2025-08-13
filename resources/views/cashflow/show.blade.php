@extends('layouts.app')

@section('title', 'Detail Transaksi Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Detail Transaksi Cashflow</h1>
            <p class="text-slate-600 mt-1">ID: #{{ $cashflow->id }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('finance.cashflow.index') }}" 
               class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            @if($cashflow->canBeEdited())
                <a href="{{ route('finance.cashflow.edit', $cashflow) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Transaction Details -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Informasi Transaksi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Transaksi</label>
                        <p class="text-lg font-medium text-slate-900">{{ $cashflow->transaction_date->format('d M Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Tipe</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $cashflow->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $cashflow->formatted_type }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Kategori</label>
                        <p class="text-lg font-medium text-slate-900">{{ $cashflow->category->name }}</p>
                        <p class="text-sm text-slate-500">{{ $cashflow->category->description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Jumlah</label>
                        <p class="text-2xl font-bold {{ $cashflow->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $cashflow->type === 'income' ? '+' : '-' }} {{ $cashflow->formatted_amount }}
                        </p>
                    </div>

                    @if($cashflow->project)
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Proyek</label>
                            <p class="text-lg font-medium text-slate-900">{{ $cashflow->project->name }}</p>
                            <p class="text-sm text-slate-500">{{ $cashflow->project->code }}</p>
                        </div>
                    @endif

                    @if($cashflow->payment_method)
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Metode Pembayaran</label>
                            <p class="text-lg font-medium text-slate-900">
                                @switch($cashflow->payment_method)
                                    @case('cash')
                                        Tunai
                                        @break
                                    @case('bank_transfer')
                                        Transfer Bank
                                        @break
                                    @case('check')
                                        Cek
                                        @break
                                    @case('credit_card')
                                        Kartu Kredit
                                        @break
                                    @default
                                        {{ ucfirst($cashflow->payment_method) }}
                                @endswitch
                            </p>
                        </div>
                    @endif

                    @if($cashflow->account_code)
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Kode Akun</label>
                            <p class="text-lg font-medium text-slate-900">{{ $cashflow->account_code }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $cashflow->status_badge_class }}">
                            {{ $cashflow->formatted_status }}
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Deskripsi</label>
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <p class="text-slate-900">{{ $cashflow->description }}</p>
                    </div>
                </div>

                @if($cashflow->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-slate-600 mb-2">Catatan</label>
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                            <p class="text-slate-900">{{ $cashflow->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Reference Information -->
            @if($cashflow->reference_type !== 'manual')
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h2 class="text-xl font-semibold text-slate-800 mb-6">Referensi Transaksi</h2>
                    
                    <div class="flex items-center space-x-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                @if($cashflow->reference_type === 'billing')
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-900">
                                Transaksi ini dibuat otomatis dari 
                                @if($cashflow->reference_type === 'billing')
                                    <strong>Penagihan Proyek</strong>
                                @else
                                    <strong>Pengeluaran Proyek</strong>
                                @endif
                            </p>
                            <p class="text-sm text-blue-700">
                                ID Referensi: #{{ $cashflow->reference_id }}
                            </p>
                            @if($cashflow->referencedModel)
                                <div class="mt-2">
                                    @if($cashflow->reference_type === 'billing')
                                        <p class="text-sm text-blue-700">
                                            Invoice: {{ $cashflow->referencedModel->invoice_number ?? 'N/A' }}
                                        </p>
                                    @else
                                        <p class="text-sm text-blue-700">
                                            Deskripsi: {{ $cashflow->referencedModel->description ?? 'N/A' }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status & Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Status & Aksi</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Status Saat Ini</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $cashflow->status_badge_class }}">
                            {{ $cashflow->formatted_status }}
                        </span>
                    </div>

                    @if($cashflow->status === 'pending')
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('finance.cashflow.bulk-action') }}" class="inline">
                                @csrf
                                <input type="hidden" name="entries" value='["{{ $cashflow->id }}"]'>
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                                    Konfirmasi
                                </button>
                            </form>
                            <form method="POST" action="{{ route('finance.cashflow.bulk-action') }}" class="inline">
                                @csrf
                                <input type="hidden" name="entries" value='["{{ $cashflow->id }}"]'>
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                                    Batalkan
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($cashflow->canBeDeleted())
                        <form method="POST" action="{{ route('finance.cashflow.destroy', $cashflow) }}" 
                              onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                                Hapus Transaksi
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Audit Trail</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Dibuat Oleh</label>
                        <p class="text-sm text-slate-900">{{ $cashflow->creator->name }}</p>
                        <p class="text-xs text-slate-500">{{ $cashflow->created_at->format('d M Y H:i') }}</p>
                    </div>

                    @if($cashflow->confirmed_at)
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Dikonfirmasi Oleh</label>
                            <p class="text-sm text-slate-900">{{ $cashflow->confirmer->name ?? 'System' }}</p>
                            <p class="text-xs text-slate-500">{{ $cashflow->confirmed_at->format('d M Y H:i') }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Terakhir Diupdate</label>
                        <p class="text-xs text-slate-500">{{ $cashflow->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            @if($cashflow->project)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Statistik Proyek</h3>
                    
                    @php
                        $projectBalance = $cashflow->project->cashflowEntries()->confirmed()->get();
                        $projectIncome = $projectBalance->where('type', 'income')->sum('amount');
                        $projectExpense = $projectBalance->where('type', 'expense')->sum('amount');
                        $projectNetBalance = $projectIncome - $projectExpense;
                    @endphp

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Total Pemasukan</span>
                            <span class="text-sm font-medium text-green-600">
                                Rp {{ number_format($projectIncome, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Total Pengeluaran</span>
                            <span class="text-sm font-medium text-red-600">
                                Rp {{ number_format($projectExpense, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                            <span class="text-sm font-medium text-slate-600">Saldo Bersih</span>
                            <span class="text-sm font-bold {{ $projectNetBalance >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                                Rp {{ number_format($projectNetBalance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection