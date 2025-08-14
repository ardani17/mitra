@extends('layouts.app')

@section('title', 'Detail Permintaan Modifikasi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Detail Permintaan Modifikasi</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">{{ $modification->formatted_action_type }} - {{ $modification->formatted_status }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('expense-modifications.index') }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar
            </a>
            <a href="{{ route('expenses.show', $modification->expense) }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Lihat Pengeluaran
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="mb-6">
        @if($modification->isPending())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Menunggu Persetujuan</h3>
                        <p class="mt-1 text-sm text-yellow-700">Permintaan ini sedang menunggu persetujuan dari approver yang berwenang.</p>
                    </div>
                </div>
            </div>
        @elseif($modification->isApproved())
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Disetujui</h3>
                        <p class="mt-1 text-sm text-green-700">
                            Permintaan ini telah disetujui oleh {{ $modification->approver->name }} 
                            pada {{ $modification->approved_at->format('d M Y H:i') }}.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($modification->isRejected())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Ditolak</h3>
                        <p class="mt-1 text-sm text-red-700">
                            Permintaan ini telah ditolak oleh {{ $modification->approver->name }} 
                            pada {{ $modification->approved_at->format('d M Y H:i') }}.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Request Information -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-slate-800 mb-4">Informasi Permintaan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jenis Aksi</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->action_type_badge_class }}">
                        {{ $modification->formatted_action_type }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->status_badge_class }}">
                        {{ $modification->formatted_status }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Permintaan</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $modification->created_at->format('d M Y H:i') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Pengaju</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $modification->requester->name }}</p>
                </div>
            </div>
            
            <div class="space-y-4">
                @if($modification->approver)
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Diproses oleh</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $modification->approver->name }}</p>
                    </div>
                @endif
                
                @if($modification->approved_at)
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Diproses</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $modification->approved_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Memerlukan Persetujuan Tingkat Tinggi</label>
                    <p class="mt-1 text-sm text-slate-900">
                        @if($modification->requiresHighLevelApproval())
                            <span class="text-red-600">Ya</span> (Direktur)
                        @else
                            <span class="text-green-600">Tidak</span> (Finance Manager/Project Manager)
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Details -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-slate-800 mb-4">Detail Pengeluaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-slate-700">Proyek</label>
                <p class="mt-1 text-sm text-slate-900">{{ $modification->expense->project->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <p class="mt-1 text-sm text-slate-900">{{ $modification->expense->description }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Jumlah</label>
                <p class="mt-1 text-sm text-slate-900 font-semibold">Rp {{ number_format($modification->expense->amount, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Status Pengeluaran</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ ucfirst($modification->expense->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Reason -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-slate-800 mb-4">Alasan Permintaan</h3>
        <div class="bg-slate-50 rounded-lg p-4">
            <p class="text-sm text-slate-700">{{ $modification->reason }}</p>
        </div>
    </div>

    <!-- Changes (for edit requests) -->
    @if($modification->isEditRequest() && $modification->proposed_data)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-slate-800 mb-4">Perubahan yang Diajukan</h3>
            @php
                $changes = $modification->getChangesSummary();
            @endphp
            @if(!empty($changes))
                <div class="space-y-4">
                    @foreach($changes as $field => $change)
                        <div class="border border-slate-200 rounded-lg p-4">
                            <h4 class="font-medium text-slate-800 mb-2">{{ $change['field_name'] }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-red-700">Nilai Lama</label>
                                    <div class="mt-1 p-2 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                                        {{ $change['old'] ?: '-' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-green-700">Nilai Baru</label>
                                    <div class="mt-1 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-800">
                                        {{ $change['new'] ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Tidak ada perubahan terdeteksi</p>
            @endif
        </div>
    @endif

    <!-- Approval Notes -->
    @if($modification->approval_notes)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-slate-800 mb-4">Catatan Persetujuan</h3>
            <div class="bg-slate-50 rounded-lg p-4">
                <p class="text-sm text-slate-700">{{ $modification->approval_notes }}</p>
            </div>
        </div>
    @endif

    <!-- Actions -->
    @if($modification->isPending())
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-medium text-slate-800 mb-4">Aksi</h3>
            <div class="flex flex-col sm:flex-row gap-4">
                
                <!-- Cancel (for requester) -->
                @if($modification->requested_by === auth()->id())
                    <form method="POST" action="{{ route('expense-modifications.cancel', $modification) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md transition-colors"
                                onclick="return confirm('Yakin ingin membatalkan permintaan ini?')">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batalkan Permintaan
                        </button>
                    </form>
                @endif

                <!-- Approve/Reject (for approvers) -->
                @can('approve', $modification)
                    <div class="flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('expense-modifications.approve', $modification) }}" class="inline">
                            @csrf
                            <div class="space-y-3">
                                <textarea name="approval_notes" rows="2" 
                                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Catatan persetujuan (opsional)"></textarea>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors"
                                        onclick="return confirm('Yakin ingin menyetujui permintaan ini?')">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                        </form>
                        
                        <form method="POST" action="{{ route('expense-modifications.reject', $modification) }}" class="inline">
                            @csrf
                            <div class="space-y-3">
                                <textarea name="approval_notes" rows="2" required
                                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                          placeholder="Alasan penolakan (wajib)"></textarea>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors"
                                        onclick="return confirm('Yakin ingin menolak permintaan ini?')">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    @endif
</div>
@endsection