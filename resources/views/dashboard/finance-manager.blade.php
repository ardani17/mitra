<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Finance Manager') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-emerald-100">Kelola keuangan dan cash flow perusahaan</p>
            </div>

            <!-- Financial KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
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

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Invoice Tertunda</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($pendingInvoices) }}</p>
                            <p class="text-sm opacity-75">Menunggu pembayaran</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Invoice Terlambat</h3>
                            <p class="text-2xl font-bold">{{ \App\Helpers\FormatHelper::formatRupiah($overdueInvoices) }}</p>
                            <p class="text-sm opacity-75">Perlu tindak lanjut</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            🚨
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
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
            </div>

            <!-- Cash Flow Bulanan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Revenue Bulan Ini</h3>
                        <p class="text-3xl font-bold text-green-600">Rp {{ number_format($monthlyRevenue) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Expenses Bulan Ini</h3>
                        <p class="text-3xl font-bold text-red-600">Rp {{ number_format($monthlyExpenses) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Persetujuan Tertunda</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                            <h4 class="font-semibold text-yellow-800">Pengeluaran Pending</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $pendingExpenses }}</p>
                            <p class="text-sm text-yellow-600">Total: Rp {{ number_format($pendingExpensesAmount) }}</p>
                            <a href="{{ route('expenses.index', ['status' => 'pending']) }}" class="text-yellow-700 hover:text-yellow-900 text-sm font-medium">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <h4 class="font-semibold text-blue-800">Net Cash Flow Bulan Ini</h4>
                            <p class="text-2xl font-bold {{ ($monthlyRevenue - $monthlyExpenses) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($monthlyRevenue - $monthlyExpenses) }}
                            </p>
                            <p class="text-sm text-blue-600">Revenue - Expenses</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts dan Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Penagihan berdasarkan Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Penagihan berdasarkan Status</h3>
                        <div class="space-y-2">
                            @foreach($billingsByStatus as $billing)
                                <div class="flex justify-between items-center">
                                    <span class="capitalize">{{ str_replace('_', ' ', $billing->status) }}</span>
                                    <span class="font-semibold">{{ $billing->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Top Proyek berdasarkan Revenue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Top Proyek (Revenue)</h3>
                        <div class="space-y-2">
                            @foreach($topProjects as $project)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">{{ Str::limit($project->name, 20) }}</span>
                                    <span class="font-semibold text-green-600">Rp {{ number_format($project->total_revenue) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('billings.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Penagihan
                        </a>
                        <a href="{{ route('billings.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Buat Invoice
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                            Review Expenses
                        </a>
                        <a href="{{ route('projects.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Proyek
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
