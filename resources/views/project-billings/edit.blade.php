@extends('layouts.app')

@section('title', 'Edit Penagihan Proyek')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800">Edit Penagihan Proyek</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base break-words">Edit penagihan untuk proyek {{ $projectBilling->project->name }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('project-billings.show', $projectBilling) }}"
               class="bg-slate-600 hover:bg-slate-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Lihat
            </a>
            <a href="{{ route('project-billings.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('project-billings.update', $projectBilling) }}" method="POST" class="space-y-4 sm:space-y-6" id="billing-form">
        @csrf
        @method('PUT')
        
        <!-- Project Info (Read-only) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Informasi Proyek</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 p-3 sm:p-4 bg-slate-50 rounded-lg">
                <div>
                    <span class="text-slate-600 text-xs sm:text-sm">Nama Proyek:</span>
                    <p class="font-medium text-slate-900 text-sm sm:text-base break-words">{{ $projectBilling->project->name }}</p>
                </div>
                <div>
                    <span class="text-slate-600 text-xs sm:text-sm">Kode Proyek:</span>
                    <p class="font-medium text-slate-900 text-sm sm:text-base">{{ $projectBilling->project->code }}</p>
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <span class="text-slate-600 text-xs sm:text-sm">Tipe Pembayaran:</span>
                    <p class="font-medium text-slate-900 text-sm sm:text-base">
                        {{ $projectBilling->payment_type == 'full' ? 'Pembayaran Penuh' : 'Pembayaran Termin' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Billing Details -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Detail Penagihan</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="invoice_number" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Nomor Invoice <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="invoice_number" id="invoice_number" required
                           value="{{ old('invoice_number', $projectBilling->invoice_number) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('invoice_number') border-red-500 @enderror"
                           placeholder="Contoh: INV-2025-001">
                    @error('invoice_number')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="billing_date" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Tanggal Penagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="billing_date" id="billing_date" required
                           value="{{ old('billing_date', $projectBilling->billing_date) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('billing_date') border-red-500 @enderror">
                    @error('billing_date')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="status" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('status') border-red-500 @enderror">
                        <option value="draft" {{ old('status', $projectBilling->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status', $projectBilling->status) == 'sent' ? 'selected' : '' }}>Terkirim</option>
                        <option value="paid" {{ old('status', $projectBilling->status) == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="overdue" {{ old('status', $projectBilling->status) == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Paid Date (shown when status is paid) -->
            <div id="paid-date-section" class="mt-4 sm:mt-6 {{ old('status', $projectBilling->status) == 'paid' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="paid_date" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="paid_date" id="paid_date"
                               value="{{ old('paid_date', $projectBilling->paid_date) }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('paid_date') border-red-500 @enderror">
                        <p class="text-xs text-slate-500 mt-1">Tanggal ketika pembayaran diterima (tidak bisa tanggal masa depan)</p>
                        @error('paid_date')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Configuration -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Konfigurasi Nilai</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="nilai_jasa_display" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Nilai Jasa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nilai_jasa_display"
                           value="{{ old('nilai_jasa') ? number_format(old('nilai_jasa'), 0, ',', '.') : number_format($projectBilling->nilai_jasa, 0, ',', '.') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('nilai_jasa') border-red-500 @enderror"
                           placeholder="5.000.000">
                    <input type="hidden" name="nilai_jasa" id="nilai_jasa" value="{{ old('nilai_jasa', $projectBilling->nilai_jasa) }}">
                    @error('nilai_jasa')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nilai_material_display" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Nilai Material <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nilai_material_display"
                           value="{{ old('nilai_material') ? number_format(old('nilai_material'), 0, ',', '.') : number_format($projectBilling->nilai_material, 0, ',', '.') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('nilai_material') border-red-500 @enderror"
                           placeholder="0">
                    <input type="hidden" name="nilai_material" id="nilai_material" value="{{ old('nilai_material', $projectBilling->nilai_material) }}">
                    @error('nilai_material')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- PPN Configuration -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
                <div>
                    <label for="ppn_rate" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        PPN (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ppn_rate" id="ppn_rate" required min="0" max="100" step="0.01"
                           value="{{ old('ppn_rate', $projectBilling->ppn_rate) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('ppn_rate') border-red-500 @enderror">
                    @error('ppn_rate')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ppn_calculation" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Metode Perhitungan PPN <span class="text-red-500">*</span>
                    </label>
                    <select name="ppn_calculation" id="ppn_calculation" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('ppn_calculation') border-red-500 @enderror">
                        <option value="normal" {{ old('ppn_calculation', $projectBilling->ppn_calculation) == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="round_up" {{ old('ppn_calculation', $projectBilling->ppn_calculation) == 'round_up' ? 'selected' : '' }}>Pembulatan Ke Atas</option>
                        <option value="round_down" {{ old('ppn_calculation', $projectBilling->ppn_calculation) == 'round_down' ? 'selected' : '' }}>Pembulatan Ke Bawah</option>
                    </select>
                    @error('ppn_calculation')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Total Amount Display -->
            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-slate-50 rounded-lg">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-600">Subtotal:</span>
                        <span id="subtotal-display" class="font-medium text-slate-900 text-sm sm:text-base">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-600">PPN:</span>
                        <span id="ppn-display" class="font-medium text-slate-900 text-sm sm:text-base">Rp 0</span>
                    </div>
                    <hr class="border-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-base sm:text-lg font-medium text-slate-700">Total Nilai:</span>
                        <span id="total-display" class="text-lg sm:text-2xl font-bold text-slate-900">Rp 0</span>
                    </div>
                </div>
                
                <!-- Hidden fields for calculated values -->
                <input type="hidden" name="subtotal" id="subtotal" value="{{ old('subtotal', $projectBilling->subtotal) }}">
                <input type="hidden" name="ppn_amount" id="ppn_amount" value="{{ old('ppn_amount', $projectBilling->ppn_amount) }}">
                <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', $projectBilling->total_amount) }}">
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Deskripsi</h2>
            
            <div>
                <label for="description" class="block text-xs sm:text-sm font-medium text-slate-700 mb-2">
                    Deskripsi Penagihan
                </label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base @error('description') border-red-500 @enderror"
                          placeholder="Masukkan deskripsi atau catatan untuk penagihan ini...">{{ old('description', $projectBilling->notes) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('project-billings.show', $projectBilling) }}"
               class="px-4 sm:px-6 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 rounded-lg font-medium transition-colors duration-200 text-center text-sm sm:text-base order-2 sm:order-1">
                Batal
            </a>
            <button type="submit"
                    class="px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 text-sm sm:text-base order-1 sm:order-2">
                Update Penagihan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const nilaiJasaDisplay = document.getElementById('nilai_jasa_display');
    const nilaiJasa = document.getElementById('nilai_jasa');
    const nilaiMaterialDisplay = document.getElementById('nilai_material_display');
    const nilaiMaterial = document.getElementById('nilai_material');
    const ppnRate = document.getElementById('ppn_rate');
    const ppnCalculation = document.getElementById('ppn_calculation');
    const statusSelect = document.getElementById('status');
    const paidDateSection = document.getElementById('paid-date-section');
    const form = document.getElementById('billing-form');

    // Format number with Indonesian thousand separator
    function formatNumber(num) {
        if (!num || num === 0) return '';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Parse formatted number to raw number
    function parseNumber(str) {
        if (!str) return 0;
        return parseInt(str.replace(/\./g, '')) || 0;
    }

    // Format currency display
    function formatCurrency(num) {
        if (!num || num === 0) return 'Rp 0';
        return 'Rp ' + formatNumber(num);
    }

    // Setup currency input
    function setupCurrencyInput(displayInput, hiddenInput) {
        displayInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            let numericValue = parseInt(value) || 0;
            
            hiddenInput.value = numericValue;
            
            if (numericValue > 0) {
                this.value = formatNumber(numericValue);
            } else {
                this.value = '';
            }
            
            calculateTotal();
        });

        displayInput.addEventListener('focus', function() {
            let rawValue = hiddenInput.value;
            if (rawValue && rawValue > 0) {
                this.value = rawValue;
            }
        });

        displayInput.addEventListener('blur', function() {
            let value = this.value.replace(/[^\d]/g, '');
            let numericValue = parseInt(value) || 0;
            
            hiddenInput.value = numericValue;
            
            if (numericValue > 0) {
                this.value = formatNumber(numericValue);
            } else {
                this.value = '';
            }
            calculateTotal();
        });
    }

    // Calculate total amounts
    function calculateTotal() {
        const jasaValue = parseInt(nilaiJasa.value) || 0;
        const materialValue = parseInt(nilaiMaterial.value) || 0;
        const subtotal = jasaValue + materialValue;
        const ppnRateValue = parseFloat(ppnRate.value) || 0;
        
        let ppnAmount = (subtotal * ppnRateValue) / 100;
        
        // Apply PPN calculation method
        const ppnCalc = ppnCalculation.value;
        if (ppnCalc === 'round_up') {
            ppnAmount = Math.ceil(ppnAmount);
        } else if (ppnCalc === 'round_down') {
            ppnAmount = Math.floor(ppnAmount);
        } else {
            ppnAmount = Math.round(ppnAmount);
        }
        
        const total = subtotal + ppnAmount;
        
        // Update displays
        document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
        document.getElementById('ppn-display').textContent = formatCurrency(ppnAmount);
        document.getElementById('total-display').textContent = formatCurrency(total);
        
        // Update hidden fields
        document.getElementById('subtotal').value = subtotal;
        document.getElementById('ppn_amount').value = ppnAmount;
        document.getElementById('total_amount').value = total;
    }

    // Initialize currency inputs
    setupCurrencyInput(nilaiJasaDisplay, nilaiJasa);
    setupCurrencyInput(nilaiMaterialDisplay, nilaiMaterial);

    // Handle status change for paid date
    statusSelect.addEventListener('change', function() {
        if (this.value === 'paid') {
            paidDateSection.classList.remove('hidden');
            document.getElementById('paid_date').required = true;
        } else {
            paidDateSection.classList.add('hidden');
            document.getElementById('paid_date').required = false;
        }
    });

    // Add event listeners for PPN changes
    ppnRate.addEventListener('input', calculateTotal);
    ppnCalculation.addEventListener('change', calculateTotal);

    // Set max date for paid_date to today
    const today = new Date().toISOString().split('T')[0];
    const paidDateInput = document.getElementById('paid_date');
    if (paidDateInput) {
        paidDateInput.setAttribute('max', today);
    }

    // Set minimum due date to billing date
    const billingDateInput = document.getElementById('billing_date');
    const dueDateInput = document.getElementById('due_date');

    billingDateInput.addEventListener('change', function() {
        dueDateInput.min = this.value;
        if (dueDateInput.value && dueDateInput.value < this.value) {
            dueDateInput.value = this.value;
        }
    });

    // Form validation before submit
    form.addEventListener('submit', function(e) {
        const jasaValue = parseInt(nilaiJasa.value) || 0;
        const materialValue = parseInt(nilaiMaterial.value) || 0;
        
        if (jasaValue <= 0) {
            e.preventDefault();
            alert('Nilai Jasa harus diisi dan lebih dari 0');
            nilaiJasaDisplay.focus();
            return false;
        }
        
        if (materialValue < 0) {
            e.preventDefault();
            alert('Nilai Material tidak boleh negatif');
            nilaiMaterialDisplay.focus();
            return false;
        }
        
        // Ensure all calculations are up to date
        calculateTotal();
    });

    // Initial calculation
    calculateTotal();
});
</script>
@endsection