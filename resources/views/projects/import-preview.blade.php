@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Preview Import Proyek</h1>
        </div>
        <a href="{{ route('projects.import') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    
                    <!-- Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">Data Valid</p>
                                    <p class="text-2xl font-bold text-green-900">{{ count($validData) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">Data Error</p>
                                    <p class="text-2xl font-bold text-red-900">{{ count($invalidData) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800">Total Data</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ count($validData) + count($invalidData) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Options -->
                    @if(count($validData) > 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4">Opsi Import</h3>
                        
                        <form action="{{ route('projects.import.confirm') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="filename" value="{{ $filename }}">
                            
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="import_valid_only" value="1" checked class="form-radio text-blue-600">
                                    <span class="ml-2 text-blue-700">Import hanya data yang valid ({{ count($validData) }} data)</span>
                                </label>
                            </div>
                            
                            @if(count($invalidData) > 0)
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="import_valid_only" value="0" class="form-radio text-blue-600">
                                    <span class="ml-2 text-blue-700">Coba import semua data (data error akan dilewati)</span>
                                </label>
                            </div>
                            @endif
                            
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Konfirmasi Import
                                </button>
                                
                                <a href="{{ route('projects.import') }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                    @endif

                    <!-- Valid Data Preview -->
                    @if(count($validData) > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-green-800 mb-4">Data Valid ({{ count($validData) }} baris)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Proyek</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nilai Jasa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($validData as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['row_number'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item['data']['nama_proyek'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($item['data']['tipe_proyek'] == 'konstruksi') bg-blue-100 text-blue-800
                                                @elseif($item['data']['tipe_proyek'] == 'maintenance') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($item['data']['tipe_proyek']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($item['data']['status'] == 'planning') bg-blue-100 text-blue-800
                                                @elseif($item['data']['status'] == 'in_progress') bg-yellow-100 text-yellow-800
                                                @elseif($item['data']['status'] == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $item['data']['status'])) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($item['data']['prioritas'] == 'urgent') bg-red-100 text-red-800
                                                @elseif($item['data']['prioritas'] == 'high') bg-orange-100 text-orange-800
                                                @elseif($item['data']['prioritas'] == 'medium') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800 @endif">
                                                {{ ucfirst($item['data']['prioritas']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item['data']['lokasi'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($item['data']['nilai_jasa_plan'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

        <!-- Invalid Data Preview -->
        @if(count($invalidData) > 0)
        <div class="mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-red-800 mb-4">Data Error ({{ count($invalidData) }} baris)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Proyek</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Data Asli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($invalidData as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-900">{{ $item['row_number'] }}</td>
                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $item['data']['nama_proyek'] ?: '-' }}">
                                    {{ $item['data']['nama_proyek'] ?: '-' }}
                                </div>
                            </td>
                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm">
                                <ul class="list-disc list-inside text-red-600 space-y-1">
                                    @foreach($item['errors'] as $error)
                                    <li class="text-xs">{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <!-- Mobile data preview -->
                                <div class="sm:hidden mt-2">
                                    <details class="cursor-pointer">
                                        <summary class="text-blue-600 hover:text-blue-800 text-xs">Data asli</summary>
                                        <div class="mt-1 p-2 bg-gray-100 rounded text-xs">
                                            @foreach($item['original_row'] as $key => $value)
                                                <div><strong>{{ $key }}:</strong> {{ $value ?: 'null' }}</div>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            </td>
                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-500 hidden sm:table-cell">
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 hover:text-blue-800">Lihat data asli</summary>
                                    <div class="mt-2 p-2 bg-gray-100 rounded text-xs">
                                        @foreach($item['original_row'] as $key => $value)
                                            <div><strong>{{ $key }}:</strong> {{ $value ?: 'null' }}</div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(count($validData) == 0 && count($invalidData) == 0)
        <div class="text-center py-6 sm:py-8">
            <svg class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm sm:text-base font-medium text-gray-900">Tidak ada data ditemukan</h3>
            <p class="mt-1 text-xs sm:text-sm text-gray-500">File Excel tidak mengandung data yang dapat diproses.</p>
        </div>
        @endif
    </div>
</div>
@endsection
