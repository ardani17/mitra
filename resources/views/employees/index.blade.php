<x-app-layout>
    <x-slot name="header">
        <div class="mobile-header">
            <h2 class="mobile-header-title sm:font-semibold sm:text-xl sm:text-gray-800 sm:leading-tight">
                {{ __('Manajemen Karyawan') }}
            </h2>
            <!-- Desktop Actions - Hidden on Mobile -->
            <div class="hidden sm:flex sm:space-x-2">
                <a href="{{ route('finance.employees.export') }}"
                   class="mobile-btn-success sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm sm:font-bold">
                    <i class="fas fa-download mr-2"></i>Export
                </a>
                <a href="{{ route('finance.employees.create') }}"
                   class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm sm:font-bold">
                    <i class="fas fa-plus mr-2"></i>Tambah Karyawan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mobile Actions - Below Title, Hidden on Desktop -->
            <div class="sm:hidden mb-6 px-4 space-y-3">
                <a href="{{ route('finance.employees.export') }}"
                   class="mobile-btn-success w-full justify-center">
                    <i class="fas fa-download mr-2"></i>Export
                </a>
                <a href="{{ route('finance.employees.create') }}"
                   class="mobile-btn-primary w-full justify-center">
                    <i class="fas fa-plus mr-2"></i>Tambah Karyawan
                </a>
            </div>
            <!-- Statistics Cards -->
            <div class="mobile-stats-grid sm:grid sm:grid-cols-2 md:grid-cols-4 sm:gap-6 mb-6">
                <div class="mobile-stat-card">
                    <div class="flex items-center">
                        <div class="stat-icon bg-blue-100">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Total Karyawan</p>
                            <p class="stat-value sm:text-2xl">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="mobile-stat-card">
                    <div class="flex items-center">
                        <div class="stat-icon bg-green-100">
                            <i class="fas fa-user-check text-green-600"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Aktif</p>
                            <p class="stat-value sm:text-2xl">{{ $stats['active'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="mobile-stat-card">
                    <div class="flex items-center">
                        <div class="stat-icon bg-red-100">
                            <i class="fas fa-user-times text-red-600"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Tidak Aktif</p>
                            <p class="stat-value sm:text-2xl">{{ $stats['inactive'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="mobile-stat-card" title="Total gaji karyawan yang sudah dikonfirmasi tapi belum dirilis. Setelah dirilis akan masuk ke pengeluaran.">
                    <div class="flex items-center">
                        <div class="stat-icon bg-green-100">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Anggaran Gaji</p>
                            <p class="stat-value sm:text-lg">{{ 'Rp ' . number_format($stats['salary_budget'], 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 mt-1">Belum dirilis</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="mobile-filter-container">
                <div class="mobile-filter-header">
                    <h3 class="mobile-filter-title">Filter Karyawan</h3>
                    <button type="button" class="mobile-filter-toggle sm:hidden bg-blue-50 text-blue-600 px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-100 transition-colors" onclick="toggleEmployeeFilter()">
                        <i class="fas fa-filter mr-1"></i>
                        <span id="employee-filter-toggle-text">Tampilkan Filter</span>
                    </button>
                </div>
                
                <div id="employee-filter-content" class="mobile-filter-content hidden sm:block" style="display: none;">
                    <form method="GET" action="{{ route('finance.employees.index') }}">
                        <div class="mobile-filter-row sm:grid sm:grid-cols-6 sm:gap-4">
                            <div class="mobile-filter-group sm:col-span-2">
                                <label for="search" class="mobile-filter-label">Pencarian</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       placeholder="Nama, kode, posisi, departemen..."
                                       class="mobile-filter-input">
                            </div>
                            <div class="mobile-filter-group">
                                <label for="status" class="mobile-filter-label">Status</label>
                                <select name="status" id="status" class="mobile-filter-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="mobile-filter-group">
                                <label for="department" class="mobile-filter-label">Departemen</label>
                                <select name="department" id="department" class="mobile-filter-select">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mobile-filter-group">
                                <label for="employment_type" class="mobile-filter-label">Tipe Karyawan</label>
                                <select name="employment_type" id="employment_type" class="mobile-filter-select">
                                    <option value="">Semua Tipe</option>
                                    <option value="permanent" {{ request('employment_type') === 'permanent' ? 'selected' : '' }}>Tetap</option>
                                    <option value="contract" {{ request('employment_type') === 'contract' ? 'selected' : '' }}>Kontrak</option>
                                    <option value="freelance" {{ request('employment_type') === 'freelance' ? 'selected' : '' }}>Freelance</option>
                                </select>
                            </div>
                            <div class="mobile-filter-group">
                                <label class="mobile-filter-label sm:invisible sm:select-none">Actions</label>
                                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full">
                                    <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                        <i class="fas fa-search mr-2"></i>Filter
                                    </button>
                                    <a href="{{ route('finance.employees.index') }}" class="mobile-btn-secondary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm text-center">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort Options -->
                        <div class="mobile-filter-row sm:flex sm:items-center sm:space-x-4 mt-4 pt-4 border-t border-gray-200">
                            <label class="mobile-filter-label sm:text-sm sm:font-medium sm:text-gray-700 sm:mb-0">Urutkan:</label>
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                <select name="sort_by" class="mobile-filter-select sm:w-auto">
                                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Nama</option>
                                    <option value="employee_code" {{ request('sort_by') === 'employee_code' ? 'selected' : '' }}>Kode</option>
                                    <option value="hire_date" {{ request('sort_by') === 'hire_date' ? 'selected' : '' }}>Tanggal Masuk</option>
                                    <option value="daily_rate" {{ request('sort_by') === 'daily_rate' ? 'selected' : '' }}>Gaji Harian</option>
                                    <option value="department" {{ request('sort_by') === 'department' ? 'selected' : '' }}>Departemen</option>
                                </select>
                                <select name="sort_order" class="mobile-filter-select sm:w-auto">
                                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>A-Z</option>
                                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Z-A</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Employees Table/Cards -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($employees->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden sm:block p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Karyawan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kontak
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Posisi & Departemen
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipe & Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status Gaji
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gaji Harian
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($employees as $employee)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                             src="{{ $employee->avatar_url }}"
                                                             alt="{{ $employee->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $employee->employee_code }}</div>
                                                        @if($employee->hasUnreleasedSalaries())
                                                            <div class="text-xs text-green-600 font-medium">
                                                                <i class="fas fa-money-bill-wave mr-1"></i>{{ $employee->getFormattedUnreleasedSalaryTotal() }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $employee->email ?: '-' }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->phone ?: '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $employee->position }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->department }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="mb-1">{!! $employee->employment_type_badge !!}</div>
                                                <div>{!! $employee->status_badge !!}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap" data-employee-id="{{ $employee->id }}">
                                                @php
                                                    $salaryStatus = $salaryStatusesKeyed[$employee->id] ?? null;
                                                @endphp
                                                
                                                @if($salaryStatus)
                                                    <x-salary-status-indicator :status="$salaryStatus" />
                                                @else
                                                    <span class="text-gray-400 text-sm">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $employee->formatted_daily_rate }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('finance.employees.show', $employee) }}"
                                                       class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('update', $employee)
                                                        <a href="{{ route('finance.employees.edit', $employee) }}"
                                                           class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $employee)
                                                        <form action="{{ route('finance.employees.destroy', $employee) }}"
                                                              method="POST" class="inline"
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden mobile-table-container p-4">
                        @foreach($employees as $employee)
                            <div class="mobile-table-card mobile-fade-in">
                                <div class="mobile-table-card-header">
                                    <div class="flex items-center">
                                        <img class="w-12 h-12 rounded-full object-cover flex-shrink-0"
                                             src="{{ $employee->avatar_url }}"
                                             alt="{{ $employee->name }}">
                                        <div class="ml-3 flex-1">
                                            <div class="mobile-table-card-title">{{ $employee->name }}</div>
                                            <div class="mobile-table-card-subtitle">{{ $employee->employee_code }} • {{ $employee->position }}</div>
                                            @if($employee->hasUnreleasedSalaries())
                                                <div class="text-xs text-green-600 font-medium mt-1">
                                                    <i class="fas fa-money-bill-wave mr-1"></i>{{ $employee->getFormattedUnreleasedSalaryTotal() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mobile-table-card-meta">
                                        <div class="mobile-table-card-amount text-slate-900">
                                            {{ $employee->formatted_daily_rate }}
                                        </div>
                                        <div class="mobile-table-card-status mt-1">
                                            @php
                                                $salaryStatus = $salaryStatusesKeyed[$employee->id] ?? null;
                                            @endphp
                                            
                                            @if($salaryStatus)
                                                <x-salary-status-indicator :status="$salaryStatus" />
                                            @else
                                                <span class="mobile-badge-secondary">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mobile-table-card-body">
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Departemen</span>
                                        <span class="mobile-table-card-value">{{ $employee->department }}</span>
                                    </div>
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Email</span>
                                        <span class="mobile-table-card-value">{{ $employee->email ?: '-' }}</span>
                                    </div>
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Telepon</span>
                                        <span class="mobile-table-card-value">{{ $employee->phone ?: '-' }}</span>
                                    </div>
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Tipe & Status</span>
                                        <span class="mobile-table-card-value">
                                            <div class="flex flex-wrap gap-1">
                                                {!! $employee->employment_type_badge !!}
                                                {!! $employee->status_badge !!}
                                            </div>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mobile-table-card-actions">
                                    <a href="{{ route('finance.employees.show', $employee) }}"
                                       class="mobile-table-card-action primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $employee)
                                        <a href="{{ route('finance.employees.edit', $employee) }}"
                                           class="mobile-table-card-action warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $employee)
                                        <form action="{{ route('finance.employees.destroy', $employee) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mobile-table-card-action danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $employees->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="mobile-empty-state">
                        <div class="mobile-empty-state-icon">
                            <i class="fas fa-users text-4xl"></i>
                        </div>
                        <h3 class="mobile-empty-state-title">Belum ada data karyawan</h3>
                        <p class="mobile-empty-state-description">Mulai dengan menambahkan karyawan pertama Anda.</p>
                        <a href="{{ route('finance.employees.create') }}" class="mobile-empty-state-action">
                            <i class="fas fa-plus mr-2"></i>Tambah Karyawan Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile employee filter toggle functionality
            window.toggleEmployeeFilter = function() {
                const content = document.getElementById('employee-filter-content');
                const toggleText = document.getElementById('employee-filter-toggle-text');
                const toggleButton = document.querySelector('.mobile-filter-toggle');
                
                if (content.classList.contains('hidden') || content.style.display === 'none') {
                    content.classList.remove('hidden');
                    content.style.display = 'block';
                    content.classList.add('mobile-slide-down');
                    if (toggleText) toggleText.textContent = 'Sembunyikan Filter';
                    if (toggleButton) {
                        toggleButton.classList.add('bg-blue-100');
                        toggleButton.classList.remove('bg-blue-50');
                    }
                } else {
                    content.classList.add('hidden');
                    content.style.display = 'none';
                    content.classList.remove('mobile-slide-down');
                    if (toggleText) toggleText.textContent = 'Tampilkan Filter';
                    if (toggleButton) {
                        toggleButton.classList.remove('bg-blue-100');
                        toggleButton.classList.add('bg-blue-50');
                    }
                }
            };

            // Mobile responsive adjustments
            function handleResize() {
                const isMobile = window.innerWidth < 640;
                const filterContent = document.getElementById('employee-filter-content');
                const toggleText = document.getElementById('employee-filter-toggle-text');
                const toggleButton = document.querySelector('.mobile-filter-toggle');
                
                if (!isMobile) {
                    // Show filter content on desktop
                    filterContent.classList.remove('hidden');
                    filterContent.style.display = '';
                } else {
                    // Always hide filter content on mobile by default
                    // Check if there are active filters to determine initial state
                    const hasActiveFilters = document.querySelector('form input[name="search"]').value ||
                                            document.querySelector('form select[name="status"]').value ||
                                            document.querySelector('form select[name="department"]').value ||
                                            document.querySelector('form select[name="employment_type"]').value;
                    
                    if (hasActiveFilters) {
                        // Show filter if there are active filters
                        filterContent.classList.remove('hidden');
                        filterContent.style.display = 'block';
                        if (toggleText) toggleText.textContent = 'Sembunyikan Filter';
                        if (toggleButton) {
                            toggleButton.classList.add('bg-blue-100');
                            toggleButton.classList.remove('bg-blue-50');
                        }
                    } else {
                        // Hide filter by default
                        filterContent.classList.add('hidden');
                        filterContent.style.display = 'none';
                        if (toggleText) toggleText.textContent = 'Tampilkan Filter';
                        if (toggleButton) {
                            toggleButton.classList.remove('bg-blue-100');
                            toggleButton.classList.add('bg-blue-50');
                        }
                    }
                }
            }

            // Initial call and resize listener
            handleResize();
            window.addEventListener('resize', handleResize);

            // Auto-submit form on select change (desktop only)
            const statusSelect = document.getElementById('status');
            const deptSelect = document.getElementById('department');
            const typeSelect = document.getElementById('employment_type');
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    // Only auto-submit on desktop
                    if (window.innerWidth >= 640) {
                        this.form.submit();
                    }
                });
            }
            
            if (deptSelect) {
                deptSelect.addEventListener('change', function() {
                    // Only auto-submit on desktop
                    if (window.innerWidth >= 640) {
                        this.form.submit();
                    }
                });
            }
            
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    // Only auto-submit on desktop
                    if (window.innerWidth >= 640) {
                        this.form.submit();
                    }
                });
            }

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
    @endpush
</x-app-layout>