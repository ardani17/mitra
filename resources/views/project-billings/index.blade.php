@extends('layouts.app')

@section('title', 'Daftar Penagihan Proyek')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mobile-header sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="mobile-header-title sm:text-3xl">Daftar Penagihan Proyek</h1>
            <p class="mobile-header-subtitle sm:text-base">Kelola semua penagihan proyek dengan sistem pembayaran penuh atau termin</p>
        </div>
        <div class="mobile-header-actions sm:flex-row sm:space-y-0 sm:space-x-3 sm:w-auto">
            <a href="{{ route('project-billings.create') }}"
               class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Penagihan
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="mobile-stats-grid sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-6 mb-6">
        <div class="mobile-stat-card">
            <div class="flex items-center">
                <div class="stat-icon bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Penagihan</p>
                    <p class="stat-value sm:text-2xl">{{ $stats['total_billings'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="mobile-stat-card">
            <div class="flex items-center">
                <div class="stat-icon bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Nilai</p>
                    <p class="stat-value sm:text-2xl">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="mobile-stat-card">
            <div class="flex items-center">
                <div class="stat-icon bg-emerald-100">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Sudah Lunas</p>
                    <p class="stat-value sm:text-2xl">Rp {{ number_format($stats['paid_amount'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="mobile-stat-card">
            <div class="flex items-center">
                <div class="stat-icon bg-orange-100">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Belum Lunas</p>
                    <p class="stat-value sm:text-2xl">Rp {{ number_format(($stats['total_amount'] ?? 0) - ($stats['paid_amount'] ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mobile-filter-container">
        <div class="mobile-filter-header">
            <h3 class="mobile-filter-title">Filter Penagihan</h3>
            <button type="button" class="mobile-filter-toggle sm:hidden" onclick="toggleMobileFilter()">
                <span id="filter-toggle-text">Tampilkan Filter</span>
            </button>
        </div>
        
        <div id="mobile-filter-content" class="mobile-filter-content hidden sm:block">
            <form method="GET" action="{{ route('project-billings.index') }}">
                <div class="mobile-filter-row sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">
                    <div class="mobile-filter-group">
                        <label for="payment_type" class="mobile-filter-label">Tipe Pembayaran</label>
                        <select name="payment_type" id="payment_type" class="mobile-filter-select">
                            <option value="">Semua Tipe</option>
                            <option value="full" {{ request('payment_type') == 'full' ? 'selected' : '' }}>Pembayaran Penuh</option>
                            <option value="termin" {{ request('payment_type') == 'termin' ? 'selected' : '' }}>Pembayaran Termin</option>
                        </select>
                    </div>

                    <div class="mobile-filter-group">
                        <label for="status" class="mobile-filter-label">Status</label>
                        <select name="status" id="status" class="mobile-filter-select">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>

                    <div class="mobile-filter-group">
                        <label for="project_id" class="mobile-filter-label">Proyek</label>
                        <select name="project_id" id="project_id" class="mobile-filter-select">
                            <option value="">Semua Proyek</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }} - {{ $project->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mobile-filter-group">
                        <label for="search" class="mobile-filter-label">Pencarian</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Cari invoice atau proyek..."
                               class="mobile-filter-input">
                    </div>
                </div>

                <div class="mobile-filter-actions sm:flex sm:justify-between sm:items-center sm:flex-row sm:space-y-0 sm:pt-4">
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                            Filter
                        </button>
                        <a href="{{ route('project-billings.index') }}" class="mobile-btn-secondary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                            Reset
                        </a>
                    </div>
                    
                    <div class="text-sm text-slate-600 text-center sm:text-right mt-3 sm:mt-0">
                        Menampilkan {{ $billings->firstItem() ?? 0 }} - {{ $billings->lastItem() ?? 0 }} dari {{ $billings->total() }} penagihan
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Billings Table/Cards -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        @if($billings->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Proyek</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($billings as $billing)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $billing->invoice_number }}</div>
                                    <div class="text-sm text-slate-500">{{ $billing->billing_date ? \Carbon\Carbon::parse($billing->billing_date)->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $billing->project->name ?? '-' }}</div>
                                    <div class="text-sm text-slate-500">{{ $billing->project->code ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $billing->payment_type == 'full' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $billing->payment_type == 'full' ? 'Penuh' : 'Termin' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    <div>{{ $billing->billing_date ? \Carbon\Carbon::parse($billing->billing_date)->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                    Rp {{ number_format($billing->total_amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'sent' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'overdue' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'sent' => 'Terkirim',
                                            'paid' => 'Lunas',
                                            'overdue' => 'Terlambat'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$billing->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$billing->status] ?? ucfirst($billing->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('project-billings.show', $billing) }}"
                                           class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($billing->status !== 'paid')
                                            <a href="{{ route('project-billings.edit', $billing) }}"
                                               class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($billing->status !== 'paid')
                                            <form action="{{ route('project-billings.destroy', $billing) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus penagihan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
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
            <div class="sm:hidden mobile-table-container p-4">
                @foreach($billings as $billing)
                    <div class="mobile-table-card mobile-fade-in">
                        <div class="mobile-table-card-header">
                            <div class="mobile-table-card-main">
                                <div class="mobile-table-card-title">{{ $billing->invoice_number }}</div>
                                <div class="mobile-table-card-subtitle">
                                    {{ $billing->project->name ?? '-' }} • {{ $billing->billing_date ? \Carbon\Carbon::parse($billing->billing_date)->format('d M Y') : '-' }}
                                </div>
                            </div>
                            <div class="mobile-table-card-meta">
                                <div class="mobile-table-card-amount text-slate-900">
                                    Rp {{ number_format($billing->total_amount ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="mobile-table-card-status">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'mobile-badge-secondary',
                                            'sent' => 'mobile-badge-warning',
                                            'paid' => 'mobile-badge-success',
                                            'overdue' => 'mobile-badge-danger'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'sent' => 'Terkirim',
                                            'paid' => 'Lunas',
                                            'overdue' => 'Terlambat'
                                        ];
                                    @endphp
                                    <span class="{{ $statusClasses[$billing->status] ?? 'mobile-badge-secondary' }}">
                                        {{ $statusLabels[$billing->status] ?? ucfirst($billing->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mobile-table-card-body">
                            <div class="mobile-table-card-row">
                                <span class="mobile-table-card-label">Proyek</span>
                                <span class="mobile-table-card-value">{{ $billing->project->code ?? '-' }}</span>
                            </div>
                            <div class="mobile-table-card-row">
                                <span class="mobile-table-card-label">Tipe Pembayaran</span>
                                <span class="mobile-table-card-value">
                                    <span class="mobile-badge {{ $billing->payment_type == 'full' ? 'mobile-badge-info' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $billing->payment_type == 'full' ? 'Penuh' : 'Termin' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mobile-table-card-actions">
                            <a href="{{ route('project-billings.show', $billing) }}"
                               class="mobile-table-card-action primary" title="Lihat Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($billing->status !== 'paid')
                                <a href="{{ route('project-billings.edit', $billing) }}"
                                   class="mobile-table-card-action warning" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($billing->status !== 'paid')
                                <form action="{{ route('project-billings.destroy', $billing) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus penagihan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mobile-table-card-action danger" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($billings->hasPages())
                <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
                    {{ $billings->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="mobile-empty-state">
                <svg class="mobile-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mobile-empty-state-title">Belum ada penagihan</h3>
                <p class="mobile-empty-state-description">Mulai dengan membuat penagihan pertama Anda.</p>
                <a href="{{ route('project-billings.create') }}" class="mobile-empty-state-action">
                    Buat Penagihan
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile filter toggle functionality
    window.toggleMobileFilter = function() {
        const content = document.getElementById('mobile-filter-content');
        const toggleText = document.getElementById('filter-toggle-text');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            toggleText.textContent = 'Sembunyikan Filter';
        } else {
            content.classList.add('hidden');
            toggleText.textContent = 'Tampilkan Filter';
        }
    };

    // Auto-submit form when filter changes (desktop)
    const filterInputs = document.querySelectorAll('#payment_type, #status, #project_id');
    
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
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Only auto-submit on desktop
            if (window.innerWidth >= 640) {
                this.form.submit();
            }
        }, 500);
    });

    // Mobile responsive adjustments
    function handleResize() {
        const isMobile = window.innerWidth < 640;
        const filterContent = document.getElementById('mobile-filter-content');
        
        if (!isMobile) {
            // Show filter content on desktop
            filterContent.classList.remove('hidden');
        } else {
            // Hide filter content on mobile by default
            if (!filterContent.classList.contains('hidden')) {
                // Only hide if it wasn't manually opened
                const hasActiveFilters = document.querySelector('form').elements.namedItem('payment_type').value ||
                                        document.querySelector('form').elements.namedItem('status').value ||
                                        document.querySelector('form').elements.namedItem('project_id').value ||
                                        document.querySelector('form').elements.namedItem('search').value;
                
                if (!hasActiveFilters) {
                    filterContent.classList.add('hidden');
                    document.getElementById('filter-toggle-text').textContent = 'Tampilkan Filter';
                }
            }
        }
    }

    // Initial call and resize listener
    handleResize();
    window.addEventListener('resize', handleResize);

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