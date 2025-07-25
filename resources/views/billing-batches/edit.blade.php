@extends('layouts.app')

@section('content')
<div class="container mx-auto px-8 lg:px-16 xl:px-24 py-8 max-w-6xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Batch Penagihan</h1>
        <div class="flex space-x-2">
            <a href="{{ route('billing-batches.show', $billingBatch) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('billing-batches.update', $billingBatch) }}" method="POST" id="batchForm">
            @csrf
            @method('PUT')
            
            <!-- Batch Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Batch</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Batch</label>
                        <input type="text" value="{{ $billingBatch->batch_code }}" disabled
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500">
                        <p class="text-xs text-gray-500 mt-1">Kode batch tidak dapat diubah</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Penagihan *</label>
                        <input type="date" name="billing_date" id="billing_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               value="{{ old('billing_date', $billingBatch->billing_date->format('Y-m-d')) }}">
                        @error('billing_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($billingBatch->status_color == 'green') bg-green-100 text-green-800
                            @elseif($billingBatch->status_color == 'blue') bg-blue-100 text-blue-800
                            @elseif($billingBatch->status_color == 'yellow') bg-yellow-100 text-yellow-800
                            @elseif($billingBatch->status_color == 'red') bg-red-100 text-red-800
                            @elseif($billingBatch->status_color == 'purple') bg-purple-100 text-purple-800
                            @elseif($billingBatch->status_color == 'indigo') bg-indigo-100 text-indigo-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $billingBatch->status_label }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor SP *</label>
                        <input type="text" name="sp_number" id="sp_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               value="{{ old('sp_number', $billingBatch->sp_number) }}"
                               placeholder="Contoh: SP/001/2025">
                        @error('sp_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Faktur Pajak *</label>
                        <input type="text" name="invoice_number" id="invoice_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               value="{{ old('invoice_number', $billingBatch->invoice_number) }}"
                               placeholder="Contoh: 010.000-25.00000001">
                        @error('invoice_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rate PPh (%) *</label>
                        <input type="number" name="pph_rate" id="pph_rate" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               value="{{ old('pph_rate', $billingBatch->pph_rate) }}" onchange="calculateTotals()" oninput="calculateTotals()"
                               placeholder="Contoh: 1.75">
                        @error('pph_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Masukkan rate PPh dalam persen (contoh: 1.75 untuk 1.75%)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rate PPN (%) *</label>
                        <input type="number" name="ppn_rate" id="ppn_rate" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               value="{{ old('ppn_rate', $billingBatch->ppn_rate) }}" onchange="calculateTotals()" oninput="calculateTotals()"
                               placeholder="Contoh: 11.50">
                        @error('ppn_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Masukkan rate PPN dalam persen (contoh: 11.50 untuk 11.50%)</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Catatan untuk batch penagihan ini...">{{ old('notes', $billingBatch->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Project Billings -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Penagihan Proyek Saat Ini</h3>
                <div class="space-y-4">
                    @foreach($billingBatch->projectBillings as $billing)
                        <div class="border border-gray-200 rounded-lg p-4 bg-blue-50">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" name="project_billings[]" value="{{ $billing->id }}" 
                                       class="mt-1 billing-checkbox" checked onchange="calculateTotals()"
                                       data-amount="{{ $billing->total_amount }}">
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $billing->project->code }} - {{ $billing->project->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $billing->project->client_name }}</p>
                                            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-gray-600">
                                                <div>
                                                    <span class="font-medium">Nilai Jasa:</span><br>
                                                    Rp {{ number_format($billing->nilai_jasa, 0, ',', '.') }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">Nilai Material:</span><br>
                                                    Rp {{ number_format($billing->nilai_material, 0, ',', '.') }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">PPN:</span><br>
                                                    Rp {{ number_format($billing->ppn_amount, 0, ',', '.') }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">Total:</span><br>
                                                    <span class="font-semibold text-blue-600">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Dalam Batch
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $billing->billing_date->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Available Project Billings -->
            @if($availableProjects->count() > 0)
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Penagihan Proyek Tersedia untuk Ditambahkan</h3>
                <div class="space-y-4">
                    @foreach($availableProjects as $project)
                        @foreach($project->billings as $billing)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="project_billings[]" value="{{ $billing->id }}" 
                                           class="mt-1 billing-checkbox" onchange="calculateTotals()"
                                           data-amount="{{ $billing->total_amount }}">
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">{{ $project->code }} - {{ $project->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $project->client_name }}</p>
                                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-gray-600">
                                                    <div>
                                                        <span class="font-medium">Nilai Jasa:</span><br>
                                                        Rp {{ number_format($billing->nilai_jasa, 0, ',', '.') }}
                                                    </div>
                                                    <div>
                                                        <span class="font-medium">Nilai Material:</span><br>
                                                        Rp {{ number_format($billing->nilai_material, 0, ',', '.') }}
                                                    </div>
                                                    <div>
                                                        <span class="font-medium">PPN:</span><br>
                                                        Rp {{ number_format($billing->ppn_amount, 0, ',', '.') }}
                                                    </div>
                                                    <div>
                                                        <span class="font-medium">Total:</span><br>
                                                        <span class="font-semibold text-blue-600">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($billing->status == 'draft') bg-gray-100 text-gray-800
                                                    @elseif($billing->status == 'sent') bg-blue-100 text-blue-800
                                                    @elseif($billing->status == 'paid') bg-green-100 text-green-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($billing->status) }}
                                                </span>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $billing->billing_date->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                
                @error('project_billings')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <!-- Calculation Summary -->
            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-3">Ringkasan Perhitungan Batch</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Total Base:</span>
                        <div class="font-medium text-blue-900" id="summary-base">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">PPN (<span id="summary-ppn-rate">{{ $billingBatch->ppn_rate }}</span>%):</span>
                        <div class="font-medium text-blue-900" id="summary-ppn">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">PPh (<span id="summary-pph-rate">{{ $billingBatch->pph_rate }}</span>%):</span>
                        <div class="font-medium text-blue-900" id="summary-pph">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">Total Billing:</span>
                        <div class="font-medium text-blue-900" id="summary-billing">Rp 0</div>
                    </div>
                    <div>
                        <span class="text-blue-700">Diterima:</span>
                        <div class="font-semibold text-blue-900" id="summary-received">Rp 0</div>
                    </div>
                </div>
                <div class="mt-3 text-xs text-blue-600">
                    <span id="selected-count">0</span> penagihan dipilih
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="{{ route('billing-batches.show', $billingBatch) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                    Update Batch Penagihan
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Informasi Edit Batch</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Anda dapat menambah atau mengurangi penagihan proyek dalam batch ini</li>
                        <li>Perubahan rate PPh dan PPN akan mempengaruhi perhitungan total</li>
                        <li>Batch hanya dapat diedit selama masih berstatus "Draft"</li>
                        <li>Minimal harus ada satu penagihan proyek dalam batch</li>
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

function calculateTotals() {
    const checkboxes = document.querySelectorAll('.billing-checkbox:checked');
    const pphRate = parseFloat(document.getElementById('pph_rate').value);
    const ppnRate = parseFloat(document.getElementById('ppn_rate').value);
    
    let totalBase = 0;
    let selectedCount = 0;
    
    checkboxes.forEach(checkbox => {
        totalBase += parseFloat(checkbox.dataset.amount);
        selectedCount++;
    });
    
    const ppnAmount = totalBase * (ppnRate / 100);
    const pphAmount = totalBase * (pphRate / 100);
    const totalBilling = totalBase + ppnAmount;
    const receivedAmount = totalBilling - pphAmount;
    
    // Update display
    document.getElementById('summary-base').textContent = 'Rp ' + formatRupiah(totalBase);
    document.getElementById('summary-ppn-rate').textContent = ppnRate;
    document.getElementById('summary-ppn').textContent = 'Rp ' + formatRupiah(ppnAmount);
    document.getElementById('summary-pph-rate').textContent = pphRate;
    document.getElementById('summary-pph').textContent = 'Rp ' + formatRupiah(pphAmount);
    document.getElementById('summary-billing').textContent = 'Rp ' + formatRupiah(totalBilling);
    document.getElementById('summary-received').textContent = 'Rp ' + formatRupiah(receivedAmount);
    document.getElementById('selected-count').textContent = selectedCount;
    
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

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
@endsection
