@extends('layouts.app')

@section('title', 'Edit Transaksi Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Edit Transaksi Cashflow</h1>
            <p class="text-slate-600 mt-1">ID: #{{ $cashflow->id }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('finance.cashflow.show', $cashflow) }}" 
               class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <form method="POST" action="{{ route('finance.cashflow.update', $cashflow) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Warning for auto-generated entries -->
            @if($cashflow->reference_type !== 'manual')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Transaksi ini dibuat otomatis dari sistem. Perubahan yang Anda lakukan mungkin akan ditimpa jika status referensi berubah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-slate-700 mb-2">
                        Tanggal Transaksi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date" 
                           value="{{ old('transaction_date', $cashflow->transaction_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('transaction_date') border-red-500 @enderror">
                    @error('transaction_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">
                        Tipe Transaksi <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror">
                        <option value="">Pilih Tipe</option>
                        <option value="income" {{ old('type', $cashflow->type) === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ old('type', $cashflow->type) === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category_id') border-red-500 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    data-type="{{ $category->type }}"
                                    {{ old('category_id', $cashflow->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->formatted_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="project_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Proyek (Opsional)
                    </label>
                    <select name="project_id" id="project_id"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('project_id') border-red-500 @enderror">
                        <option value="">Pilih Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $cashflow->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="3" required
                          placeholder="Masukkan deskripsi transaksi..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $cashflow->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="amount" class="block text-sm font-medium text-slate-700 mb-2">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-slate-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" name="amount" id="amount" 
                               value="{{ old('amount', $cashflow->amount) }}" required min="0" step="0.01"
                               placeholder="0"
                               class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror">
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-slate-700 mb-2">
                        Metode Pembayaran
                    </label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_method') border-red-500 @enderror">
                        <option value="">Pilih Metode</option>
                        <option value="cash" {{ old('payment_method', $cashflow->payment_method) === 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="bank_transfer" {{ old('payment_method', $cashflow->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="check" {{ old('payment_method', $cashflow->payment_method) === 'check' ? 'selected' : '' }}>Cek</option>
                        <option value="credit_card" {{ old('payment_method', $cashflow->payment_method) === 'credit_card' ? 'selected' : '' }}>Kartu Kredit</option>
                        <option value="other" {{ old('payment_method', $cashflow->payment_method) === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="account_code" class="block text-sm font-medium text-slate-700 mb-2">
                    Kode Akun (Opsional)
                </label>
                <input type="text" name="account_code" id="account_code" 
                       value="{{ old('account_code', $cashflow->account_code) }}"
                       placeholder="Contoh: 1-1001"
                       class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_code') border-red-500 @enderror">
                @error('account_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="Catatan tambahan..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror">{{ old('notes', $cashflow->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current vs New Comparison -->
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <h4 class="text-sm font-medium text-slate-800 mb-3">Perbandingan Perubahan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h5 class="text-xs font-medium text-slate-600 mb-2">Data Saat Ini</h5>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Tipe:</span>
                                <span class="font-medium {{ $cashflow->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $cashflow->formatted_type }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Jumlah:</span>
                                <span class="font-medium {{ $cashflow->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $cashflow->formatted_amount }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Kategori:</span>
                                <span class="font-medium">{{ $cashflow->category->name }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="text-xs font-medium text-slate-600 mb-2">Preview Perubahan</h5>
                        <div class="space-y-1 text-sm" id="preview-changes">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Tipe:</span>
                                <span id="preview-type" class="font-medium">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Jumlah:</span>
                                <span id="preview-amount" class="font-medium">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Kategori:</span>
                                <span id="preview-category" class="font-medium">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-200">
                <a href="{{ route('finance.cashflow.show', $cashflow) }}" 
                   class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-medium transition-colors duration-200">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');
    const amountInput = document.getElementById('amount');

    // Filter categories based on selected type
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const categoryOptions = categorySelect.querySelectorAll('option');
        
        categoryOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const optionType = option.dataset.type;
            if (selectedType === '' || optionType === selectedType) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset category selection if current selection is not compatible
        const currentCategory = categorySelect.querySelector('option:checked');
        if (currentCategory && currentCategory.dataset.type !== selectedType && selectedType !== '') {
            categorySelect.value = '';
        }
        
        updatePreview();
    });

    // Update preview when form changes
    [typeSelect, categorySelect, amountInput].forEach(element => {
        element.addEventListener('change', updatePreview);
        element.addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const type = typeSelect.value;
        const categoryOption = categorySelect.querySelector('option:checked');
        const amount = amountInput.value;

        if (type) {
            document.getElementById('preview-type').textContent = type === 'income' ? 'Pemasukan' : 'Pengeluaran';
            document.getElementById('preview-type').className = `font-medium ${type === 'income' ? 'text-green-600' : 'text-red-600'}`;
        } else {
            document.getElementById('preview-type').textContent = '-';
            document.getElementById('preview-type').className = 'font-medium';
        }
        
        if (categoryOption && categoryOption.value) {
            document.getElementById('preview-category').textContent = categoryOption.textContent;
        } else {
            document.getElementById('preview-category').textContent = '-';
        }
        
        if (amount) {
            const formattedAmount = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
            
            document.getElementById('preview-amount').textContent = formattedAmount;
            document.getElementById('preview-amount').className = `font-medium ${type === 'income' ? 'text-green-600' : 'text-red-600'}`;
        } else {
            document.getElementById('preview-amount').textContent = '-';
            document.getElementById('preview-amount').className = 'font-medium';
        }
    }

    // Format amount input
    amountInput.addEventListener('input', function() {
        // Remove non-numeric characters except decimal point
        this.value = this.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = this.value.split('.');
        if (parts.length > 2) {
            this.value = parts[0] + '.' + parts.slice(1).join('');
        }
    });

    // Initialize preview on page load
    updatePreview();
});
</script>

@endsection