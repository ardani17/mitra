<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Selamat Datang di Sistem Manajemen Proyek Telekomunikasi</h3>
                    <p class="text-gray-600 mb-4">
                        Sistem ini dirancang untuk mengelola proyek konstruksi telekomunikasi dengan fitur lengkap untuk manajemen keuangan, 
                        tracking progress, dan approval workflow.
                    </p>
                </div>
            </div>

            <!-- Basic Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Proyek</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $totalProjects }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Proyek Aktif</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $activeProjects }}</p>
                    </div>
                </div>
            </div>

            <!-- Role Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informasi Role</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                        <h4 class="font-semibold text-yellow-800">Perhatian</h4>
                        <p class="text-yellow-700">
                            Anda belum memiliki role yang sesuai atau role Anda tidak dikenali. 
                            Silakan hubungi administrator untuk mendapatkan akses yang tepat.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Available Features -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Fitur Sistem</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-800">Manajemen Proyek</h4>
                            <p class="text-sm text-gray-600">Kelola proyek konstruksi telekomunikasi dari perencanaan hingga penyelesaian.</p>
                        </div>
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-800">Tracking Keuangan</h4>
                            <p class="text-sm text-gray-600">Monitor budget, pengeluaran, dan revenue dengan sistem approval yang terintegrasi.</p>
                        </div>
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-800">Sistem Approval</h4>
                            <p class="text-sm text-gray-600">Workflow approval untuk pengeluaran dengan hierarki yang jelas.</p>
                        </div>
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-800">Reporting & Analytics</h4>
                            <p class="text-sm text-gray-600">Dashboard dan laporan komprehensif untuk analisis bisnis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
