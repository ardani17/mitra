@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Proyek</h1>
        <a href="{{ route('projects.show', $project) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Kembali ke Proyek
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Proyek *</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Proyek</label>
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 font-mono">
                        {{ $project->code }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Kode proyek tidak dapat diubah</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Proyek *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Tipe</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $project->type) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Jasa Plan (Rp)</label>
                    <input type="text" name="planned_service_value_display" id="planned_service_value_display" 
                           value="{{ old('planned_service_value', $project->planned_service_value) ? number_format(old('planned_service_value', $project->planned_service_value), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    <input type="hidden" name="planned_service_value" id="planned_service_value" value="{{ old('planned_service_value', $project->planned_service_value) }}">
                    @error('planned_service_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Material Plan (Rp)</label>
                    <input type="text" name="planned_material_value_display" id="planned_material_value_display" 
                           value="{{ old('planned_material_value', $project->planned_material_value) ? number_format(old('planned_material_value', $project->planned_material_value), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    <input type="hidden" name="planned_material_value" id="planned_material_value" value="{{ old('planned_material_value', $project->planned_material_value) }}">
                    @error('planned_material_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Nilai Plan (Rp)</label>
                    <input type="text" name="planned_total_value_display" id="planned_total_value_display" 
                           value="{{ old('planned_total_value', $project->planned_total_value) ? number_format(old('planned_total_value', $project->planned_total_value), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                           placeholder="0" readonly>
                    <input type="hidden" name="planned_total_value" id="planned_total_value" value="{{ old('planned_total_value', $project->planned_total_value) }}">
                    <p class="mt-1 text-xs text-gray-500">Otomatis dihitung dari nilai jasa + material</p>
                    @error('planned_total_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $project->status) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prioritas *</label>
                    <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Prioritas</option>
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}" {{ old('priority', $project->priority) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="duration_info" class="mt-1 text-xs text-gray-500"></div>
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $project->location) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Nilai Akhir Proyek Section -->
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Nilai Akhir Proyek</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Jasa Akhir (Rp)</label>
                        <input type="text" name="final_service_value_display" id="final_service_value_display" 
                               value="{{ old('final_service_value', $project->final_service_value) ? number_format(old('final_service_value', $project->final_service_value), 0, ',', '.') : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                        <input type="hidden" name="final_service_value" id="final_service_value" value="{{ old('final_service_value', $project->final_service_value) }}">
                        @error('final_service_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Material Akhir (Rp)</label>
                        <input type="text" name="final_material_value_display" id="final_material_value_display" 
                               value="{{ old('final_material_value', $project->final_material_value) ? number_format(old('final_material_value', $project->final_material_value), 0, ',', '.') : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                        <input type="hidden" name="final_material_value" id="final_material_value" value="{{ old('final_material_value', $project->final_material_value) }}">
                        @error('final_material_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Nilai Akhir (Rp)</label>
                        <input type="text" name="final_total_value_display" id="final_total_value_display" 
                               value="{{ old('final_total_value', $project->final_total_value) ? number_format(old('final_total_value', $project->final_total_value), 0, ',', '.') : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                               placeholder="0" readonly>
                        <input type="hidden" name="final_total_value" id="final_total_value" value="{{ old('final_total_value', $project->final_total_value) }}">
                        <p class="mt-1 text-xs text-gray-500">Otomatis dihitung dari nilai jasa + material akhir</p>
                        @error('final_total_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $project->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-8 flex justify-between">
                <!-- Delete Button (Only for Direktur) -->
                @can('delete', $project)
                <div>
                    <button type="button" onclick="showDeleteConfirmation()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus Proyek
                    </button>
                </div>
                @else
                <div></div>
                @endcan
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <a href="{{ route('projects.show', $project) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Proyek
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format number with dots as thousand separators
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Remove dots and convert to number
    function unformatNumber(str) {
        return str.replace(/\./g, '');
    }
    
    // Setup number formatting for all value fields
    const valueFields = [
        'planned_service_value',
        'planned_material_value',
        'planned_total_value',
        'final_service_value',
        'final_material_value',
        'final_total_value'
    ];
    
    valueFields.forEach(fieldName => {
        const displayInput = document.getElementById(fieldName + '_display');
        const hiddenInput = document.getElementById(fieldName);
        
        if (displayInput && hiddenInput) {
            // Handle input event
            displayInput.addEventListener('input', function(e) {
                let value = e.target.value;
                
                // Remove all non-digit characters
                value = value.replace(/[^\d]/g, '');
                
                // Update hidden input with raw number
                hiddenInput.value = value;
                
                // Format and display with dots
                if (value) {
                    e.target.value = formatNumber(value);
                } else {
                    e.target.value = '';
                }
                
                // Calculate totals
                if (fieldName === 'planned_service_value' || fieldName === 'planned_material_value') {
                    calculatePlannedTotal();
                }
                if (fieldName === 'final_service_value' || fieldName === 'final_material_value') {
                    calculateFinalTotal();
                }
            });
            
            // Handle paste event
            displayInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    let value = e.target.value.replace(/[^\d]/g, '');
                    hiddenInput.value = value;
                    if (value) {
                        e.target.value = formatNumber(value);
                    }
                    
                    // Calculate totals
                    if (fieldName === 'planned_service_value' || fieldName === 'planned_material_value') {
                        calculatePlannedTotal();
                    }
                    if (fieldName === 'final_service_value' || fieldName === 'final_material_value') {
                        calculateFinalTotal();
                    }
                }, 10);
            });
        }
    });
    
    // Calculate planned total value
    function calculatePlannedTotal() {
        const serviceValue = parseInt(document.getElementById('planned_service_value').value) || 0;
        const materialValue = parseInt(document.getElementById('planned_material_value').value) || 0;
        const totalValue = serviceValue + materialValue;
        
        // Update total fields
        document.getElementById('planned_total_value').value = totalValue;
        document.getElementById('planned_total_value_display').value = totalValue > 0 ? formatNumber(totalValue.toString()) : '';
    }
    
    // Calculate final total value
    function calculateFinalTotal() {
        const serviceValue = parseInt(document.getElementById('final_service_value').value) || 0;
        const materialValue = parseInt(document.getElementById('final_material_value').value) || 0;
        const totalValue = serviceValue + materialValue;
        
        // Update total fields
        document.getElementById('final_total_value').value = totalValue;
        document.getElementById('final_total_value_display').value = totalValue > 0 ? formatNumber(totalValue.toString()) : '';
    }
    
    // Calculate duration between start and end date
    function calculateDuration() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const durationInfo = document.getElementById('duration_info');
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                durationInfo.textContent = `Durasi pengerjaan: ${diffDays} hari`;
                durationInfo.className = 'mt-1 text-xs text-green-600';
            } else {
                durationInfo.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                durationInfo.className = 'mt-1 text-xs text-red-600';
            }
        } else {
            durationInfo.textContent = '';
        }
    }
    
    // Add event listeners for date fields
    document.getElementById('start_date').addEventListener('change', calculateDuration);
    document.getElementById('end_date').addEventListener('change', calculateDuration);
    
    // Calculate duration on page load if both dates exist
    calculateDuration();
});

