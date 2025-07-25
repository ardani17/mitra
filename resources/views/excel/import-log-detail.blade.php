@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Detail Log Import</h2>
                    <a href="{{ route('excel.import-logs') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Log Import
                    </a>
                </div>

                <!-- Import Information -->
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Import</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama File</label>
                            <p class="text-sm text-gray-900">{{ $log->filename }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipe Data</label>
                            @php
                                $typeLabels = [
                                    'projects' => 'Proyek',
                                    'expenses' => 'Pengeluaran',
                                    'billings' => 'Tagihan',
                                    'timelines' => 'Timeline'
                                ];
                            @endphp
                            <p class="text-sm text-gray-900">{{ $typeLabels[$log->type] ?? ucfirst($log->type) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">User</label>
                            <p class="text-sm text-gray-900">{{ $log->user->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Import</label>
                            <p class="text-sm text-gray-900">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
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
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hasil Import</label>
                            <div class="text-sm">
                                @if($log->success_count)
                                    <span class="text-green-600">✓ {{ $log->success_count }} data berhasil diimport</span>
                                @endif
                                @if($log->error_count)
                                    <br><span class="text-red-600">✗ {{ $log->error_count }} data gagal diimport</span>
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
                    <div class="bg-red-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-red-800 mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Detail Error ({{ count($errors) }})
                        </h3>
                        
                        <div class="max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach($errors as $index => $error)
                                    <div class="bg-white p-3 rounded border-l-4 border-red-400">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-red-700">{{ $error }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Error Summary -->
                        <div class="mt-4 p-4 bg-red-100 rounded">
                            <h4 class="font-semibold text-red-800 mb-2">Tips Mengatasi Error:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
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
                        <div class="bg-green-50 p-6 rounded-lg text-center">
                            <i class="fas fa-check-circle text-6xl text-green-400 mb-4"></i>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Import Berhasil!</h3>
                            <p class="text-green-700">Semua data berhasil diimport tanpa error.</p>
                        </div>
                    @elseif($log->status === 'processing')
                        <div class="bg-yellow-50 p-6 rounded-lg text-center">
                            <i class="fas fa-spinner fa-spin text-6xl text-yellow-400 mb-4"></i>
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Sedang Memproses...</h3>
                            <p class="text-yellow-700">Import sedang dalam proses. Silakan refresh halaman ini.</p>
                        </div>
                    @endif
                @endif

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-between">
                    <a href="{{ route('excel.import-logs') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-list mr-2"></i>Lihat Semua Log
                    </a>
                    
                    @if($log->status === 'completed_with_errors' || $log->status === 'failed')
                        <a href="{{ route('excel.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-redo mr-2"></i>Coba Import Lagi
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
