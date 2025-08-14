<x-app-layout>
    <x-slot name="header">
        <div class="mobile-header sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 class="mobile-header-title sm:font-semibold sm:text-xl sm:text-gray-800 sm:leading-tight">
                    Manajemen User
                </h2>
                <p class="mobile-header-subtitle sm:text-base">Kelola pengguna dan hak akses sistem</p>
            </div>
            <div class="mobile-header-actions sm:flex-row sm:space-y-0 sm:space-x-3 sm:w-auto">
                <a href="{{ route('users.create') }}" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah User
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="mobile-filter-container">
                <div class="mobile-filter-header">
                    <h3 class="mobile-filter-title">Filter User</h3>
                    <button type="button" class="mobile-filter-toggle sm:hidden" onclick="toggleUserFilter()">
                        <span id="user-filter-toggle-text">Tampilkan Filter</span>
                    </button>
                </div>
                
                <div id="user-filter-content" class="mobile-filter-content hidden sm:block">
                    <form method="GET" action="{{ route('users.index') }}">
                        <div class="mobile-filter-row sm:grid sm:grid-cols-3 sm:gap-4">
                            <div class="mobile-filter-group">
                                <label for="search" class="mobile-filter-label">Cari User</label>
                                <input type="text"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nama atau email..."
                                       class="mobile-filter-input">
                            </div>
                            <div class="mobile-filter-group">
                                <label for="role" class="mobile-filter-label">Role</label>
                                <select name="role" id="role" class="mobile-filter-select">
                                    <option value="">Semua Role</option>
                                    @foreach($roles ?? [] as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="mobile-filter-actions sm:flex sm:justify-end sm:space-x-3">
                            <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                            @if(request()->hasAny(['search', 'role']))
                                <a href="{{ route('users.index') }}" class="mobile-btn-secondary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm text-center">
                                    <i class="fas fa-times mr-2"></i>Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table/Cards -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                @if(isset($users) && $users->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Perusahaan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users ?? [] as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-blue-600 font-medium text-sm">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                    <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                            @if($user->email_verified_at)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Terverifikasi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Belum Verifikasi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @foreach($user->roles ?? [] as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($role->name == 'direktur') bg-purple-100 text-purple-800
                                                    @elseif($role->name == 'project_manager') bg-blue-100 text-blue-800
                                                    @elseif($role->name == 'finance_manager') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(isset($activeCompany) && $activeCompany)
                                                <div class="text-sm text-gray-900 font-medium">{{ $activeCompany->name }}</div>
                                                @if($activeCompany->phone)
                                                    <div class="text-sm text-gray-500">{{ $activeCompany->phone }}</div>
                                                @endif
                                            @else
                                                <div class="text-sm text-gray-500">-</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-wrap gap-2">
                                                <!-- View Button -->
                                                <a href="{{ route('users.show', $user) }}"
                                                   class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-md transition duration-200"
                                                   title="Lihat Detail User">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Lihat
                                                </a>
                                                
                                                <!-- Edit Button -->
                                                <a href="{{ route('users.edit', $user) }}"
                                                   class="inline-flex items-center px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium rounded-md transition duration-200"
                                                   title="Edit User">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Edit
                                                </a>
                                                
                                                <!-- Delete Button (Only if not current user and not direktur) -->
                                                @if($user->id !== auth()->id() && !$user->hasRole('direktur'))
                                                    <form action="{{ route('users.destroy', $user) }}"
                                                          method="POST"
                                                          class="inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait user tersebut.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition duration-200"
                                                                title="Hapus User">
                                                            <i class="fas fa-trash mr-1"></i>
                                                            Hapus
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
                        @foreach($users ?? [] as $user)
                            <div class="mobile-table-card mobile-fade-in">
                                <div class="mobile-table-card-header">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-600 font-medium text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="mobile-table-card-title">{{ $user->name }}</div>
                                            <div class="mobile-table-card-subtitle">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-table-card-meta">
                                        <div class="mobile-table-card-status">
                                            @if($user->email_verified_at)
                                                <span class="mobile-badge-success">Terverifikasi</span>
                                            @else
                                                <span class="mobile-badge-warning">Belum Verifikasi</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mobile-table-card-body">
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Email</span>
                                        <span class="mobile-table-card-value">{{ $user->email }}</span>
                                    </div>
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Role</span>
                                        <span class="mobile-table-card-value">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($user->roles ?? [] as $role)
                                                    <span class="mobile-badge
                                                        @if($role->name == 'direktur') bg-purple-100 text-purple-800
                                                        @elseif($role->name == 'project_manager') bg-blue-100 text-blue-800
                                                        @elseif($role->name == 'finance_manager') bg-green-100 text-green-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </span>
                                    </div>
                                    <div class="mobile-table-card-row">
                                        <span class="mobile-table-card-label">Perusahaan</span>
                                        <span class="mobile-table-card-value">
                                            @if(isset($activeCompany) && $activeCompany)
                                                <div>{{ $activeCompany->name }}</div>
                                                @if($activeCompany->phone)
                                                    <div class="text-xs text-gray-500">{{ $activeCompany->phone }}</div>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mobile-table-card-actions">
                                    <a href="{{ route('users.show', $user) }}"
                                       class="mobile-table-card-action primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="mobile-table-card-action warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id() && !$user->hasRole('direktur'))
                                        <form action="{{ route('users.destroy', $user) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait user tersebut.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mobile-table-card-action danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(isset($users) && $users->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $users->links() ?? '' }}
                        </div>
                    @endif
                @else
                    <div class="mobile-empty-state">
                        <div class="mobile-empty-state-icon">
                            <i class="fas fa-users text-4xl"></i>
                        </div>
                        <h3 class="mobile-empty-state-title">Tidak ada user yang ditemukan</h3>
                        <p class="mobile-empty-state-description">Mulai dengan menambahkan user pertama.</p>
                        <a href="{{ route('users.create') }}" class="mobile-empty-state-action">
                            <i class="fas fa-plus mr-2"></i>Tambah User
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.fixed.bottom-4').remove();
            }, 3000);
        </script>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.fixed.bottom-4').remove();
            }, 3000);
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile user filter toggle functionality
            window.toggleUserFilter = function() {
                const content = document.getElementById('user-filter-content');
                const toggleText = document.getElementById('user-filter-toggle-text');
                
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    toggleText.textContent = 'Sembunyikan Filter';
                } else {
                    content.classList.add('hidden');
                    toggleText.textContent = 'Tampilkan Filter';
                }
            };

            // Mobile responsive adjustments
            function handleResize() {
                const isMobile = window.innerWidth < 640;
                const filterContent = document.getElementById('user-filter-content');
                
                if (!isMobile && filterContent) {
                    // Show filter content on desktop
                    filterContent.classList.remove('hidden');
                } else if (isMobile && filterContent) {
                    // Hide filter content on mobile by default
                    if (!filterContent.classList.contains('hidden')) {
                        // Only hide if it wasn't manually opened
                        const searchInput = document.querySelector('form input[name="search"]');
                        const roleSelect = document.querySelector('form select[name="role"]');
                        const hasActiveFilters = (searchInput && searchInput.value) || (roleSelect && roleSelect.value);
                        
                        if (!hasActiveFilters) {
                            filterContent.classList.add('hidden');
                            const toggleText = document.getElementById('user-filter-toggle-text');
                            if (toggleText) {
                                toggleText.textContent = 'Tampilkan Filter';
                            }
                        }
                    }
                }
            }

            // Initial call and resize listener
            handleResize();
            window.addEventListener('resize', handleResize);

            // Auto-submit form when filter changes (desktop only)
            const roleSelect = document.getElementById('role');
            
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
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
</x-app-layout>
