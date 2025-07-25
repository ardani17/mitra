<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Finance Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistik Keuangan -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Revenue</h3>
                        <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalRevenue) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Pending Invoices</h3>
                        <p class="text-2xl font-bold text-yellow-600">Rp {{ number_format($pendingInvoices) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Overdue Invoices</h3>
                        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($overdueInvoices) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Expenses</h3>
                        <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($totalExpenses) }}</p>
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
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Pending Approvals</h3>
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
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
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
