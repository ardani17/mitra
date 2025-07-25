<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mitra - Sistem Manajemen Proyek Konstruksi Telekomunikasi</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
            </style>
        @endif
        
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
        <!-- Header -->
        <header class="w-full bg-white/80 backdrop-blur-sm border-b border-blue-200/50 sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">Mitra</h1>
                            <p class="text-xs text-gray-600">Project Management System</p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <nav class="flex items-center space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="text-gray-700 hover:text-blue-600 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Masuk
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                                        Daftar
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-6 py-12">
            <div class="max-w-6xl mx-auto">
                <!-- Hero Section -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium mb-6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Sistem Manajemen Proyek Terdepan
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Kelola Proyek
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                            Konstruksi Telekomunikasi
                        </span>
                        dengan Mudah
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                        Platform terintegrasi untuk mengelola proyek konstruksi telekomunikasi dari perencanaan hingga penagihan. 
                        Tingkatkan efisiensi tim dan transparansi keuangan proyek Anda.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                Mulai Sekarang
                            </a>
                        @endauth
                        <a href="#features" 
                           class="border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                <!-- Features Section -->
                <div id="features" class="grid md:grid-cols-3 gap-8 mb-16">
                    <!-- Feature 1 -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Manajemen Proyek</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Kelola seluruh siklus hidup proyek konstruksi telekomunikasi dari perencanaan, eksekusi, hingga penyelesaian dengan sistem yang terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Kontrol Keuangan</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Pantau anggaran, pengeluaran, dan profitabilitas proyek secara real-time dengan sistem approval workflow yang terstruktur.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Sistem Tagihan</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Otomatisasi proses penagihan dengan integrasi langsung ke data proyek, mendukung berbagai jenis klien dan skema pajak.
                        </p>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="bg-white rounded-2xl p-8 shadow-lg mb-16">
                    <div class="grid md:grid-cols-4 gap-8 text-center">
                        <div>
                            <div class="text-3xl font-bold text-blue-600 mb-2">100+</div>
                            <div class="text-gray-600">Proyek Dikelola</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-green-600 mb-2">95%</div>
                            <div class="text-gray-600">Tingkat Kepuasan</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-purple-600 mb-2">24/7</div>
                            <div class="text-gray-600">Dukungan Sistem</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-indigo-600 mb-2">4</div>
                            <div class="text-gray-600">Level User Role</div>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="text-center bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-12 text-white">
                    <h2 class="text-3xl font-bold mb-4">Siap Meningkatkan Efisiensi Proyek Anda?</h2>
                    <p class="text-xl mb-8 opacity-90">
                        Bergabunglah dengan perusahaan konstruksi telekomunikasi yang telah mempercayai sistem kami.
                    </p>
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Akses Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Mulai Gratis Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12 mt-16">
            <div class="container mx-auto px-6">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold">Mitra</span>
                        </div>
                        <p class="text-gray-400">
                            Sistem manajemen proyek konstruksi telekomunikasi yang membantu perusahaan meningkatkan efisiensi dan transparansi.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">Fitur Utama</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Manajemen Proyek</li>
                            <li>Kontrol Keuangan</li>
                            <li>Sistem Tagihan</li>
                            <li>Laporan & Analytics</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">User Roles</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Direktur</li>
                            <li>Project Manager</li>
                            <li>Finance Manager</li>
                            <li>Staff</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">Dukungan</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Dokumentasi</li>
                            <li>Tutorial</li>
                            <li>Support 24/7</li>
                            <li>Training</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} Mitra Project Management System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
