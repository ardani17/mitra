<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Project Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistik Proyek -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Proyek</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $totalProjects }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Proyek Aktif</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $myActiveProjects }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Proyek Selesai</h3>
                        <p class="text-3xl font-bold text-purple-600">{{ $completedProjects }}</p>
                    </div>
                </div>
            </div>

            <!-- Budget Tracking -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Budget</h3>
                        <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalBudget) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Total Pengeluaran</h3>
                        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpenses) }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-700">Utilisasi Budget</h3>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($budgetUtilization, 1) }}%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                            <div class="bg-orange-600 h-2.5 rounded-full" style="width: {{ min($budgetUtilization, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts dan Alerts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

                <!-- Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Alerts</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span>Pengeluaran Pending</span>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-semibold">
                                    {{ $pendingExpenses }}
                                </span>
                            </div>
                            @if(isset($urgentProjects) && $urgentProjects->count() > 0)
                                <div class="bg-red-50 border border-red-200 rounded p-3">
                                    <h4 class="font-semibold text-red-800">Proyek Overdue</h4>
                                    <p class="text-sm text-red-600">{{ $urgentProjects->count() }} proyek melewati deadline</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proyek yang Perlu Perhatian -->
            @if(isset($urgentProjects) && $urgentProjects->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Proyek yang Perlu Perhatian</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Proyek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($urgentProjects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $project->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $project->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->end_date ? $project->end_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('projects.show', $project->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('projects.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Proyek
                        </a>
                        <a href="{{ route('projects.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Buat Proyek
                        </a>
                        <a href="{{ route('expenses.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lihat Pengeluaran
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
