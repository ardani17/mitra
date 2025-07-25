<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Halaman Tidak Ditemukan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-center py-16">
                        <!-- Icon -->
                        <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-yellow-100 mb-8">
                            <svg class="h-16 w-16 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m6 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>

                        <!-- Error Code -->
                        <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
                        
                        <!-- Error Message -->
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Halaman Tidak Ditemukan</h2>
                        
                        <!-- Description -->
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                            Mungkin halaman telah dipindahkan atau URL yang dimasukkan salah.
                        </p>

                        <!-- Search Suggestion -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8 max-w-md mx-auto">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700">
                                        <strong>URL:</strong> {{ request()->url() }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Periksa kembali URL yang dimasukkan
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Kembali ke Dashboard
                            </a>
                            
                            <button onclick="history.back()" 
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Kembali
                            </button>
                        </div>

                        <!-- Quick Links -->
                        <div class="mt-12 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Halaman Populer</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 max-w-4xl mx-auto">
                                @auth
                                    @if(Auth::user()->roles->pluck('name')->contains('direktur') || 
                                        Auth::user()->roles->pluck('name')->contains('project_manager'))
                                        <a href="{{ route('projects.index') }}" 
                                           class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-150 ease-in-out">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-blue-900">Proyek</p>
                                                <p class="text-xs text-blue-600">Kelola proyek</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(Auth::user()->roles->pluck('name')->contains('finance_manager') || 
                                        Auth::user()->roles->pluck('name')->contains('direktur'))
                                        <a href="{{ route('expenses.index') }}" 
                                           class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-150 ease-in-out">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-green-900">Pengeluaran</p>
                                                <p class="text-xs text-green-600">Kelola expenses</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(Auth::user()->roles->pluck('name')->contains('finance_manager') || 
                                        Auth::user()->roles->pluck('name')->contains('direktur'))
                                        <a href="{{ route('billings.index') }}" 
                                           class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-150 ease-in-out">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-purple-900">Penagihan</p>
                                                <p class="text-xs text-purple-600">Kelola billing</p>
                                            </div>
                                        </a>
                                    @endif

                                    <a href="{{ route('reports.index') }}" 
                                       class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition duration-150 ease-in-out">
                                        <div class="flex-shrink-0">
                                            <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-orange-900">Laporan</p>
                                            <p class="text-xs text-orange-600">Lihat reports</p>
                                        </div>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
