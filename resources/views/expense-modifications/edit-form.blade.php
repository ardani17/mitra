@extends('layouts.app')

@section('title', 'Permintaan Edit Pengeluaran')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Permintaan Edit Pengeluaran</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Ajukan permintaan untuk mengedit pengeluaran yang sudah disetujui</p>
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

    <!-- Current Expense Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-medium text-blue-900 mb-3">Data Pengeluaran Saat Ini</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-blue-800">Deskripsi:</span>
                <span class="text-blue-700">{{ $expense->description }}</span>
            </div>
            <div>
                <span class="font-medium text-blue-800">Jumlah:</span>
                <span class="text-blue-700">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="font-medium text-blue-800">Tanggal:</span>
                <span class="text-blue-700">{{ $expense->expense_date->format('d M Y') }}</span>
            </div>
            <div>
                <span class="font-medium text-blue-800">Kategori:</span>
                <span class="text-blue-700">{{ $categories[$expense->category] ?? $expense->category }}</span>
            </div>
            <div>
                <span class="font-medium text-blue-800">Vendor:</span>
                <span class="text-blue-700">{{ $expense->vendor ?? '-' }}</span>
            </div>
            <div>
                <span class="font-medium text-blue-800">No. Kuitansi:</span>
                <span class="text-blue-700">{{ $expense->receipt_number ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-medium text-slate-800 mb-6">Data Pengeluaran Baru</h3>
        
        <form method="POST" action="{{ route('expense-modifications.request-edit', $expense) }}" class="space-y-6">
            @csrf
            
            <!-- Project -->
            <div>
                <label for="project_id" class="block text-sm font-medium text-slate-700 mb-2">Proyek <span class="text-red-500">*</span></label>
                <select name="project_id" id="project_id" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror">
                    <option value="">Pilih Proyek</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="description" id="description" rows="3" required
                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="Deskripsi pengeluaran">{{ old('description', $expense->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-medium text-slate-700 mb-2">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="text" name="amount" id="amount" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                       placeholder="0"
                       value="{{ old('amount', number_format($expense->amount, 0, ',', '.')) }}">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expense Date -->
            <div>
                <label for="expense_date" class="block text-sm font-medium text-slate-700 mb-2">Tanggal Pengeluaran <span class="text-red-500">*</span></label>
                <input type="date" name="expense_date" id="expense_date" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expense_date') border-red-500 @enderror"
                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}">
                @error('expense_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                <select name="category" id="category" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category') border-red-500 @enderror">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Receipt Number -->
            <div>
                <label for="receipt_number" class="block text-sm font-medium text-slate-700 mb-2">Nomor Kuitansi</label>
                <input type="text" name="receipt_number" id="receipt_number"
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('receipt_number') border-red-500 @enderror"
                       placeholder="Nomor kuitansi"
                       value="{{ old('receipt_number', $expense->receipt_number) }}">
                @error('receipt_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Vendor -->
            <div>
                <label for="vendor" class="block text-sm font-medium text-slate-700 mb-2">Vendor</label>
                <input type="text" name="vendor" id="vendor"
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('vendor') border-red-500 @enderror"
                       placeholder="Nama vendor"
                       value="{{ old('vendor', $expense->vendor) }}">
                @error('vendor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">Catatan</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror"
                          placeholder="Catatan tambahan">{{ old('notes', $expense->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Modification Reason -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label for="modification_reason" class="block text-sm font-medium text-yellow-800 mb-2">Alasan Perubahan <span class="text-red-500">*</span></label>
                <textarea name="modification_reason" id="modification_reason" rows="3" required
                          class="w-full px-3 py-2 border border-yellow-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('modification_reason') border-red-500 @enderror"
                          placeholder="Jelaskan alasan mengapa pengeluaran ini perlu diubah">{{ old('modification_reason') }}</textarea>
                @error('modification_reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-yellow-700">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Permintaan edit akan memerlukan persetujuan dari finance manager dan/atau direktur.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-slate-200">
                <a href="{{ route('expenses.show', $expense) }}"
                   class="w-full sm:w-auto px-6 py-2 border border-slate-300 text-slate-700 rounded-md hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                    Batal
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Ajukan Permintaan Edit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format amount input
    const amountInput = document.getElementById('amount');
    
    amountInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        e.target.value = value;
    });
    
    // Remove formatting before form submission
    document.querySelector('form').addEventListener('submit', function() {
        amountInput.value = amountInput.value.replace(/[^\d]/g, '');
    });
});
</script>
@endsection