// Delete confirmation modal
function showDeleteConfirmation() {
    // Get project data for confirmation
    const projectName = "{{ $project->name }}";
    const projectCode = "{{ $project->code }}";
    const expensesCount = {{ $project->expenses()->count() }};
    const timelinesCount = {{ $project->timelines()->count() }};
    const billingsCount = {{ $project->billings()->count() }};
    const revenuesCount = {{ $project->revenues()->count() }};
    const documentsCount = {{ $project->documents()->count() }};
    const activitiesCount = {{ $project->activities()->count() }};
    
    const totalRelatedData = expensesCount + timelinesCount + billingsCount + revenuesCount + documentsCount + activitiesCount;
    
    let relatedDataText = '';
    if (totalRelatedData > 0) {
        relatedDataText = `\n\nData terkait yang akan ikut terhapus:`;
        if (expensesCount > 0) relatedDataText += `\n• ${expensesCount} data pengeluaran`;
        if (timelinesCount > 0) relatedDataText += `\n• ${timelinesCount} data timeline`;
        if (billingsCount > 0) relatedDataText += `\n• ${billingsCount} data billing`;
        if (revenuesCount > 0) relatedDataText += `\n• ${revenuesCount} data revenue`;
        if (documentsCount > 0) relatedDataText += `\n• ${documentsCount} dokumen`;
        if (activitiesCount > 0) relatedDataText += `\n• ${activitiesCount} log aktivitas`;
        relatedDataText += `\n\nSemua data ini akan PERMANEN terhapus dan tidak dapat dikembalikan!`;
    }
    
    const confirmMessage = `PERINGATAN: Anda akan menghapus proyek "${projectName}" (${projectCode}).${relatedDataText}\n\nApakah Anda yakin ingin melanjutkan?`;
    
    if (confirm(confirmMessage)) {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('projects.destroy', $project) }}";
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = "{{ csrf_token() }}";
        form.appendChild(csrfToken);
        
        // Add DELETE method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Custom Styles for Better Modal -->
<style>
.delete-confirmation {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.delete-modal {
    background: white;
    border-radius: 8px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.delete-modal h3 {
    color: #dc2626;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 16px;
}

.delete-modal p {
    color: #374151;
    margin-bottom: 16px;
    line-height: 1.5;
}

.delete-modal .related-data {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    padding: 12px;
    margin: 16px 0;
}

.delete-modal .related-data h4 {
    color: #dc2626;
    font-weight: 600;
    margin-bottom: 8px;
}

.delete-modal .related-data ul {
    list-style-type: disc;
    margin-left: 20px;
    color: #6b7280;
}

.delete-modal .buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn-cancel {
    background-color: #6b7280;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel:hover {
    background-color: #4b5563;
}

.btn-delete {
    background-color: #dc2626;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-delete:hover {
    background-color: #b91c1c;
}
</style>
@endsection
