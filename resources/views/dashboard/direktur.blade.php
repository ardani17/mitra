<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Direktur') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-blue-100">Overview lengkap perusahaan dan performa proyek</p>
            </div>

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Proyek</h3>
                            <p class="text-3xl font-bold">{{ $totalProjects }}</p>
                            <p class="text-sm opacity-75">{{ $activeProjects }} aktif</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            📊
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Pendapatan</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($totalRevenue) }}</p>
                            <p class="text-sm opacity-75">Pendapatan terkonfirmasi</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            💰
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Pengeluaran</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($totalExpenses) }}</p>
                            <p class="text-sm opacity-75">Pengeluaran disetujui</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            💸
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 to-{{ $netProfit >= 0 ? 'green' : 'red' }}-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Laba Bersih</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($netProfit) }}</p>
                            <p class="text-sm opacity-75">
                                @php
                                    $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
                                @endphp
                                Margin: {{ number_format($profitMargin, 1) }}%
                            </p>
                        </div>
                        <div class="text-4xl opacity-75">
                            {{ $netProfit >= 0 ? '📈' : '📉' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Anggaran -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Anggaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Total Anggaran</h4>
                        <p class="text-2xl font-bold text-blue-600">{{ \App\Helpers\FormatHelper::formatRupiah($totalBudget) }}</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Utilisasi Anggaran</h4>
                        @php
                            $budgetUtilization = $totalBudget > 0 ? ($totalExpenses / $totalBudget) * 100 : 0;
                        @endphp
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($budgetUtilization, 1) }}%</p>
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                            <div class="bg-orange-500 h-3 rounded-full transition-all duration-300" style="width: {{ min($budgetUtilization, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Sisa Anggaran</h4>
                        <p class="text-2xl font-bold text-green-600">{{ \App\Helpers\FormatHelper::formatRupiah($totalBudget - $totalExpenses) }}</p>
                    </div>
                </div>
            </div>

            <!-- Charts dan Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Proyek berdasarkan Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Proyek berdasarkan Status</h3>
                        <div class="space-y-2">
                            @foreach($projectsByStatus as $status)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $status->status) }}</span>
                                    <span class="font-semibold">{{ $status->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Proyek berdasarkan Tipe -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Proyek berdasarkan Tipe</h3>
                        <div class="space-y-2">
                            @foreach($projectsByType as $type)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $type->type) }}</span>
                                    <span class="font-semibold">{{ $type->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peringatan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Persetujuan Tertunda</h3>
                        <div class="flex justify-between items-center">
                            <span>Pengeluaran Tertunda</span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $pendingExpenses }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span>Invoice Terlambat</span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-semibold">
                                {{ $overdueInvoices }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Aktivitas Terbaru</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($recentActivities as $activity)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $activity->user_name }}</span>
                                    <span class="text-gray-600">{{ $activity->description }}</span>
                                    <div class="text-xs text-gray-500">{{ $activity->project_name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('projects.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Proyek
                        </a>
                        <a href="{{ route('companies.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Perusahaan
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Pengeluaran
                        </a>
                        <a href="{{ route('billings.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Penagihan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
