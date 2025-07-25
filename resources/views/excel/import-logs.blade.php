@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Log Import Excel</h2>
                    <a href="{{ route('excel.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Export/Import
                    </a>
                </div>

                @if($logs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        File
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipe
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hasil
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->filename }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $typeLabels = [
                                                    'projects' => 'Proyek',
                                                    'expenses' => 'Pengeluaran',
                                                    'billings' => 'Tagihan',
                                                    'timelines' => 'Timeline'
                                                ];
                                                $typeColors = [
                                                    'projects' => 'bg-blue-100 text-blue-800',
                                                    'expenses' => 'bg-yellow-100 text-yellow-800',
                                                    'billings' => 'bg-purple-100 text-purple-800',
                                                    'timelines' => 'bg-orange-100 text-orange-800'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeColors[$log->type] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $typeLabels[$log->type] ?? ucfirst($log->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->user->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'processing' => 'bg-yellow-100 text-yellow-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'completed_with_errors' => 'bg-orange-100 text-orange-800',
                                                    'failed' => 'bg-red-100 text-red-800'
                                                ];
                                                $statusLabels = [
                                                    'processing' => 'Memproses',
                                                    'completed' => 'Selesai',
                                                    'completed_with_errors' => 'Selesai dengan Error',
                                                    'failed' => 'Gagal'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$log->status] ?? ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($log->success_count || $log->error_count)
                                                <div class="text-xs">
                                                    @if($log->success_count)
                                                        <span class="text-green-600">✓ {{ $log->success_count }} berhasil</span>
                                                    @endif
                                                    @if($log->error_count)
                                                        <br><span class="text-red-600">✗ {{ $log->error_count }} error</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('excel.import-log-detail', $log->id) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-file-import text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Log Import</h3>
                        <p class="text-gray-500 mb-4">Log import akan muncul setelah Anda melakukan import data Excel.</p>
                        <a href="{{ route('excel.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-upload mr-2"></i>Mulai Import
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
