@extends('layouts.app')

@section('title', 'Manajemen Kategori Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manajemen Kategori Cashflow</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Kelola kategori pemasukan dan pengeluaran</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('finance.cashflow-categories.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                <i class="fas fa-plus mr-2"></i>
                <span class="hidden sm:inline">Tambah Kategori</span>
                <span class="sm:hidden">Tambah</span>
            </a>
            <a href="{{ route('finance.cashflow.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Kembali ke Cashflow</span>
                <span class="sm:hidden">Kembali</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(isset($statistics))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                    <i class="fas fa-arrow-down text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kategori Pemasukan</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_income_categories'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                    <i class="fas fa-arrow-up text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kategori Pengeluaran</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_expense_categories'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kategori Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $categories->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-shield-alt text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kategori Sistem</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $categories->where('is_system', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('finance.cashflow-categories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Semua Tipe</option>
                        <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                    <select name="group" id="group" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Semua Group</option>
                        @foreach($groups as $key => $label)
                            <option value="{{ $key }}" {{ request('group') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Cari nama/kode..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="{{ route('finance.cashflow-categories.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if(!$category->is_system)
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="category-checkbox rounded border-gray-300">
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">{{ $category->code }}</span>
                                @if($category->is_system)
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Sistem
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900 font-medium">{{ $category->name }}</div>
                            @if($category->description)
                            <div class="text-xs text-gray-500">{{ Str::limit($category->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->type_badge_class }}">
                                {{ $category->formatted_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fas {{ $category->group_icon }} mr-2 text-gray-400"></i>
                                {{ $category->formatted_group }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $category->cashflow_entries_count ?? 0 }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($category->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Aktif
                            </span>
                            @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('finance.cashflow-categories.show', $category) }}"
                                   class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$category->is_system)
                                <a href="{{ route('finance.cashflow-categories.edit', $category) }}"
                                   class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="toggleCategory({{ $category->id }})"
                                        class="text-{{ $category->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $category->is_active ? 'orange' : 'green' }}-900"
                                        title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas fa-{{ $category->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>
                                @if($category->canBeDeleted())
                                <form action="{{ route('finance.cashflow-categories.destroy', $category) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p>Tidak ada kategori yang ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($categories->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    <!-- Bulk Actions -->
    <div class="mt-4 bg-white rounded-lg shadow-md p-4" id="bulk-actions" style="display: none;">
        <form method="POST" action="{{ route('finance.cashflow-categories.bulk-update') }}" onsubmit="return confirmBulkAction();">
            @csrf
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">
                    <span id="selected-count">0</span> kategori dipilih
                </span>
                <select name="action" id="bulk-action" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                    <option value="">Pilih Aksi</option>
                    <option value="activate">Aktifkan</option>
                    <option value="deactivate">Nonaktifkan</option>
                    <option value="delete">Hapus</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                    Terapkan
                </button>
            </div>
            <div id="selected-categories"></div>
        </form>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.category-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.category-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const selectedCategories = document.getElementById('selected-categories');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = checkedBoxes.length;
        
        // Clear and add selected category IDs
        selectedCategories.innerHTML = '';
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_ids[]';
            input.value = checkbox.value;
            selectedCategories.appendChild(input);
        });
    } else {
        bulkActions.style.display = 'none';
    }
}

function confirmBulkAction() {
    const action = document.getElementById('bulk-action').value;
    if (!action) {
        alert('Silakan pilih aksi yang akan dilakukan.');
        return false;
    }
    
    const actionText = {
        'activate': 'mengaktifkan',
        'deactivate': 'menonaktifkan',
        'delete': 'menghapus'
    };
    
    return confirm(`Apakah Anda yakin ingin ${actionText[action]} kategori yang dipilih?`);
}

function toggleCategory(categoryId) {
    if (!confirm('Apakah Anda yakin ingin mengubah status kategori ini?')) {
        return;
    }
    
    fetch(`{{ url('finance/cashflow-categories') }}/${categoryId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengubah status kategori');
    });
}
</script>
@endsection