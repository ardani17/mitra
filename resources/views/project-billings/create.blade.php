@extends('layouts.app')

@section('title', 'Buat Penagihan Proyek')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Buat Penagihan Proyek</h1>
            <p class="text-sm sm:text-base text-slate-600 mt-1">Buat penagihan termin/cicilan untuk proyek</p>
        </div>
        <a href="{{ route('project-billings.index') }}"
           class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="hidden sm:inline">Kembali</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <form action="{{ route('project-billings.store') }}" method="POST" class="space-y-4 sm:space-y-6" id="billing-form">
        @csrf
        
        <!-- Project Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-3 sm:mb-4">Informasi Proyek</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Pilih Proyek <span class="text-red-500">*</span>
                    </label>
                    <select name="project_id" id="project_id" required
                            class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('project_id') border-red-500 @enderror">
                        <option value="">Pilih Proyek...</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" 
                                    data-nilai-jasa="{{ $project->nilai_jasa }}"
                                    data-nilai-material="{{ $project->nilai_material }}"
                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} - {{ $project->code }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_type" class="block text-sm font-medium text-slate-700 mb-2">
                        Tipe Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_type" id="payment_type" required
                            class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_type') border-red-500 @enderror">
                        <option value="termin" selected>Pembayaran Termin (Cicilan)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Pembayaran per proyek selalu menggunakan sistem termin/cicilan</p>
                    @error('payment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Project Info Display -->
            <div id="project-info" class="mt-4 p-3 sm:p-4 bg-slate-50 rounded-lg hidden">
                <h3 class="text-sm sm:text-base font-medium text-slate-800 mb-2">Informasi Nilai Proyek</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-600">Nilai Jasa:</span>
                        <span id="display-nilai-jasa" class="font-medium text-slate-900 ml-2">-</span>
                    </div>
                    <div>
                        <span class="text-slate-600">Nilai Material:</span>
                        <span id="display-nilai-material" class="font-medium text-slate-900 ml-2">-</span>
                    </div>
                    <div>
                        <span class="text-slate-600">Total Nilai:</span>
                        <span id="display-total" class="font-medium text-slate-900 ml-2">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Termin Information -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-3 sm:mb-4">Informasi Termin</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <div>
                    <label for="termin_number" class="block text-sm font-medium text-slate-700 mb-2">
                        Termin Ke- <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="termin_number" id="termin_number" min="1" required
                           value="{{ old('termin_number', 1) }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('termin_number') border-red-500 @enderror">
                    <p class="text-xs text-slate-500 mt-1">Nomor urut termin ini</p>
                    @error('termin_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="total_termin" class="block text-sm font-medium text-slate-700 mb-2">
                        Total Termin <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="total_termin" id="total_termin" min="1" required
                           value="{{ old('total_termin', 1) }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('total_termin') border-red-500 @enderror">
                    <p class="text-xs text-slate-500 mt-1">Total jumlah termin keseluruhan</p>
                    @error('total_termin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_final_termin" id="is_final_termin" value="1"
                               {{ old('is_final_termin') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Ini adalah termin terakhir<br><span class="text-xs text-slate-500">(pelunasan proyek)</span></span>
                    </label>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium">Contoh Sistem Termin:</p>
                        <p class="mt-1">Proyek senilai 60jt → Termin 1: 10jt, Termin 2: 20jt, Termin 3: 30jt (final)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Details -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-3 sm:mb-4">Detail Penagihan</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-slate-700 mb-2">
                        Nomor Invoice <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="invoice_number" id="invoice_number" required
                           value="{{ old('invoice_number') }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('invoice_number') border-red-500 @enderror"
                           placeholder="Contoh: INV-2025-001">
                    @error('invoice_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="billing_date" class="block text-sm font-medium text-slate-700 mb-2">
                        Tanggal Penagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="billing_date" id="billing_date" required
                           value="{{ old('billing_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('billing_date') border-red-500 @enderror">
                    @error('billing_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror">
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Amount Configuration -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-3 sm:mb-4">Konfigurasi Nilai</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="nilai_jasa_display" class="block text-sm font-medium text-slate-700 mb-2">
                        Nilai Jasa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nilai_jasa_display"
                           value="{{ old('nilai_jasa') ? number_format(old('nilai_jasa'), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nilai_jasa') border-red-500 @enderror"
                           placeholder="5.000.000">
                    <input type="hidden" name="nilai_jasa" id="nilai_jasa" value="{{ old('nilai_jasa', 0) }}">
                    @error('nilai_jasa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nilai_material_display" class="block text-sm font-medium text-slate-700 mb-2">
                        Nilai Material <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nilai_material_display"
                           value="{{ old('nilai_material') ? number_format(old('nilai_material'), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nilai_material') border-red-500 @enderror"
                           placeholder="0">
                    <input type="hidden" name="nilai_material" id="nilai_material" value="{{ old('nilai_material', 0) }}">
                    @error('nilai_material')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- PPN Configuration -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
                <div>
                    <label for="ppn_rate" class="block text-sm font-medium text-slate-700 mb-2">
                        PPN (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ppn_rate" id="ppn_rate" required min="0" max="100" step="0.01"
                           value="{{ old('ppn_rate', '11') }}"
                           class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ppn_rate') border-red-500 @enderror">
                    @error('ppn_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ppn_calculation" class="block text-sm font-medium text-slate-700 mb-2">
                        Metode Perhitungan PPN <span class="text-red-500">*</span>
                    </label>
                    <select name="ppn_calculation" id="ppn_calculation" required
                            class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ppn_calculation') border-red-500 @enderror">
                        <option value="normal" {{ old('ppn_calculation', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="round_up" {{ old('ppn_calculation') == 'round_up' ? 'selected' : '' }}>Pembulatan Ke Atas</option>
                        <option value="round_down" {{ old('ppn_calculation') == 'round_down' ? 'selected' : '' }}>Pembulatan Ke Bawah</option>
                    </select>
                    @error('ppn_calculation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Total Amount Display -->
            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-slate-50 rounded-lg">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-600">Subtotal:</span>
                        <span id="subtotal-display" class="text-sm sm:text-base font-medium text-slate-900">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-600">PPN:</span>
                        <span id="ppn-display" class="text-sm sm:text-base font-medium text-slate-900">Rp 0</span>
                    </div>
                    <hr class="border-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-base sm:text-lg font-medium text-slate-700">Total Nilai:</span>
                        <span id="total-display" class="text-lg sm:text-2xl font-bold text-slate-900">Rp 0</span>
                    </div>
                </div>
                
                <!-- Hidden fields for calculated values -->
                <input type="hidden" name="subtotal" id="subtotal">
                <input type="hidden" name="ppn_amount" id="ppn_amount">
                <input type="hidden" name="total_amount" id="total_amount">
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-3 sm:mb-4">Deskripsi</h2>
            
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                    Deskripsi Penagihan
                </label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-3 py-2 text-sm sm:text-base border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Masukkan deskripsi atau catatan untuk penagihan ini...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('project-billings.index') }}"
               class="px-4 sm:px-6 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 rounded-lg font-medium transition-colors duration-200 text-center text-sm sm:text-base">
                Batal
            </a>
            <button type="submit"
                    class="px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 text-sm sm:text-base">
                Simpan Penagihan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const projectSelect = document.getElementById('project_id');
    const projectInfo = document.getElementById('project-info');
    const nilaiJasaDisplay = document.getElementById('nilai_jasa_display');
    const nilaiJasa = document.getElementById('nilai_jasa');
    const nilaiMaterialDisplay = document.getElementById('nilai_material_display');
    const nilaiMaterial = document.getElementById('nilai_material');
    const ppnRate = document.getElementById('ppn_rate');
    const ppnCalculation = document.getElementById('ppn_calculation');
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

    // Handle project selection
    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const nilaiJasaValue = parseInt(selectedOption.dataset.nilaiJasa) || 0;
            const nilaiMaterialValue = parseInt(selectedOption.dataset.nilaiMaterial) || 0;
            const totalValue = nilaiJasaValue + nilaiMaterialValue;

            // Update display
            document.getElementById('display-nilai-jasa').textContent = formatCurrency(nilaiJasaValue);
            document.getElementById('display-nilai-material').textContent = formatCurrency(nilaiMaterialValue);
            document.getElementById('display-total').textContent = formatCurrency(totalValue);

            // Update input fields
            if (nilaiJasaValue > 0) {
                nilaiJasaDisplay.value = formatNumber(nilaiJasaValue);
                nilaiJasa.value = nilaiJasaValue;
            }
            if (nilaiMaterialValue > 0) {
                nilaiMaterialDisplay.value = formatNumber(nilaiMaterialValue);
                nilaiMaterial.value = nilaiMaterialValue;
            }

            projectInfo.classList.remove('hidden');
        } else {
            projectInfo.classList.add('hidden');
            nilaiJasaDisplay.value = '';
            nilaiJasa.value = 0;
            nilaiMaterialDisplay.value = '';
            nilaiMaterial.value = 0;
        }
        
        calculateTotal();
    });

    // Add event listeners for PPN changes
    ppnRate.addEventListener('input', calculateTotal);
    ppnCalculation.addEventListener('change', calculateTotal);


    // Form validation before submit
    form.addEventListener('submit', function(e) {
        const jasaValue = parseInt(nilaiJasa.value) || 0;
        const materialValue = parseInt(nilaiMaterial.value) || 0;
        
        if (jasaValue < 1) {
            e.preventDefault();
            alert('Nilai Jasa harus diisi dan minimal 1');
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
        
        // Debug log
        console.log('Form data being submitted:', {
            project_id: document.getElementById('project_id').value,
            payment_type: document.getElementById('payment_type').value,
            invoice_number: document.getElementById('invoice_number').value,
            nilai_jasa: nilaiJasa.value,
            nilai_material: nilaiMaterial.value,
            subtotal: document.getElementById('subtotal').value,
            ppn_amount: document.getElementById('ppn_amount').value,
            total_amount: document.getElementById('total_amount').value,
            billing_date: document.getElementById('billing_date').value,
            status: document.getElementById('status').value
        });
    });

    // Initialize if project is already selected (for old input)
    if (projectSelect.value) {
        projectSelect.dispatchEvent(new Event('change'));
    }

    // Initial calculation
    calculateTotal();
});
</script>
@endsection