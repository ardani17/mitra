@extends('layouts.app')

@section('title', 'Riwayat Modifikasi Pengeluaran')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Riwayat Modifikasi Pengeluaran</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Riwayat permintaan edit dan hapus untuk pengeluaran ini</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('expenses.show', $expense) }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Detail
            </a>
        </div>
    </div>

    <!-- Expense Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-slate-800 mb-4">Detail Pengeluaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <p class="mt-1 text-sm text-slate-900">{{ $expense->description }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Jumlah</label>
                <p class="mt-1 text-sm text-slate-900 font-semibold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ ucfirst($expense->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Modification History -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-medium text-slate-800">Riwayat Permintaan Modifikasi</h3>
        </div>

        @if($modifications->count() > 0)
            <div class="divide-y divide-slate-200">
                @foreach($modifications as $modification)
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->action_type_badge_class }}">
                                        {{ $modification->formatted_action_type }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->status_badge_class }}">
                                        {{ $modification->formatted_status }}
                                    </span>
                                    <span class="text-sm text-slate-500">
                                        {{ $modification->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-sm text-slate-600">
                                        <strong>Pengaju:</strong> {{ $modification->requester->name }}
                                    </p>
                                    @if($modification->approver)
                                        <p class="text-sm text-slate-600">
                                            <strong>Diproses oleh:</strong> {{ $modification->approver->name }}
                                            @if($modification->approved_at)
                                                pada {{ $modification->approved_at->format('d M Y H:i') }}
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <p class="text-sm font-medium text-slate-700">Alasan:</p>
                                    <p class="text-sm text-slate-600 mt-1">{{ $modification->reason }}</p>
                                </div>

                                @if($modification->approval_notes)
                                    <div class="mb-3">
                                        <p class="text-sm font-medium text-slate-700">Catatan Persetujuan:</p>
                                        <p class="text-sm text-slate-600 mt-1">{{ $modification->approval_notes }}</p>
                                    </div>
                                @endif

                                @if($modification->isEditRequest() && $modification->proposed_data)
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-slate-700 mb-2">Perubahan yang Diajukan:</p>
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            @php
                                                $changes = $modification->getChangesSummary();
                                            @endphp
                                            @if(!empty($changes))
                                                <div class="space-y-2">
                                                    @foreach($changes as $field => $change)
                                                        <div class="flex justify-between text-sm">
                                                            <span class="font-medium text-slate-700">{{ $change['field_name'] }}:</span>
                                                            <div class="text-right">
                                                                <div class="text-red-600 line-through">{{ $change['old'] }}</div>
                                                                <div class="text-green-600">{{ $change['new'] }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-slate-500">Tidak ada perubahan terdeteksi</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="ml-4">
                                @if($modification->isPending())
                                    <div class="flex flex-col space-y-2">
                                        @if($modification->requested_by === auth()->id())
                                            <form method="POST" action="{{ route('expense-modifications.cancel', $modification) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-3 py-1 bg-orange-100 text-orange-800 rounded text-xs hover:bg-orange-200 transition-colors"
                                                        onclick="return confirm('Yakin ingin membatalkan permintaan ini?')">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(auth()->user()->hasAnyRole(['finance_manager', 'direktur', 'project_manager']))
                                            <a href="{{ route('expense-modifications.show', $modification) }}" 
                                               class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-xs hover:bg-blue-200 transition-colors text-center">
                                                Review
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <a href="{{ route('expense-modifications.show', $modification) }}" 
                                       class="px-3 py-1 bg-slate-100 text-slate-800 rounded text-xs hover:bg-slate-200 transition-colors">
                                        Lihat Detail
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">Tidak ada riwayat modifikasi</h3>
                <p class="mt-1 text-sm text-slate-500">Belum ada permintaan edit atau hapus untuk pengeluaran ini.</p>
                
                @if($expense->canBeModified())
                    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                        @can('requestModification', $expense)
                            <a href="{{ route('expense-modifications.edit-form', $expense) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Request Edit
                            </a>
                            <a href="{{ route('expense-modifications.delete-form', $expense) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Request Delete
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection