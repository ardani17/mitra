@extends('layouts.app')

@section('title', 'Tambah Transaksi Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Tambah Transaksi Cashflow</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Buat transaksi cashflow manual baru</p>
        </div>
        <div>
            <a href="{{ route('finance.cashflow.index') }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('finance.cashflow.store') }}" class="mobile-filter-form space-y-4 sm:space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="mobile-filter-row grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="form-group-mobile">
                    <label for="transaction_date" class="form-label-mobile">
                        Tanggal Transaksi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required
                           class="form-input-mobile @error('transaction_date') border-red-500 @enderror">
                    @error('transaction_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group-mobile">
                    <label for="type" class="form-label-mobile">
                        Tipe Transaksi <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="form-select-mobile @error('type') border-red-500 @enderror">
                        <option value="">Pilih Tipe</option>
                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mobile-filter-row grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="form-group-mobile">
                    <label for="category_id" class="form-label-mobile">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                            class="form-select-mobile @error('category_id') border-red-500 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    data-type="{{ $category->type }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->formatted_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group-mobile">
                    <label for="project_id" class="form-label-mobile">
                        Proyek (Opsional)
                    </label>
                    <select name="project_id" id="project_id"
                            class="form-select-mobile @error('project_id') border-red-500 @enderror">
                        <option value="">Pilih Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group-mobile">
                <label for="description" class="form-label-mobile">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="3" required
                          placeholder="Masukkan deskripsi transaksi..."
                          class="form-textarea-mobile @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mobile-filter-row grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="form-group-mobile">
                    <label for="amount" class="form-label-mobile">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-slate-500 text-sm">Rp</span>
                        </div>
                        <input type="number" name="amount" id="amount"
                               value="{{ old('amount') }}" required min="0" step="0.01"
                               placeholder="0"
                               class="form-input-mobile pl-10 @error('amount') border-red-500 @enderror">
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group-mobile">
                    <label for="payment_method" class="form-label-mobile">
                        Metode Pembayaran
                    </label>
                    <select name="payment_method" id="payment_method"
                            class="form-select-mobile @error('payment_method') border-red-500 @enderror">
                        <option value="">Pilih Metode</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Cek</option>
                        <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Kartu Kredit</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group-mobile">
                <label for="account_code" class="form-label-mobile">
                    Kode Akun (Opsional)
                </label>
                <input type="text" name="account_code" id="account_code"
                       value="{{ old('account_code') }}"
                       placeholder="Contoh: 1-1001"
                       class="form-input-mobile @error('account_code') border-red-500 @enderror">
                @error('account_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group-mobile">
                <label for="notes" class="form-label-mobile">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="Catatan tambahan..."
                          class="form-textarea-mobile @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preview Section -->
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200" id="preview-section" style="display: none;">
                <h4 class="text-sm font-medium text-slate-800 mb-3">Preview Transaksi</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
                    <div>
                        <span class="text-slate-600">Tipe:</span>
                        <span id="preview-type" class="ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Kategori:</span>
                        <span id="preview-category" class="ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Jumlah:</span>
                        <span id="preview-amount" class="ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Proyek:</span>
                        <span id="preview-project" class="ml-2 font-medium"></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mobile-filter-actions flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-slate-200">
                <a href="{{ route('finance.cashflow.index') }}"
                   class="btn-secondary-mobile sm:btn-secondary sm:w-auto text-center">
                    Batal
                </a>
                <button type="submit"
                        class="btn-primary-mobile sm:btn-primary sm:w-auto flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');
    const projectSelect = document.getElementById('project_id');
    const amountInput = document.getElementById('amount');
    const previewSection = document.getElementById('preview-section');

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
    [typeSelect, categorySelect, projectSelect, amountInput].forEach(element => {
        element.addEventListener('change', updatePreview);
        element.addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const type = typeSelect.value;
        const categoryOption = categorySelect.querySelector('option:checked');
        const projectOption = projectSelect.querySelector('option:checked');
        const amount = amountInput.value;

        if (type && categoryOption && categoryOption.value && amount) {
            previewSection.style.display = 'block';
            
            document.getElementById('preview-type').textContent = type === 'income' ? 'Pemasukan' : 'Pengeluaran';
            document.getElementById('preview-type').className = `ml-2 font-medium ${type === 'income' ? 'text-green-600' : 'text-red-600'}`;
            
            document.getElementById('preview-category').textContent = categoryOption.textContent;
            document.getElementById('preview-project').textContent = projectOption && projectOption.value ? projectOption.textContent : 'Tidak ada';
            
            const formattedAmount = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
            
            document.getElementById('preview-amount').textContent = formattedAmount;
            document.getElementById('preview-amount').className = `ml-2 font-medium ${type === 'income' ? 'text-green-600' : 'text-red-600'}`;
        } else {
            previewSection.style.display = 'none';
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