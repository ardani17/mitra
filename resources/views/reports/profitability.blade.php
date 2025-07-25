@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Analisis Profitabilitas Proyek</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali ke Laporan
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @php
            $totalProjects = $projects->total();
            $profitableProjects = $projects->where('profit_margin', '>', 0)->count();
            $lossProjects = $projects->where('profit_margin', '<', 0)->count();
            $avgProfitMargin = $projects->avg('profit_margin');
        @endphp
        
        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Proyek</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalProjects }}</p>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Proyek Profit</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $profitableProjects }}</p>
                    <p class="text-sm text-gray-500">{{ $totalProjects > 0 ? number_format(($profitableProjects / $totalProjects) * 100, 1) : 0 }}%</p>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Proyek Rugi</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $lossProjects }}</p>
                    <p class="text-sm text-gray-500">{{ $totalProjects > 0 ? number_format(($lossProjects / $totalProjects) * 100, 1) : 0 }}%</p>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $avgProfitMargin >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata Margin</p>
                    <p class="text-2xl font-semibold {{ $avgProfitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($avgProfitMargin, 2) }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profitability Analysis Table -->
    <div class="card p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Detail Profitabilitas Proyek</h3>
            <div class="text-sm text-gray-600">
                Diurutkan berdasarkan profit margin tertinggi
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit Margin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                            <div class="text-sm text-gray-500">{{ $project->code }}</div>
                            <div class="text-xs text-gray-400">{{ $project->type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if($project->status == 'completed') bg-green-100 text-green-800
                                  @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                                  @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                @if($project->status == 'planning') Perencanaan
                                @elseif($project->status == 'in_progress') Sedang Berjalan
                                @elseif($project->status == 'completed') Selesai
                                @elseif($project->status == 'cancelled') Dibatalkan
                                @else {{ ucfirst($project->status) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($project->planned_total_value ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">Rp {{ number_format($project->total_revenue, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">Rp {{ number_format($project->total_expenses, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $project->net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <div class="font-semibold">Rp {{ number_format($project->net_profit, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-semibold {{ $project->profit_margin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($project->profit_margin, 2) }}%
                                </div>
                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $project->profit_margin >= 0 ? 'bg-green-500' : 'bg-red-500' }}" 
                                         style="width: {{ min(abs($project->profit_margin), 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $performance = '';
                                $performanceClass = '';
                                if ($project->profit_margin >= 20) {
                                    $performance = 'Excellent';
                                    $performanceClass = 'bg-green-100 text-green-800';
                                } elseif ($project->profit_margin >= 10) {
                                    $performance = 'Good';
                                    $performanceClass = 'bg-blue-100 text-blue-800';
                                } elseif ($project->profit_margin >= 0) {
                                    $performance = 'Fair';
                                    $performanceClass = 'bg-yellow-100 text-yellow-800';
                                } else {
                                    $performance = 'Poor';
                                    $performanceClass = 'bg-red-100 text-red-800';
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $performanceClass }}">
                                {{ $performance }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data proyek ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Top Performers -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Top Performers (Margin > 20%)</h3>
            <div class="space-y-3">
                @php
                    $topPerformers = $projects->where('profit_margin', '>', 20)->take(5);
                @endphp
                @forelse($topPerformers as $project)
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                        <div class="text-xs text-gray-500">{{ $project->code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-green-600">{{ number_format($project->profit_margin, 2) }}%</div>
                        <div class="text-xs text-gray-500">Rp {{ number_format($project->net_profit, 0, ',', '.') }}</div>
                    </div>
                </div>
                @empty
                <div class="text-sm text-gray-500 text-center py-4">
                    Tidak ada proyek dengan margin > 20%
                </div>
                @endforelse
            </div>
        </div>

        <!-- Poor Performers -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Perlu Perhatian (Margin < 0%)</h3>
            <div class="space-y-3">
                @php
                    $poorPerformers = $projects->where('profit_margin', '<', 0)->take(5);
                @endphp
                @forelse($poorPerformers as $project)
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                        <div class="text-xs text-gray-500">{{ $project->code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-red-600">{{ number_format($project->profit_margin, 2) }}%</div>
                        <div class="text-xs text-gray-500">Rp {{ number_format($project->net_profit, 0, ',', '.') }}</div>
                    </div>
                </div>
                @empty
                <div class="text-sm text-gray-500 text-center py-4">
                    Tidak ada proyek dengan margin negatif
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="card p-6 mt-8">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Rekomendasi Perbaikan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Optimasi Biaya</h4>
                <p class="text-sm text-blue-700">
                    Review pengeluaran pada proyek dengan margin rendah. Identifikasi area penghematan biaya tanpa mengurangi kualitas.
                </p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-2">Replikasi Sukses</h4>
                <p class="text-sm text-green-700">
                    Pelajari faktor-faktor yang membuat proyek top performer berhasil dan terapkan pada proyek lain.
                </p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-semibold text-yellow-800 mb-2">Pricing Strategy</h4>
                <p class="text-sm text-yellow-700">
                    Evaluasi strategi penetapan harga untuk memastikan margin profit yang sehat pada proyek mendatang.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
