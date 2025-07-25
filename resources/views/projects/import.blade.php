<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Proyek') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.template') }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                </a>
                <a href="{{ route('projects.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Alert Messages -->
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('import_errors'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <h4 class="font-bold mb-2">Detail Error Import:</h4>
                            <ul class="list-disc list-inside">
                                @foreach (session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3">Petunjuk Import Proyek</h3>
                        <div class="text-blue-700 space-y-2">
                            <p>1. <strong>Download template Excel</strong> terlebih dahulu dengan klik tombol "Download Template" di atas</p>
                            <p>2. <strong>Isi data proyek</strong> sesuai dengan format yang tersedia di template</p>
                            <p>3. <strong>Pastikan format data benar:</strong></p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li>Tanggal dalam format: YYYY-MM-DD (contoh: 2025-08-01)</li>
                                <li>Nilai dalam angka tanpa titik atau koma (contoh: 50000000)</li>
                                <li>Tipe proyek: fiber_optic, tower_installation, maintenance, upgrade, other</li>
                                <li>Status: draft, planning, in_progress, on_hold, completed, cancelled</li>
                                <li>Prioritas: low, medium, high, urgent</li>
                            </ul>
                            <p>4. <strong>Upload file Excel</strong> yang sudah diisi menggunakan form di bawah</p>
                        </div>
                    </div>

                    <!-- Import Form -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload File Excel</h3>
                        
                        <form action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih File Excel (.xlsx, .xls, .csv)
                                </label>
                                <input type="file" 
                                       name="file" 
                                       id="file" 
                                       accept=".xlsx,.xls,.csv"
                                       required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('file')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <div class="text-sm text-gray-600">
                                    <p>Maksimal ukuran file: 2MB</p>
                                    <p>Format yang didukung: Excel (.xlsx, .xls) dan CSV (.csv)</p>
                                </div>
                                
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Import Proyek
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sample Data Preview -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Contoh Format Data</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">nama_proyek</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">tipe</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">prioritas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">nilai_jasa_plan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-4 py-2 text-sm">Proyek Fiber Optic Jakarta</td>
                                        <td class="px-4 py-2 text-sm">fiber_optic</td>
                                        <td class="px-4 py-2 text-sm">planning</td>
                                        <td class="px-4 py-2 text-sm">high</td>
                                        <td class="px-4 py-2 text-sm">50000000</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-sm">Maintenance Tower Bekasi</td>
                                        <td class="px-4 py-2 text-sm">maintenance</td>
                                        <td class="px-4 py-2 text-sm">draft</td>
                                        <td class="px-4 py-2 text-sm">medium</td>
                                        <td class="px-4 py-2 text-sm">15000000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
