@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Log Import</h2>
                    <a href="{{ route('excel.import-logs') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Log Import
                    </a>
                </div>

                <!-- Import Information -->
                <div class="card p-4 sm:p-6 mb-4 sm:mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Import</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Nama File</label>
                            <p class="text-xs sm:text-sm text-gray-900 break-words">{{ $log->filename }}</p>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Tipe Data</label>
                            @php
                                $typeLabels = [
                                    'projects' => 'Proyek',
                                    'expenses' => 'Pengeluaran',
                                    'billings' => 'Tagihan',
                                    'timelines' => 'Timeline'
                                ];
                            @endphp
                            <p class="text-xs sm:text-sm text-gray-900">{{ $typeLabels[$log->type] ?? ucfirst($log->type) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">User</label>
                            <p class="text-xs sm:text-sm text-gray-900">{{ $log->user->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Tanggal Import</label>
                            <p class="text-xs sm:text-sm text-gray-900">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Status</label>
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
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$log->status] ?? ucfirst($log->status) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Hasil Import</label>
                            <div class="text-xs sm:text-sm">
                                @if($log->success_count)
                                    <span class="text-green-600">✓ {{ $log->success_count }} data berhasil diimport</span>
                                @endif
                                @if($log->error_count)
                                    @if($log->success_count)<br>@endif
                                    <span class="text-red-600">✗ {{ $log->error_count }} data gagal diimport</span>
                                @endif
                                @if(!$log->success_count && !$log->error_count)
                                    <span class="text-gray-400">Tidak ada data yang diproses</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Details -->
                @if(!empty($errors))
                    <div class="bg-red-50 p-4 sm:p-6 rounded-lg">
                        <h3 class="text-base sm:text-lg font-semibold text-red-800 mb-3 sm:mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Detail Error ({{ count($errors) }})
                        </h3>
                        
                        <div class="max-h-64 sm:max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach($errors as $index => $error)
                                    <div class="bg-white p-3 rounded border-l-4 border-red-400">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="ml-2 sm:ml-3 flex-1">
                                                <p class="text-xs sm:text-sm text-red-700 break-words">{{ $error }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Error Summary -->
                        <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-red-100 rounded">
                            <h4 class="font-semibold text-red-800 mb-2 text-sm sm:text-base">Tips Mengatasi Error:</h4>
                            <ul class="text-xs sm:text-sm text-red-700 space-y-1">
                                <li>• Pastikan format tanggal menggunakan YYYY-MM-DD</li>
                                <li>• Periksa kembali kode proyek yang digunakan sudah ada di sistem</li>
                                <li>• Pastikan nilai numerik tidak menggunakan titik atau koma sebagai pemisah ribuan</li>
                                <li>• Hapus baris petunjuk pengisian sebelum melakukan import</li>
                                <li>• Pastikan kolom wajib sudah terisi dengan benar</li>
                            </ul>
                        </div>
                    </div>
                @else
                    @if($log->status === 'completed')
                        <div class="bg-green-50 p-4 sm:p-6 rounded-lg text-center">
                            <i class="fas fa-check-circle text-4xl sm:text-6xl text-green-400 mb-3 sm:mb-4"></i>
                            <h3 class="text-base sm:text-lg font-semibold text-green-800 mb-2">Import Berhasil!</h3>
                            <p class="text-sm sm:text-base text-green-700">Semua data berhasil diimport tanpa error.</p>
                        </div>
                    @elseif($log->status === 'processing')
                        <div class="bg-yellow-50 p-4 sm:p-6 rounded-lg text-center">
                            <i class="fas fa-spinner fa-spin text-4xl sm:text-6xl text-yellow-400 mb-3 sm:mb-4"></i>
                            <h3 class="text-base sm:text-lg font-semibold text-yellow-800 mb-2">Sedang Memproses...</h3>
                            <p class="text-sm sm:text-base text-yellow-700">Import sedang dalam proses. Silakan refresh halaman ini.</p>
                        </div>
                    @endif
                @endif

                <!-- Action Buttons -->
                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row sm:justify-between space-y-3 sm:space-y-0">
                    <a href="{{ route('excel.import-logs') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
                        <i class="fas fa-list mr-2"></i>Lihat Semua Log
                    </a>
                    
                    @if($log->status === 'completed_with_errors' || $log->status === 'failed')
                        <a href="{{ route('excel.index') }}" class="btn-primary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
                            <i class="fas fa-redo mr-2"></i>Coba Import Lagi
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
