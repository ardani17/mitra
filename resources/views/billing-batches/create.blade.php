@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 max-w-6xl">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Buat Batch Penagihan Baru</h1>
        <a href="{{ route('billing-batches.index') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
            Kembali
        </a>
    </div>

    <div class="card p-4 sm:p-6">
        <form action="{{ route('billing-batches.store') }}" method="POST" id="batchForm">
            @csrf
            
            <!-- Batch Information -->
            <div class="mb-6 sm:mb-8">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Batch</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tanggal Penagihan *</label>
                        <input type="date" name="billing_date" id="billing_date" required
                               class="form-input w-full text-sm"
                               value="{{ old('billing_date', date('Y-m-d')) }}">
                        @error('billing_date')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Rate PPh (%) *</label>
                        <input type="number" name="pph_rate" id="pph_rate" step="0.01" min="0" max="100" required
                               class="form-input w-full text-sm"
                               value="{{ old('pph_rate', '2') }}" onchange="calculateTotals()" oninput="calculateTotals()"
                               placeholder="Contoh: 1.75">
                        @error('pph_rate')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Masukkan rate PPh dalam persen (contoh: 1.75 untuk 1.75%)</p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Rate PPN (%) *</label>
                        <input type="number" name="ppn_rate" id="ppn_rate" step="0.01" min="0" max="100" required
                               class="form-input w-full text-sm"
                               value="{{ old('ppn_rate', '11') }}" onchange="calculateTotals()" oninput="calculateTotals()"
                               placeholder="Contoh: 11.50">
                        @error('ppn_rate')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Masukkan rate PPN dalam persen (contoh: 11.50 untuk 11.50%)</p>
                    </div>
                </div>

                <!-- Document Numbers -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">No SP *</label>
                        <input type="text" name="sp_number" id="sp_number" required
                               class="form-input w-full text-sm"
                               value="{{ old('sp_number') }}"
                               placeholder="Masukkan nomor SP">
                        @error('sp_number')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">No Invoice *</label>
                        <input type="text" name="invoice_number" id="invoice_number" required
                               class="form-input w-full text-sm"
                               value="{{ old('invoice_number') }}"
                               placeholder="Masukkan nomor invoice">
                        @error('invoice_number')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="form-input w-full text-sm"
                        placeholder="Catatan untuk batch penagihan ini...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Project Selection -->
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">Pilih Proyek untuk Penagihan</h3>
                    @if($availableProjects->count() > 0)
                        <button type="button" onclick="selectAll()" class="text-xs sm:text-sm text-blue-600 hover:text-blue-800 text-left sm:text-right">
                            Pilih Semua
                        </button>
                    @endif
                </div>
                
                @if($availableProjects->count() > 0)
                    <div class="space-y-3 sm:space-y-4">
                        @foreach($availableProjects as $project)
                            <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-gray-50">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="projects[]" value="{{ $project->id }}"
                                           class="mt-1 project-checkbox" onchange="calculateTotals()"
                                           data-final-service="{{ $project->final_service_value }}"
                                           data-final-material="{{ $project->final_material_value }}"
                                           id="project_{{ $project->id }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{ $project->code }} - {{ $project->name }}</h4>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-500 break-words">{{ $project->client_name }}</p>
                                        <div class="mt-2 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 text-xs text-gray-600">
                                            <div>
                                                <span class="font-medium">Nilai Jasa:</span><br>
                                                <span class="break-words">Rp {{ number_format($project->final_service_value, 0, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Nilai Material:</span><br>
                                                <span class="break-words">Rp {{ number_format($project->final_material_value, 0, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Total DPP:</span><br>
                                                <span class="break-words">Rp {{ number_format($project->final_service_value + $project->final_material_value, 0, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Status:</span><br>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                    @if($project->status == 'selesai') bg-green-100 text-green-800
                                                    @elseif($project->status == 'berjalan') bg-blue-100 text-blue-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($project->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('projects')
                        <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <div class="text-center py-6 sm:py-8">
                        <svg class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada proyek tersedia</h3>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">Semua proyek sudah ditagih atau belum memiliki nilai final.</p>
                        <div class="mt-4 sm:mt-6">
                            <a href="{{ route('projects.index') }}" class="btn-primary text-xs sm:text-sm py-2 px-3 sm:px-4">
                                Lihat Proyek
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Calculation Summary -->
            <div class="mb-6 sm:mb-8 p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-3 text-sm sm:text-base">Ringkasan Perhitungan Batch</h4>
                
                <!-- Client Type Selection -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <h5 class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Pilih Tipe Klien untuk Batch:</h5>
                    <div class="flex flex-col sm:flex-row sm:space-x-6 space-y-2 sm:space-y-0 text-xs sm:text-sm">
                        <div class="flex items-center">
                            <input type="radio" name="client_type" value="wapu" id="batch-wapu"
                                   class="mr-2" onchange="updateBatchClientType('wapu')">
                            <label for="batch-wapu" class="text-blue-600 cursor-pointer">
                                WAPU (<span id="wapu-count">0</span> proyek)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="client_type" value="non_wapu" id="batch-non-wapu"
                                   class="mr-2" onchange="updateBatchClientType('non_wapu')" checked>
                            <label for="batch-non-wapu" class="text-green-600 cursor-pointer">
                                Non-WAPU (<span id="non-wapu-count">0</span> proyek)
                            </label>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Semua proyek dalam batch akan menggunakan tipe klien yang sama
                    </p>
                    @error('client_type')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-blue-700">Total Base:</span>
                        <div class="font-medium text-blue-900 break-words" id="summary-base">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">PPN (<span id="summary-ppn-rate">11</span>%):</span>
                        <div class="font-medium text-blue-900 break-words" id="summary-ppn">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">PPh (<span id="summary-pph-rate">2</span>%):</span>
                        <div class="font-medium text-blue-900 break-words" id="summary-pph">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">Total Billing:</span>
                        <div class="font-medium text-blue-900 break-words" id="summary-billing">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">Diterima:</span>
                        <div class="font-semibold text-blue-900 break-words" id="summary-received">Rp 0</div>
                    </div>
                </div>
                <div class="mt-3 text-xs text-blue-600">
                    <span id="selected-count">0</span> proyek dipilih
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('billing-batches.index') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
                    Batal
                </a>
                <button type="submit" id="submitBtn" class="btn-primary text-sm sm:text-base py-2 px-3 sm:px-4 disabled:opacity-50" disabled>
                    Buat Batch Penagihan
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-4 sm:mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-2 sm:ml-3">
                <h3 class="text-xs sm:text-sm font-medium text-yellow-800">Informasi Batch Penagihan</h3>
                <div class="mt-2 text-xs sm:text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Hanya proyek dengan nilai final yang dapat dimasukkan ke batch penagihan</li>
                        <li>PPh dan PPN akan dihitung secara otomatis berdasarkan nilai final proyek</li>
                        <li>Proyek WAPU tidak dikenakan PPN (dibayar oleh WAPU)</li>
                        <li>Batch yang sudah dibuat dapat dikelola melalui workflow approval yang terstruktur</li>
                        <li>Dokumen pendukung dapat diupload pada setiap tahap approval</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

function getBatchClientType() {
    const wapuRadio = document.getElementById('batch-wapu');
    const nonWapuRadio = document.getElementById('batch-non-wapu');
    
    if (wapuRadio && wapuRadio.checked) {
        return 'wapu';
    } else if (nonWapuRadio && nonWapuRadio.checked) {
        return 'non_wapu';
    }
    return 'non_wapu'; // default
}

function updateBatchClientType(clientType) {
    calculateTotals();
}

function calculateTotals() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const pphRate = parseFloat(document.getElementById('pph_rate').value) || 0;
    const ppnRate = parseFloat(document.getElementById('ppn_rate').value) || 0;
    const batchClientType = getBatchClientType();
    
    let totalDpp = 0; // Dasar Pengenaan Pajak
    let totalPpn = 0;
    let totalPph = 0;
    let totalBilling = 0;
    let selectedCount = 0;
    
    checkboxes.forEach(checkbox => {
        const finalService = parseFloat(checkbox.dataset.finalService) || 0;
        const finalMaterial = parseFloat(checkbox.dataset.finalMaterial) || 0;
        
        const dpp = finalService + finalMaterial;
        totalDpp += dpp;
        
        let ppnAmount = 0;
        let pphAmount = dpp * (pphRate / 100);
        
        if (batchClientType === 'wapu') {
            // WAPU: PPN tidak ditagihkan (dibayar oleh WAPU)
            ppnAmount = 0;
        } else {
            // Non-WAPU: PPN ditagihkan normal
            ppnAmount = dpp * (ppnRate / 100);
        }
        
        totalPpn += ppnAmount;
        totalPph += pphAmount;
        totalBilling += dpp + ppnAmount;
        selectedCount++;
    });
    
    const receivedAmount = totalBilling - totalPph;
    
    // Update display
    document.getElementById('summary-base').textContent = 'Rp ' + formatRupiah(totalDpp);
    document.getElementById('summary-ppn-rate').textContent = ppnRate;
    document.getElementById('summary-ppn').textContent = 'Rp ' + formatRupiah(totalPpn);
    document.getElementById('summary-pph-rate').textContent = pphRate;
    document.getElementById('summary-pph').textContent = 'Rp ' + formatRupiah(totalPph);
    document.getElementById('summary-billing').textContent = 'Rp ' + formatRupiah(totalBilling);
    document.getElementById('summary-received').textContent = 'Rp ' + formatRupiah(receivedAmount);
    document.getElementById('selected-count').textContent = selectedCount;
    
    // Update client type counts
    if (batchClientType === 'wapu') {
        document.getElementById('wapu-count').textContent = selectedCount;
        document.getElementById('non-wapu-count').textContent = 0;
    } else {
        document.getElementById('wapu-count').textContent = 0;
        document.getElementById('non-wapu-count').textContent = selectedCount;
    }
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    if (selectedCount > 0) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('disabled:opacity-50');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled:opacity-50');
    }
}

// Select All functionality
function selectAll() {
    const checkboxes = document.querySelectorAll('.project-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    calculateTotals();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
@endsection
