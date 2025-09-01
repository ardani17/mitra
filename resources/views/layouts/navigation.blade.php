<nav x-data="{ open: false }" class="nav-primary border-b border-sky-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="hidden xs:block sm:block">
                            @php
                                $activeCompany = \App\Models\Company::getActive();
                            @endphp
                            @if($activeCompany)
                                <span class="text-base sm:text-lg font-bold text-gray-800">{{ $activeCompany->name }}</span>
                                <div class="text-xs text-gray-600 hidden sm:block">Project Management System</div>
                            @else
                                <span class="text-base sm:text-lg font-bold text-gray-800">Mitra</span>
                                <div class="text-xs text-gray-600 hidden sm:block">Project Management</div>
                            @endif
                        </div>
                        <!-- Mobile-only short title -->
                        <div class="block xs:hidden">
                            @php
                                $activeCompany = \App\Models\Company::getActive();
                            @endphp
                            @if($activeCompany)
                                <span class="text-base font-bold text-gray-800">{{ Str::limit($activeCompany->name, 15) }}</span>
                            @else
                                <span class="text-base font-bold text-gray-800">Mitra</span>
                            @endif
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 lg:space-x-8 sm:-my-px sm:ms-6 lg:ms-10 sm:flex">
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
                    @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager', 'project_manager']))
                    <x-nav-link :href="route('expense-approvals.index')" :active="request()->routeIs('expense-approvals.*')">
                        {{ __('Approval Pengeluaran') }}
                    </x-nav-link>
                    @endif

                    <!-- Billings Menu (Only for Finance Manager and Direktur) -->
                    @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager']))
                    <div class="relative inline-flex items-center" x-data="{ billingOpen: false }" x-init="billingOpen = false">
                        <button @click.stop="billingOpen = ! billingOpen" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                               {{ request()->routeIs('billing-batches.*', 'project-billings.*', 'billing-dashboard.*') ? 'border-white text-white focus:border-sky-200' : 'border-transparent text-sky-100 hover:text-white hover:border-sky-200 focus:text-white focus:border-sky-200' }}">
                            {{ __('Penagihan') }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="billingOpen" @click.away="billingOpen = false" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="top: 100%; display: none;">
                            <div class="py-1">
                                <a href="{{ route('billing-batches.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('billing-batches.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Penagihan Batch
                                </a>
                                <a href="{{ route('project-billings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('project-billings.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Penagihan Per-Proyek
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('billing-dashboard.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('billing-dashboard.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Dashboard Penagihan
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Financial Menu (Only for Finance Manager and Direktur) -->
                    @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager']))
                    <div class="relative inline-flex items-center" x-data="{ financeOpen: false }" x-init="financeOpen = false">
                        <button @click.stop="financeOpen = ! financeOpen" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                               {{ request()->routeIs('finance.*') ? 'border-white text-white focus:border-sky-200' : 'border-transparent text-sky-100 hover:text-white hover:border-sky-200 focus:text-white focus:border-sky-200' }}">
                            {{ __('Keuangan') }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="financeOpen" @click.away="financeOpen = false" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="top: 100%; display: none;">
                            <div class="py-1">
                                <a href="{{ route('finance.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('finance.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Dashboard Keuangan
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('finance.cashflow.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('finance.cashflow.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    Jurnal Cashflow
                                </a>
                                <a href="{{ route('finance.cashflow.income') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                    Pemasukan
                                </a>
                                <a href="{{ route('finance.cashflow.expense') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    </svg>
                                    Pengeluaran
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('finance.employees.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('finance.employees.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"/>
                                    </svg>
                                    Manajemen Karyawan
                                </a>
                           </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- User Management Menu (Only for Direktur) -->
                    @if(auth()->user()->hasRole('direktur'))
                    <div class="relative inline-flex items-center" x-data="{ managementOpen: false }" x-init="managementOpen = false">
                        <button @click.stop="managementOpen = ! managementOpen" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                               {{ request()->routeIs('users.*', 'settings.*', 'telegram-bot.*') ? 'border-white text-white focus:border-sky-200' : 'border-transparent text-sky-100 hover:text-white hover:border-sky-200 focus:text-white focus:border-sky-200' }}">
                            {{ __('Manajemen') }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="managementOpen" @click.away="managementOpen = false" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="top: 100%; display: none;">
                            <div class="py-1">
                                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('users.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"/>
                                    </svg>
                                    Manajemen User
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('settings.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Pengaturan Sistem
                                </a>
                                <a href="{{ route('system-statistics.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('system-statistics.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Statistik Sistem
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-1">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tools</div>
                                </div>
                                <a href="{{ route('telegram-bot.config') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('telegram-bot.config') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Bot Configuration
                                </a>
                                <a href="{{ route('telegram-bot.explorer') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('telegram-bot.explorer') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    File Explorer
                                </a>
                                <a href="{{ route('telegram-bot.activity') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('telegram-bot.activity') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    Bot Activity
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-1">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">User Management</div>
                                </div>
                                <a href="{{ route('telegram-bot.users') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('telegram-bot.users*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Bot Users
                                </a>
                                <a href="{{ route('telegram-bot.registrations') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150 ease-in-out {{ request()->routeIs('telegram-bot.registrations') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    Registration Requests
                                    @php
                                        $pendingCount = \App\Models\BotRegistrationRequest::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $pendingCount }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-3 lg:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 lg:px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <!-- Mobile: Show only initials -->
                            <div class="block lg:hidden">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <!-- Desktop: Show full name -->
                            <div class="hidden lg:block">{{ Auth::user()->name }}</div>

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
                                @if(Auth::user()->roles)
                                    @foreach(Auth::user()->roles as $role)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                    @endforeach
                                @endif
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
            @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager', 'project_manager']))
            <x-responsive-nav-link :href="route('expense-approvals.index')" :active="request()->routeIs('expense-approvals.*')">
                {{ __('Approval Pengeluaran') }}
            </x-responsive-nav-link>
            @endif

            <!-- Billings Menu (Only for Finance Manager and Direktur) -->
            @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager']))
                <div class="border-t border-gray-200 pt-2">
                    <div class="px-4 py-2">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Penagihan</div>
                    </div>
                    <x-responsive-nav-link :href="route('billing-batches.index')" :active="request()->routeIs('billing-batches.*')">
                        <div class="flex items-center">
                            <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ __('Penagihan Batch') }}
                        </div>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('project-billings.index')" :active="request()->routeIs('project-billings.*')">
                        <div class="flex items-center">
                            <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('Penagihan Per-Proyek') }}
                        </div>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('billing-dashboard.index')">
                        <div class="flex items-center">
                            <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ __('Dashboard Penagihan') }}
                        </div>
                    </x-responsive-nav-link>
                </div>
            @endif

            <!-- Financial Menu (Only for Finance Manager and Direktur) -->
            @if(auth()->user()->hasAnyRole(['direktur', 'finance_manager']))
                <div class="responsive-nav-section">
                    <div class="responsive-nav-section-title">
                        Keuangan
                    </div>
                    <x-responsive-nav-link :href="route('finance.dashboard')" :active="request()->routeIs('finance.dashboard')" class="responsive-nav-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ __('Dashboard Keuangan') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('finance.cashflow.index')" :active="request()->routeIs('finance.cashflow.*')" class="responsive-nav-item {{ request()->routeIs('finance.cashflow.*') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        {{ __('Jurnal Cashflow') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('finance.cashflow.income')" class="responsive-nav-item">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                        {{ __('Pemasukan') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('finance.cashflow.expense')" class="responsive-nav-item">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                        {{ __('Pengeluaran') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('finance.employees.index')" :active="request()->routeIs('finance.employees.*')" class="responsive-nav-item {{ request()->routeIs('finance.employees.*') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"/>
                        </svg>
                        {{ __('Manajemen Karyawan') }}
                    </x-responsive-nav-link>
                </div>
            @endif

            <!-- User Management Menu (Only for Direktur) -->
            @if(auth()->user()->hasRole('direktur'))
                <div class="responsive-nav-section">
                    <div class="responsive-nav-section-title">
                        Manajemen
                    </div>
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="responsive-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"/>
                        </svg>
                        {{ __('Manajemen User') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" class="responsive-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('Pengaturan Sistem') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('system-statistics.index')" :active="request()->routeIs('system-statistics.*')" class="responsive-nav-item {{ request()->routeIs('system-statistics.*') ? 'active' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ __('Statistik Sistem') }}
                    </x-responsive-nav-link>
                    
                    <div class="border-t border-gray-200 mt-2 pt-2">
                        <div class="px-4 py-2">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tools</div>
                        </div>
                        <x-responsive-nav-link :href="route('telegram-bot.config')" :active="request()->routeIs('telegram-bot.config')" class="responsive-nav-item {{ request()->routeIs('telegram-bot.config') ? 'active' : '' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            {{ __('Bot Configuration') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('telegram-bot.explorer')" :active="request()->routeIs('telegram-bot.explorer')" class="responsive-nav-item {{ request()->routeIs('telegram-bot.explorer') ? 'active' : '' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ __('File Explorer') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('telegram-bot.activity')" :active="request()->routeIs('telegram-bot.activity')" class="responsive-nav-item {{ request()->routeIs('telegram-bot.activity') ? 'active' : '' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            {{ __('Bot Activity') }}
                        </x-responsive-nav-link>
                        
                        <div class="border-t border-gray-200 mt-2 pt-2">
                            <div class="px-4 py-2">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">User Management</div>
                            </div>
                            <x-responsive-nav-link :href="route('telegram-bot.users')" :active="request()->routeIs('telegram-bot.users*')" class="responsive-nav-item {{ request()->routeIs('telegram-bot.users*') ? 'active' : '' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ __('Bot Users') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('telegram-bot.registrations')" :active="request()->routeIs('telegram-bot.registrations')" class="responsive-nav-item {{ request()->routeIs('telegram-bot.registrations') ? 'active' : '' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                {{ __('Registration Requests') }}
                                @php
                                    $pendingCount = \App\Models\BotRegistrationRequest::where('status', 'pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </x-responsive-nav-link>
                        </div>
                    </div>
                </div>
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
