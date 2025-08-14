@extends('layouts.app')

@section('title', 'Permintaan Hapus Pengeluaran')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Permintaan Hapus Pengeluaran</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Ajukan permintaan untuk menghapus pengeluaran yang sudah disetujui</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('expenses.show', $expense) }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Peringatan Penghapusan</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Penghapusan pengeluaran yang sudah disetujui memerlukan persetujuan khusus</li>
                        <li>Data pengeluaran akan dihapus secara permanen setelah disetujui</li>
                        <li>Cashflow entry terkait akan dibatalkan</li>
                        <li>Tindakan ini tidak dapat dibatalkan setelah disetujui</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Details -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-slate-800 mb-4">Detail Pengeluaran yang Akan Dihapus</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Proyek</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $expense->project->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $expense->description }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jumlah</label>
                    <p class="mt-1 text-sm text-slate-900 font-semibold text-red-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Pengeluaran</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $expense->expense_date->format('d M Y') }}</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <p class="mt-1 text-sm text-slate-900">{{ ucfirst($expense->category) }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Vendor</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $expense->vendor ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nomor Kuitansi</label>
                    <p class="mt-1 text-sm text-slate-900">{{ $expense->receipt_number ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ ucfirst($expense->status) }}
                    </span>
                </div>
            </div>
        </div>
        
        @if($expense->notes)
            <div class="mt-6 pt-6 border-t border-slate-200">
                <label class="block text-sm font-medium text-slate-700">Catatan</label>
                <p class="mt-1 text-sm text-slate-900">{{ $expense->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Delete Request Form -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-medium text-slate-800 mb-6">Permintaan Penghapusan</h3>
        
        <form method="POST" action="{{ route('expense-modifications.request-delete', $expense) }}" class="space-y-6">
            @csrf
            
            <!-- Deletion Reason -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label for="deletion_reason" class="block text-sm font-medium text-yellow-800 mb-2">
                    Alasan Penghapusan <span class="text-red-500">*</span>
                </label>
                <textarea name="deletion_reason" id="deletion_reason" rows="4" required
                          class="w-full px-3 py-2 border border-yellow-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('deletion_reason') border-red-500 @enderror"
                          placeholder="Jelaskan secara detail mengapa pengeluaran ini perlu dihapus. Sertakan alasan yang kuat karena penghapusan memerlukan persetujuan khusus.">{{ old('deletion_reason') }}</textarea>
                @error('deletion_reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-3 text-sm text-yellow-700">
                    <p class="font-medium mb-2">Contoh alasan yang valid:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Pengeluaran dicatat ganda (duplikasi)</li>
                        <li>Kesalahan input data yang signifikan</li>
                        <li>Pembatalan transaksi dari vendor</li>
                        <li>Pengeluaran tidak sesuai dengan proyek</li>
                    </ul>
                </div>
            </div>

            <!-- Approval Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Informasi Persetujuan</h4>
                <div class="text-sm text-blue-700 space-y-2">
                    <p>
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Permintaan penghapusan memerlukan persetujuan dari <strong>Finance Manager</strong> dan <strong>Direktur</strong>
                    </p>
                    <p>
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Proses persetujuan biasanya memakan waktu 1-3 hari kerja
                    </p>
                    <p>
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Anda akan menerima notifikasi email tentang status persetujuan
                    </p>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="confirm_deletion" name="confirm_deletion" type="checkbox" required
                           class="focus:ring-red-500 h-4 w-4 text-red-600 border-slate-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="confirm_deletion" class="font-medium text-slate-700">
                        Saya memahami bahwa penghapusan ini bersifat permanen dan tidak dapat dibatalkan setelah disetujui
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-slate-200">
                <a href="{{ route('expenses.show', $expense) }}"
                   class="w-full sm:w-auto px-6 py-2 border border-slate-300 text-slate-700 rounded-md hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                    Batal
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        onclick="return confirm('Apakah Anda yakin ingin mengajukan permintaan penghapusan untuk pengeluaran ini?')">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Ajukan Permintaan Hapus
                </button>
            </div>
        </form>
    </div>
</div>
@endsection