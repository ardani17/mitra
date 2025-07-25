<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kesalahan Server') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-center py-16">
                        <!-- Icon -->
                        <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-red-100 mb-8">
                            <svg class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>

                        <!-- Error Code -->
                        <h1 class="text-6xl font-bold text-gray-900 mb-4">500</h1>
                        
                        <!-- Error Message -->
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Kesalahan Server Internal</h2>
                        
                        <!-- Description -->
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            Maaf, terjadi kesalahan pada server kami. 
                            Tim teknis telah diberitahu dan sedang menangani masalah ini.
                        </p>

                        <!-- Error Info -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8 max-w-md mx-auto">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800">
                                        <strong>Error ID:</strong> {{ Str::random(8) }}
                                    </p>
                                    <p class="text-sm text-red-600">
                                        <strong>Waktu:</strong> {{ now()->format('d/m/Y H:i:s') }}
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
                            
                            <button onclick="window.location.reload()" 
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Coba Lagi
                            </button>
                        </div>

                        <!-- Troubleshooting Tips -->
                        <div class="mt-12 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Langkah Troubleshooting</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                                <div class="text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900">Refresh Halaman</h4>
                                    <p class="text-sm text-gray-500">Coba muat ulang halaman ini</p>
                                </div>
                                
                                <div class="text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white mb-4">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900">Tunggu Sebentar</h4>
                                    <p class="text-sm text-gray-500">Server mungkin sedang sibuk</p>
                                </div>
                                
                                <div class="text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white mb-4">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900">Hubungi Support</h4>
                                    <p class="text-sm text-gray-500">Jika masalah berlanjut</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg max-w-md mx-auto">
                            <div class="flex items-center justify-center">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-2 w-2 bg-red-400 rounded-full animate-pulse"></div>
                                    </div>
                                    <div class="ml-2">
                                        <p class="text-sm text-gray-600">
                                            Tim teknis telah diberitahu tentang masalah ini
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
