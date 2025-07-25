<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Staf') }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-indigo-100">Kelola tugas dan pengeluaran proyek Anda</p>
            </div>

            <!-- Staff Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Proyek Aktif</h3>
                            <p class="text-3xl font-bold">{{ $activeProjects }}</p>
                            <p class="text-sm opacity-75">Sedang berjalan</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            🚀
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Total Pengeluaran</h3>
                            <p class="text-3xl font-bold">{{ $myExpenses }}</p>
                            <p class="text-sm opacity-75">Yang saya buat</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            📝
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Menunggu Persetujuan</h3>
                            <p class="text-3xl font-bold">{{ $myPendingExpenses }}</p>
                            <p class="text-sm opacity-75">Menunggu persetujuan</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold opacity-90">Disetujui</h3>
                            <p class="text-3xl font-bold">{{ $myApprovedExpenses }}</p>
                            <p class="text-sm opacity-75">Sudah disetujui</p>
                        </div>
                        <div class="text-4xl opacity-75">
                            ✅
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Summary -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Pengeluaran Saya</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Total Pengajuan</h4>
                        <p class="text-2xl font-bold text-blue-600">{{ $myExpenses }}</p>
                        <p class="text-sm text-gray-500">Semua expense yang dibuat</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Tingkat Keberhasilan</h4>
                        @php
                            $successRate = $myExpenses > 0 ? ($myApprovedExpenses / $myExpenses) * 100 : 0;
                        @endphp
                        <p class="text-2xl font-bold text-green-600">{{ number_format($successRate, 1) }}%</p>
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                            <div class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: {{ $successRate }}%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-700">Menunggu Review</h4>
                        <p class="text-2xl font-bold text-yellow-600">{{ $myPendingExpenses }}</p>
                        <p class="text-sm text-gray-500">Butuh tindak lanjut</p>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Pengeluaran Terbaru Saya</h3>
                    @if($recentExpenses->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentExpenses as $expense)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $expense->project->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ Str::limit($expense->description, 30) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($expense->amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($expense->status == 'approved') bg-green-100 text-green-800
                                                @elseif($expense->status == 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($expense->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expense->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Belum ada pengeluaran yang dibuat.</p>
                    @endif
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

                <!-- Aktivitas Terbaru -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Aktivitas Terbaru Saya</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($myActivities as $activity)
                                <div class="text-sm">
                                    <span class="text-gray-600">{{ $activity->description }}</span>
                                    <div class="text-xs text-gray-500">{{ $activity->project_name }} - {{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}</div>
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
                        <a href="{{ route('expenses.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Buat Pengeluaran
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Pengeluaran
                        </a>
                        <a href="{{ route('projects.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Proyek
                        </a>
                        <a href="{{ route('timelines.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Timeline
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
