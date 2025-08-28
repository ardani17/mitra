<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Left Column -->
    <div class="space-y-4">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                Kode Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" name="code" id="code"
                   value="{{ old('code', $cashflowCategory->code ?? '') }}"
                   required
                   {{ isset($cashflowCategory) ? 'readonly' : '' }}
                   placeholder="Contoh: INC_CUSTOM_001"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('code') border-red-500 @enderror {{ isset($cashflowCategory) ? 'bg-gray-100' : '' }}">
            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if(!isset($cashflowCategory))
            <p class="mt-1 text-xs text-gray-500">Kode tidak dapat diubah setelah disimpan</p>
            @endif
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $cashflowCategory->name ?? '') }}"
                   required
                   placeholder="Masukkan nama kategori"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                Tipe <span class="text-red-500">*</span>
            </label>
            <select name="type" id="type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('type') border-red-500 @enderror">
                <option value="">Pilih Tipe</option>
                <option value="income" {{ old('type', $cashflowCategory->type ?? '') === 'income' ? 'selected' : '' }}>
                    Pemasukan
                </option>
                <option value="expense" {{ old('type', $cashflowCategory->type ?? '') === 'expense' ? 'selected' : '' }}>
                    Pengeluaran
                </option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="group" class="block text-sm font-medium text-gray-700 mb-2">
                Group <span class="text-red-500">*</span>
            </label>
            <select name="group" id="group" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('group') border-red-500 @enderror">
                <option value="">Pilih Group</option>
                <optgroup label="Group Pemasukan" id="income-groups">
                    <option value="proyek" {{ old('group', $cashflowCategory->group ?? '') === 'proyek' ? 'selected' : '' }}>
                        Proyek
                    </option>
                    <option value="hutang_modal" {{ old('group', $cashflowCategory->group ?? '') === 'hutang_modal' ? 'selected' : '' }}>
                        Hutang & Modal
                    </option>
                    <option value="piutang_tagihan" {{ old('group', $cashflowCategory->group ?? '') === 'piutang_tagihan' ? 'selected' : '' }}>
                        Piutang & Tagihan
                    </option>
                    <option value="pendapatan_lain" {{ old('group', $cashflowCategory->group ?? '') === 'pendapatan_lain' ? 'selected' : '' }}>
                        Pendapatan Lainnya
                    </option>
                </optgroup>
                <optgroup label="Group Pengeluaran" id="expense-groups">
                    <option value="proyek" {{ old('group', $cashflowCategory->group ?? '') === 'proyek' ? 'selected' : '' }}>
                        Proyek
                    </option>
                    <option value="hutang_pinjaman" {{ old('group', $cashflowCategory->group ?? '') === 'hutang_pinjaman' ? 'selected' : '' }}>
                        Hutang & Pinjaman
                    </option>
                    <option value="operasional" {{ old('group', $cashflowCategory->group ?? '') === 'operasional' ? 'selected' : '' }}>
                        Operasional
                    </option>
                    <option value="aset_investasi" {{ old('group', $cashflowCategory->group ?? '') === 'aset_investasi' ? 'selected' : '' }}>
                        Aset & Investasi
                    </option>
                    <option value="pengeluaran_lain" {{ old('group', $cashflowCategory->group ?? '') === 'pengeluaran_lain' ? 'selected' : '' }}>
                        Pengeluaran Lainnya
                    </option>
                </optgroup>
            </select>
            @error('group')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-4">
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Deskripsi
            </label>
            <textarea name="description" id="description" rows="4"
                      placeholder="Masukkan deskripsi kategori (opsional)"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('description') border-red-500 @enderror">{{ old('description', $cashflowCategory->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                Urutan Tampil
            </label>
            <input type="number" name="sort_order" id="sort_order"
                   value="{{ old('sort_order', $cashflowCategory->sort_order ?? 999) }}"
                   min="0"
                   placeholder="0"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base @error('sort_order') border-red-500 @enderror">
            @error('sort_order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Kategori dengan angka lebih kecil akan ditampilkan lebih dulu</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="is_active" value="1"
                           {{ old('is_active', $cashflowCategory->is_active ?? true) ? 'checked' : '' }}
                           class="form-radio h-4 w-4 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="is_active" value="0"
                           {{ !old('is_active', $cashflowCategory->is_active ?? true) ? 'checked' : '' }}
                           class="form-radio h-4 w-4 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Nonaktif</span>
                </label>
            </div>
            @error('is_active')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Preview Box -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
            <div class="space-y-2">
                <div class="flex items-center">
                    <span class="text-xs text-gray-500 w-20">Kode:</span>
                    <span class="text-sm font-mono" id="preview-code">-</span>
                </div>
                <div class="flex items-center">
                    <span class="text-xs text-gray-500 w-20">Nama:</span>
                    <span class="text-sm" id="preview-name">-</span>
                </div>
                <div class="flex items-center">
                    <span class="text-xs text-gray-500 w-20">Tipe:</span>
                    <span class="text-sm" id="preview-type">-</span>
                </div>
                <div class="flex items-center">
                    <span class="text-xs text-gray-500 w-20">Group:</span>
                    <span class="text-sm" id="preview-group">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const groupSelect = document.getElementById('group');
    const codeInput = document.getElementById('code');
    const nameInput = document.getElementById('name');
    
    // Update preview
    function updatePreview() {
        document.getElementById('preview-code').textContent = codeInput.value || '-';
        document.getElementById('preview-name').textContent = nameInput.value || '-';
        
        const typeOption = typeSelect.options[typeSelect.selectedIndex];
        document.getElementById('preview-type').textContent = typeOption.text || '-';
        
        const groupOption = groupSelect.options[groupSelect.selectedIndex];
        document.getElementById('preview-group').textContent = groupOption.text || '-';
        
        // Update type color
        const previewType = document.getElementById('preview-type');
        if (typeSelect.value === 'income') {
            previewType.className = 'text-sm text-green-600 font-medium';
        } else if (typeSelect.value === 'expense') {
            previewType.className = 'text-sm text-red-600 font-medium';
        } else {
            previewType.className = 'text-sm';
        }
    }
    
    // Filter groups based on type
    function filterGroups() {
        const selectedType = typeSelect.value;
        const incomeGroups = document.getElementById('income-groups');
        const expenseGroups = document.getElementById('expense-groups');
        
        if (selectedType === 'income') {
            incomeGroups.style.display = 'block';
            expenseGroups.style.display = 'none';
        } else if (selectedType === 'expense') {
            incomeGroups.style.display = 'none';
            expenseGroups.style.display = 'block';
        } else {
            incomeGroups.style.display = 'block';
            expenseGroups.style.display = 'block';
        }
    }
    
    // Auto-generate code suggestion
    function suggestCode() {
        if (!codeInput.readOnly && typeSelect.value && nameInput.value) {
            const prefix = typeSelect.value === 'income' ? 'INC_' : 'EXP_';
            const namePart = nameInput.value
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '_')
                .replace(/_+/g, '_')
                .substring(0, 20);
            
            if (!codeInput.value || codeInput.value.startsWith('INC_') || codeInput.value.startsWith('EXP_')) {
                codeInput.value = prefix + namePart;
            }
        }
    }
    
    // Event listeners
    typeSelect.addEventListener('change', function() {
        filterGroups();
        suggestCode();
        updatePreview();
    });
    
    groupSelect.addEventListener('change', updatePreview);
    codeInput.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', function() {
        suggestCode();
        updatePreview();
    });
    
    // Initial setup
    filterGroups();
    updatePreview();
});
</script>