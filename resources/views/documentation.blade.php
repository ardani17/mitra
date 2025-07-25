<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dokumentasi Aplikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            Aplikasi Manajemen Proyek Telekomunikasi
                        </h1>
                        <p class="text-lg text-gray-600">
                            Sistem manajemen proyek konstruksi telekomunikasi dengan fitur lengkap untuk tracking anggaran, timeline, dan profitabilitas.
                        </p>
                    </div>

                    <!-- Fitur Utama -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">Role-Based Access Control</h3>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Direktur - Akses penuh</li>
                                <li>• Project Manager - Manajemen proyek</li>
                                <li>• Finance Manager - Keuangan</li>
                                <li>• Staf - Input data</li>
                            </ul>
                        </div>

                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 mb-3">Manajemen Proyek</h3>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• Kode proyek otomatis</li>
                                <li>• Tracking nilai jasa & material</li>
                                <li>• Status dan progress monitoring</li>
                                <li>• Timeline dan milestone</li>
                            </ul>
                        </div>

                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-3">Sistem Keuangan</h3>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• Tracking pengeluaran</li>
                                <li>• Sistem approval workflow</li>
                                <li>• Penagihan dan pendapatan</li>
                                <li>• Analisis profitabilitas</li>
                            </ul>
                        </div>

                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-900 mb-3">Laporan & Analytics</h3>
                            <ul class="text-sm text-purple-700 space-y-1">
                                <li>• Dashboard real-time</li>
                                <li>• Laporan keuangan</li>
                                <li>• Export Excel/PDF</li>
                                <li>• Grafik dan visualisasi</li>
                            </ul>
                        </div>

                        <div class="bg-red-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-red-900 mb-3">Import/Export</h3>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Import proyek dari Excel</li>
                                <li>• Template Excel</li>
                                <li>• Export laporan</li>
                                <li>• Validasi data import</li>
                            </ul>
                        </div>

                        <div class="bg-indigo-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-indigo-900 mb-3">UI/UX Modern</h3>
                            <ul class="text-sm text-indigo-700 space-y-1">
                                <li>• Responsive design</li>
                                <li>• Tailwind CSS</li>
                                <li>• Filter dan search</li>
                                <li>• Animasi smooth</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Status Implementasi -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Status Implementasi</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-green-100 border border-green-300 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-green-800 mb-3">✅ Fitur Selesai</h3>
                                <ul class="text-sm text-green-700 space-y-1">
                                    <li>• Database dan migrasi lengkap</li>
                                    <li>• Autentikasi dan RBAC</li>
                                    <li>• CRUD semua entitas</li>
                                    <li>• Dashboard role-based</li>
                                    <li>• Sistem approval expense</li>
                                    <li>• Import/Export Excel</li>
                                    <li>• Laporan keuangan</li>
                                    <li>• UI responsive modern</li>
                                    <li>• Seeder data sample</li>
                                </ul>
                            </div>

                            <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-yellow-800 mb-3">🚧 Dalam Pengembangan</h3>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Notifikasi sistem</li>
                                    <li>• Audit trail lengkap</li>
                                    <li>• Dark mode support</li>
                                    <li>• Advanced analytics</li>
                                    <li>• Mobile app companion</li>
                                    <li>• API documentation</li>
                                    <li>• Unit testing</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Teknologi -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Teknologi yang Digunakan</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-2xl mb-2">🐘</div>
                                <div class="font-semibold">Laravel 12</div>
                                <div class="text-sm text-gray-600">Backend Framework</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-2xl mb-2">🐘</div>
                                <div class="font-semibold">PostgreSQL</div>
                                <div class="text-sm text-gray-600">Database</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-2xl mb-2">🎨</div>
                                <div class="font-semibold">Tailwind CSS</div>
                                <div class="text-sm text-gray-600">UI Framework</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-2xl mb-2">📊</div>
                                <div class="font-semibold">Maatwebsite Excel</div>
                                <div class="text-sm text-gray-600">Import/Export</div>
                            </div>
                        </div>
                    </div>

                    <!-- User Accounts -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Akun Testing</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900">Direktur</h3>
                                <p class="text-sm text-blue-700 mt-1">
                                    Email: direktur@mitra.com<br>
                                    Password: password
                                </p>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h3 class="font-semibold text-green-900">Project Manager</h3>
                                <p class="text-sm text-green-700 mt-1">
                                    Email: pm@mitra.com<br>
                                    Password: password
                                </p>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h3 class="font-semibold text-yellow-900">Finance Manager</h3>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Email: finance@mitra.com<br>
                                    Password: password
                                </p>
                            </div>
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <h3 class="font-semibold text-purple-900">Staf</h3>
                                <p class="text-sm text-purple-700 mt-1">
                                    Email: staf@mitra.com<br>
                                    Password: password
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="border-t pt-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Quick Links</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="{{ route('projects.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                                <div class="text-2xl mb-2">📋</div>
                                <div class="font-semibold">Proyek</div>
                            </a>
                            <a href="{{ route('expenses.index') }}" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                                <div class="text-2xl mb-2">💰</div>
                                <div class="font-semibold">Pengeluaran</div>
                            </a>
                            <a href="{{ route('billings.index') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded-lg text-center transition-colors">
                                <div class="text-2xl mb-2">🧾</div>
                                <div class="font-semibold">Penagihan</div>
                            </a>
                            <a href="{{ route('reports.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
                                <div class="text-2xl mb-2">📊</div>
                                <div class="font-semibold">Laporan</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
