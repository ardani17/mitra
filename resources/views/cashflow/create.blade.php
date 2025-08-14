@extends('layouts.app')

@section('title', 'Tambah Transaksi Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Tambah Transaksi Cashflow</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Buat transaksi cashflow manual baru</p>
        </div>
        <a href="{{ route('finance.cashflow.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Kembali ke Cashflow</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <form method="POST" action="{{ route('finance.cashflow.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Transaksi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('transaction_date') border-red-500 @enderror">
                    @error('transaction_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipe Transaksi <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('type') border-red-500 @enderror">
                        <option value="">Pilih Tipe</option>
                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('category_id') border-red-500 @enderror">
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

                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Proyek (Opsional)
                    </label>
                    <select name="project_id" id="project_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('project_id') border-red-500 @enderror">
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
                
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="amount" id="amount"
                           value="{{ old('amount') }}" required
                           placeholder="Masukkan jumlah..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('amount') border-red-500 @enderror"
                           oninput="formatCurrency(this)">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                        Metode Pembayaran
                    </label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('payment_method') border-red-500 @enderror">
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
            
            <div class="mt-4 sm:mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                          placeholder="Masukkan deskripsi transaksi..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 sm:mt-6">
                <label for="account_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Akun (Opsional)
                </label>
                <input type="text" name="account_code" id="account_code"
                       value="{{ old('account_code') }}"
                       placeholder="Contoh: 1-1001"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('account_code') border-red-500 @enderror">
                @error('account_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 sm:mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="Catatan tambahan..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preview Section -->
            <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4" id="preview-section" style="display: none;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Preview Transaksi</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <span class="text-blue-600">Tipe:</span>
                                    <span id="preview-type" class="ml-2 font-medium"></span>
                                </div>
                                <div>
                                    <span class="text-blue-600">Kategori:</span>
                                    <span id="preview-category" class="ml-2 font-medium"></span>
                                </div>
                                <div>
                                    <span class="text-blue-600">Jumlah:</span>
                                    <span id="preview-amount" class="ml-2 font-medium"></span>
                                </div>
                                <div>
                                    <span class="text-blue-600">Proyek:</span>
                                    <span id="preview-project" class="ml-2 font-medium"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('finance.cashflow.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base order-1 sm:order-2">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
    
    <!-- Information Box -->
    <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi Transaksi</h3>
                <div class="mt-2 text-xs sm:text-sm text-blue-700">
                    <p class="hidden sm:block">Transaksi cashflow manual akan dibuat dengan status "Dikonfirmasi" dan langsung mempengaruhi laporan keuangan.</p>
                    <p class="sm:hidden">Transaksi akan dibuat dengan status "Dikonfirmasi".</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format currency function
function formatCurrency(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/\D/g, '');
    
    // Format dengan titik sebagai pemisah ribuan
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    // Set nilai yang sudah diformat
    input.value = value;
}

// Saat form disubmit, hapus format untuk mengirim angka murni
document.querySelector('form').addEventListener('submit', function(e) {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        // Hapus titik pemisah ribuan sebelum submit
        let rawValue = amountInput.value.replace(/\./g, '');
        amountInput.value = rawValue;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');
    const projectSelect = document.getElementById('project_id');
    const amountInput = document.getElementById('amount');
    const previewSection = document.getElementById('preview-section');

    // Format amount input on page load if it has value
    if (amountInput && amountInput.value) {
        formatCurrency(amountInput);
    }

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
        const amount = amountInput.value.replace(/\./g, ''); // Remove dots for calculation

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

    // Initialize preview on page load
    updatePreview();
});
</script>

@endsection