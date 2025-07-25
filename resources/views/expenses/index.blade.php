@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Manajemen Pengeluaran</h1>
        <div class="flex space-x-2">
            <a href="{{ route('expenses.export', request()->query()) }}" 
               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('expenses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Ajukan Pengeluaran
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="card p-6 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-500 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengajuan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expenses->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-6 bg-gradient-to-r from-yellow-50 to-yellow-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-500 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Menunggu Approval</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expenses->where('status', 'submitted')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-6 bg-gradient-to-r from-green-50 to-green-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-500 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Disetujui</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expenses->where('status', 'approved')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-6 bg-gradient-to-r from-red-50 to-red-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-500 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ditolak</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expenses->where('status', 'rejected')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Filter & Pencarian</h3>
            <button type="button" id="toggleAdvancedFilter" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <span id="filterToggleText">Tampilkan Filter Lanjutan</span>
                <svg id="filterToggleIcon" class="inline w-4 h-4 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        
        <form method="GET" action="{{ route('expenses.index') }}" id="filterForm">
            <!-- Basic Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-input" placeholder="Cari deskripsi atau nomor..."
                           id="searchInput">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Proyek</label>
                    <select name="project_id" class="form-select" id="projectFilter">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary p-3" title="Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <a href="{{ route('expenses.index') }}" class="btn-secondary p-3" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div id="advancedFilters" class="hidden border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Range Jumlah (Rp)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="amount_min" value="{{ request('amount_min') }}" 
                                   class="form-input" placeholder="Min" min="0">
                            <input type="number" name="amount_max" value="{{ request('amount_max') }}" 
                                   class="form-input" placeholder="Max" min="0">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pengeluaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="form-input">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="form-input">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Urutkan Berdasarkan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort_by" class="form-select">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                                <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>Jumlah</option>
                                <option value="expense_date" {{ request('sort_by') == 'expense_date' ? 'selected' : '' }}>Tanggal Pengeluaran</option>
                            </select>
                            <select name="sort_direction" class="form-select">
                                <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Proyek</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Deskripsi</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-3 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expenses as $index => $expense)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-3 py-4 text-sm font-medium text-gray-900">
                            {{ $expenses->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                <div class="text-sm font-medium text-gray-900 truncate" title="{{ $expense->project->name }}">
                                    {{ Str::limit($expense->project->name, 25) }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $expense->project->code }}</div>
                                <div class="text-xs text-blue-600">
                                    {{ Str::limit($expense->user->name, 15) }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                <div class="text-sm text-gray-900 truncate" title="{{ $expense->description }}">
                                    {{ Str::limit($expense->description, 30) }}
                                </div>
                                @if($expense->category)
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="px-1 py-0.5 bg-gray-100 rounded text-xs">{{ Str::limit($expense->category, 10) }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4 text-sm">
                            <div class="font-medium text-gray-900">
                                {{ \App\Helpers\FormatHelper::formatRupiah($expense->amount) }}
                            </div>
                            @if($expense->receipt_number)
                            <div class="text-xs text-gray-500">{{ Str::limit($expense->receipt_number, 8) }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-xs text-gray-500">
                            <div>{{ $expense->expense_date->format('d/m/Y') }}</div>
                            <div>{{ $expense->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                      @if($expense->status == 'approved') bg-green-100 text-green-800
                                      @elseif($expense->status == 'rejected') bg-red-100 text-red-800
                                      @elseif($expense->status == 'submitted') bg-yellow-100 text-yellow-800
                                      @else bg-gray-100 text-gray-800 @endif">
                                    @if($expense->status == 'draft') Draft
                                    @elseif($expense->status == 'submitted') Diajukan
                                    @elseif($expense->status == 'approved') Disetujui
                                    @elseif($expense->status == 'rejected') Ditolak
                                    @else {{ ucfirst($expense->status) }}
                                    @endif
                                </span>
                                
                                <!-- Simplified Approval Status -->
                                @if($expense->status != 'draft')
                                <div class="text-xs text-gray-500">
                                    @php
                                        $approvals = $expense->approvals()->with('approver')->get();
                                        $financeApproval = $approvals->where('level', 'finance_manager')->first();
                                        $finalApproval = $approvals->whereIn('level', ['director', 'project_manager'])->first();
                                    @endphp
                                    
                                    <div class="flex space-x-1">
                                        <!-- Finance Status -->
                                        @if($financeApproval && $financeApproval->status == 'approved')
                                            <span class="text-green-500">✓F</span>
                                        @elseif($financeApproval && $financeApproval->status == 'rejected')
                                            <span class="text-red-500">✗F</span>
                                        @else
                                            <span class="text-gray-400">○F</span>
                                        @endif
                                        
                                        <!-- Final Status -->
                                        @if($finalApproval && $finalApproval->status == 'approved')
                                            <span class="text-green-500">✓M</span>
                                        @elseif($finalApproval && $finalApproval->status == 'rejected')
                                            <span class="text-red-500">✗M</span>
                                        @else
                                            <span class="text-gray-400">○M</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4 text-xs">
                            <div class="flex flex-col space-y-1">
                                <a href="{{ route('expenses.show', $expense) }}" class="text-blue-600 hover:text-blue-900 font-medium">Lihat</a>
                                
                                @if($expense->status == 'draft' && $expense->user_id == auth()->id())
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                @endif
                                
                                @if($expense->status == 'submitted' && auth()->user()->hasRole(['finance_manager', 'direktur', 'project_manager']))
                                <button onclick="showApprovalModal({{ $expense->id }}, '{{ $expense->description }}')" 
                                        class="text-green-600 hover:text-green-900 text-left">
                                    Approve
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada pengeluaran ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-t border-blue-200 sm:px-6">
            <div class="pagination-wrapper">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Approval Pengeluaran</h3>
                <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600">Deskripsi:</p>
                <p id="expenseDescription" class="font-medium"></p>
            </div>
            
            <form id="approvalForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keputusan</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="status" value="approved" class="mr-2" required>
                            <span class="text-green-600">Setujui</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="status" value="rejected" class="mr-2" required>
                            <span class="text-red-600">Tolak</span>
                        </label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" class="form-input" placeholder="Berikan catatan untuk keputusan Anda..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeApprovalModal()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Kirim Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Advanced Filter Toggle
    const toggleButton = document.getElementById('toggleAdvancedFilter');
    const advancedFilters = document.getElementById('advancedFilters');
    const toggleText = document.getElementById('filterToggleText');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    toggleButton.addEventListener('click', function() {
        if (advancedFilters.classList.contains('hidden')) {
            advancedFilters.classList.remove('hidden');
            toggleText.textContent = 'Sembunyikan Filter Lanjutan';
            toggleIcon.style.transform = 'rotate(180deg)';
        } else {
            advancedFilters.classList.add('hidden');
            toggleText.textContent = 'Tampilkan Filter Lanjutan';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    });
    
    // Real-time Search with Debounce
    const searchInput = document.getElementById('searchInput');
    const projectFilter = document.getElementById('projectFilter');
    const statusFilter = document.getElementById('statusFilter');
    let searchTimeout;
    
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    }
    
    // Auto-submit on filter changes
    searchInput.addEventListener('input', performSearch);
    projectFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    statusFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    // Filter lanjutan selalu tersembunyi secara default
    // Tidak ada auto-expand logic - user harus manual klik untuk membuka
});

function showApprovalModal(expenseId, description) {
    document.getElementById('expenseDescription').textContent = description;
    document.getElementById('approvalForm').action = `/expenses/${expenseId}/approve`;
    document.getElementById('approvalModal').classList.remove('hidden');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
}
</script>
@endsection
