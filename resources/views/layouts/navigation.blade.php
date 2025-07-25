<nav x-data="{ open: false }" class="nav-primary border-b border-sky-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold text-gray-800">Mitra</span>
                            <div class="text-xs text-gray-600">Project Management</div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Projects Menu -->
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                        {{ __('Proyek') }}
                    </x-nav-link>

                    <!-- Expenses Menu -->
                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        {{ __('Pengeluaran') }}
                    </x-nav-link>

                    <!-- Expense Approvals Menu (Only for Finance Manager, Project Manager, and Direktur) -->
                    @if(auth()->user()->roles->whereIn('name', ['direktur', 'finance_manager', 'project_manager'])->count() > 0)
                    <x-nav-link :href="route('expense-approvals.index')" :active="request()->routeIs('expense-approvals.*')">
                        {{ __('Approval Pengeluaran') }}
                    </x-nav-link>
                    @endif

                    <!-- Billings Menu (Only for Finance Manager and Direktur) -->
                    @if(auth()->user()->roles->whereIn('name', ['direktur', 'finance_manager'])->count() > 0)
                    <x-nav-link :href="route('billing-batches.index')" :active="request()->routeIs('billing-batches.*')">
                        {{ __('Penagihan') }}
                    </x-nav-link>
                    
                    @if(auth()->user()->hasRole(['direktur', 'finance_manager', 'project_manager']))
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('Laporan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('documentation')" :active="request()->routeIs('documentation')">
                        {{ __('Dokumentasi') }}
                    </x-nav-link>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Role Info -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="text-sm text-gray-600">Role:</div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach(Auth::user()->roles as $role)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>


                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Keluar') }}
                        </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-sky-200 hover:bg-sky-600 focus:outline-none focus:bg-sky-600 focus:text-sky-200 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Projects Menu -->
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                {{ __('Proyek') }}
            </x-responsive-nav-link>

            <!-- Expenses Menu -->
            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                {{ __('Pengeluaran') }}
            </x-responsive-nav-link>

            <!-- Expense Approvals Menu (Only for Finance Manager, Project Manager, and Direktur) -->
            @if(auth()->user()->roles->whereIn('name', ['direktur', 'finance_manager', 'project_manager'])->count() > 0)
            <x-responsive-nav-link :href="route('expense-approvals.index')" :active="request()->routeIs('expense-approvals.*')">
                {{ __('Approval Pengeluaran') }}
            </x-responsive-nav-link>
            @endif

            <!-- Billings Menu (Only for Finance Manager and Direktur) -->
            @if(auth()->user()->roles->whereIn('name', ['direktur', 'finance_manager'])->count() > 0)
                <x-responsive-nav-link :href="route('billing-batches.index')" :active="request()->routeIs('billing-batches.*')">
                    {{ __('Penagihan') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>


                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Keluar') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
