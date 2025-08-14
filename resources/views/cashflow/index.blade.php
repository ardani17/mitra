@extends('layouts.app')

@section('title', 'Jurnal Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Simple Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Jurnal Cashflow</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola dan pantau arus kas perusahaan</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('finance.cashflow.export') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
            </a>
            <a href="{{ route('finance.cashflow.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Transaksi
            </a>
        </div>
    </div>

    <!-- Simple Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Pemasukan</p>
                    <p class="text-lg sm:text-2xl font-semibold text-green-600">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Pengeluaran</p>
                    <p class="text-lg sm:text-2xl font-semibold text-red-600">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 {{ $summary['balance'] >= 0 ? 'bg-blue-100' : 'bg-orange-100' }} rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 {{ $summary['balance'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Saldo Bersih</p>
                    <p class="text-lg sm:text-2xl font-semibold {{ $summary['balance'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                        Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Transaksi</h3>
        <form method="GET" action="{{ route('finance.cashflow.index') }}">
            <!-- Quick Filter Buttons -->
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('finance.cashflow.income') }}"
                   class="px-3 py-1 rounded text-sm {{ request()->routeIs('finance.cashflow.income') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Pemasukan
                </a>
                <a href="{{ route('finance.cashflow.expense') }}"
                   class="px-3 py-1 rounded text-sm {{ request()->routeIs('finance.cashflow.expense') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Pengeluaran
                </a>
                <a href="{{ route('finance.cashflow.index') }}"
                   class="px-3 py-1 rounded text-sm {{ request()->routeIs('finance.cashflow.index') && !request()->has('type') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Semua
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
                <div>
                    <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Pencarian</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Cari deskripsi..."
                           class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tipe</label>
                    <select name="type" id="type" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Tipe</option>
                        <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label for="category_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Kategori</label>
                    <select name="category_id" id="category_id" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Proyek</label>
                    <select name="project_id" id="project_id" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:flex sm:items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        Filter
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-4">
                <div>
                    <label for="start_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="end_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="sm:flex sm:items-end">
                    <a href="{{ route('finance.cashflow.index') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm text-center block">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Simple Transactions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Daftar Transaksi</h3>
                <div class="text-sm text-gray-600">
                    Total: {{ $entries->total() }} transaksi
                </div>
            </div>
        </div>

        @if($entries->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($entries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="entries[]" value="{{ $entry->id }}" class="entry-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->transaction_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $entry->description }}</div>
                                    @if($entry->notes)
                                        <div class="text-sm text-gray-500">{{ Str::limit($entry->notes, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->category->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->project?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entry->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $entry->formatted_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $entry->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $entry->type === 'income' ? '+' : '-' }} {{ $entry->formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entry->status_badge_class }}">
                                        {{ $entry->formatted_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('finance.cashflow.show', $entry) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                        @if($entry->canBeEdited())
                                            <a href="{{ route('finance.cashflow.edit', $entry) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @endif
                                        @if($entry->canBeDeleted())
                                            <form method="POST" action="{{ route('finance.cashflow.destroy', $entry) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden space-y-3">
                @foreach($entries as $entry)
                    <div class="transaction-card">
                        <div class="transaction-card-header">
                            <div class="transaction-card-icon {{ $entry->type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($entry->type === 'income')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="transaction-card-info">
                                <div class="transaction-card-title">{{ Str::limit($entry->description, 40) }}</div>
                                <div class="transaction-card-subtitle">{{ $entry->transaction_date->format('d M Y') }} • {{ $entry->category->name }}</div>
                            </div>
                            <div class="transaction-card-amount">
                                <div class="transaction-card-amount-value {{ $entry->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $entry->type === 'income' ? '+' : '-' }} {{ $entry->formatted_amount }}
                                </div>
                                <div class="transaction-card-amount-label">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $entry->status_badge_class }}">
                                        {{ $entry->formatted_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="transaction-card-details">
                            <div class="transaction-card-detail">
                                <div class="transaction-card-detail-label">Tipe</div>
                                <div class="transaction-card-detail-value">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $entry->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $entry->formatted_type }}
                                    </span>
                                </div>
                            </div>
                            <div class="transaction-card-detail">
                                <div class="transaction-card-detail-label">Proyek</div>
                                <div class="transaction-card-detail-value">{{ $entry->project?->name ?? '-' }}</div>
                            </div>
                        </div>
                        
                        @if($entry->notes)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="text-xs text-gray-500">Catatan:</div>
                                <div class="text-sm text-gray-700 mt-1">{{ Str::limit($entry->notes, 100) }}</div>
                            </div>
                        @endif
                        
                        <div class="mobile-table-card-actions">
                            <input type="checkbox" name="entries[]" value="{{ $entry->id }}" class="entry-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-3">
                            <a href="{{ route('finance.cashflow.show', $entry) }}" class="action-btn-mobile primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($entry->canBeEdited())
                                <a href="{{ route('finance.cashflow.edit', $entry) }}" class="action-btn-mobile warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if($entry->canBeDeleted())
                                <form method="POST" action="{{ route('finance.cashflow.destroy', $entry) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn-mobile danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bulk Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" id="bulk-actions" style="display: none;">
                <form method="POST" action="{{ route('finance.cashflow.bulk-action') }}" class="flex items-center space-x-4">
                    @csrf
                    <input type="hidden" name="entries" id="selected-entries">
                    <span class="text-sm text-gray-600">Aksi untuk item terpilih:</span>
                    <select name="action" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                        <option value="">Pilih Aksi</option>
                        <option value="confirm">Konfirmasi</option>
                        <option value="cancel">Batalkan</option>
                        <option value="delete">Hapus</option>
                    </select>
                    <button type="submit" class="px-4 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md">
                        Jalankan
                    </button>
                </form>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $entries->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada transaksi</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan transaksi cashflow pertama.</p>
                <div class="mt-6">
                    <a href="{{ route('finance.cashflow.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Transaksi
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Bulk actions functionality
    const selectAll = document.getElementById('select-all');
    const entryCheckboxes = document.querySelectorAll('.entry-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedEntries = document.getElementById('selected-entries');

    if (selectAll && entryCheckboxes.length > 0) {
        // Select all functionality
        selectAll.addEventListener('change', function() {
            entryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox functionality
        entryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.entry-checkbox:checked');
            const checkedIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (checkedIds.length > 0 && bulkActions) {
                bulkActions.style.display = 'block';
                if (selectedEntries) {
                    selectedEntries.value = JSON.stringify(checkedIds);
                }
            } else if (bulkActions) {
                bulkActions.style.display = 'none';
            }

            // Update select all checkbox
            if (selectAll) {
                selectAll.checked = checkedIds.length === entryCheckboxes.length;
                selectAll.indeterminate = checkedIds.length > 0 && checkedIds.length < entryCheckboxes.length;
            }
        }
    }

    // Auto-submit form when filter changes (desktop only)
    const filterInputs = document.querySelectorAll('#type, #status, #category_id, #project_id');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Only auto-submit on desktop
            if (window.innerWidth >= 640) {
                this.form.submit();
            }
        });
    });

    // Search with debounce
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Only auto-submit on desktop
                if (window.innerWidth >= 640) {
                    this.form.submit();
                }
            }, 500);
        });
    }

    // Add loading states for better UX
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Memuat...
                `;
                submitBtn.disabled = true;
            }
        });
    });
});
</script>

@endsection