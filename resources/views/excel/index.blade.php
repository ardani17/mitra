@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Export & Import Data Excel</h2>
                    <a href="{{ route('excel.import-logs') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-history mr-2"></i>Log Import
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        {{ session('warning') }}
                        @if(session('import_errors'))
                            <details class="mt-2">
                                <summary class="cursor-pointer font-semibold">Detail Error</summary>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach(session('import_errors') as $error)
                                        <li class="text-sm">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Export/Import Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button onclick="showTab('projects')" id="tab-projects" class="tab-button active border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600">
                                <i class="fas fa-project-diagram mr-2"></i>Proyek
                            </button>
                            <button onclick="showTab('expenses')" id="tab-expenses" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-money-bill-wave mr-2"></i>Pengeluaran
                            </button>
                            <button onclick="showTab('billings')" id="tab-billings" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-file-invoice mr-2"></i>Tagihan
                            </button>
                            <button onclick="showTab('timelines')" id="tab-timelines" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-calendar-alt mr-2"></i>Timeline
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Projects Tab -->
                <div id="content-projects" class="tab-content">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Export Section -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-800 mb-4">
                                <i class="fas fa-download mr-2"></i>Export Data Proyek
                            </h3>
                            <form action="{{ route('excel.export', 'projects') }}" method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                                    <select name="format" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="data">Data Lengkap</option>
                                        <option value="template">Template Kosong</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Proyek</label>
                                    <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Semua Proyek</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                                        <input type="date" name="date_from" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                                        <input type="date" name="date_to" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-download mr-2"></i>Export Proyek
                                </button>
                            </form>

                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <a href="{{ route('excel.template', 'projects') }}" class="w-full inline-block text-center bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2 px-4 rounded">
                                    <i class="fas fa-file-download mr-2"></i>Download Template
                                </a>
                            </div>
                        </div>

                        <!-- Import Section -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800 mb-4">
                                <i class="fas fa-upload mr-2"></i>Import Data Proyek
                            </h3>
                            <form action="{{ route('excel.import', 'projects') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                           class="w-full border-gray-300 rounded-md shadow-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max: 10MB)</p>
                                </div>

                                <div class="bg-green-100 p-4 rounded">
                                    <h4 class="font-semibold text-green-800 mb-2">Petunjuk Import:</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• Download template terlebih dahulu</li>
                                        <li>• Isi data sesuai format yang tersedia</li>
                                        <li>• Pastikan kode proyek unik</li>
                                        <li>• Hapus baris petunjuk sebelum import</li>
                                    </ul>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-upload mr-2"></i>Import Proyek
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Expenses Tab -->
                <div id="content-expenses" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Export Section -->
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                                <i class="fas fa-download mr-2"></i>Export Data Pengeluaran
                            </h3>
                            <form action="{{ route('excel.export', 'expenses') }}" method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                                    <select name="format" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="data">Data Lengkap</option>
                                        <option value="template">Template Kosong</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Proyek</label>
                                    <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Semua Proyek</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                                        <input type="date" name="date_from" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                                        <input type="date" name="date_to" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-download mr-2"></i>Export Pengeluaran
                                </button>
                            </form>

                            <div class="mt-4 pt-4 border-t border-yellow-200">
                                <a href="{{ route('excel.template', 'expenses') }}" class="w-full inline-block text-center bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold py-2 px-4 rounded">
                                    <i class="fas fa-file-download mr-2"></i>Download Template
                                </a>
                            </div>
                        </div>

                        <!-- Import Section -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800 mb-4">
                                <i class="fas fa-upload mr-2"></i>Import Data Pengeluaran
                            </h3>
                            <form action="{{ route('excel.import', 'expenses') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                           class="w-full border-gray-300 rounded-md shadow-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max: 10MB)</p>
                                </div>

                                <div class="bg-green-100 p-4 rounded">
                                    <h4 class="font-semibold text-green-800 mb-2">Petunjuk Import:</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• Kode proyek harus sudah ada di sistem</li>
                                        <li>• Format tanggal: YYYY-MM-DD</li>
                                        <li>• Jumlah dalam angka tanpa titik/koma</li>
                                        <li>• Status default: draft</li>
                                    </ul>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-upload mr-2"></i>Import Pengeluaran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Billings Tab -->
                <div id="content-billings" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Export Section -->
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-800 mb-4">
                                <i class="fas fa-download mr-2"></i>Export Data Tagihan
                            </h3>
                            <form action="{{ route('excel.export', 'billings') }}" method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                                    <select name="format" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="data">Data Lengkap</option>
                                        <option value="template">Template Kosong</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Proyek</label>
                                    <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Semua Proyek</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                                        <input type="date" name="date_from" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                                        <input type="date" name="date_to" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-download mr-2"></i>Export Tagihan
                                </button>
                            </form>

                            <div class="mt-4 pt-4 border-t border-purple-200">
                                <a href="{{ route('excel.template', 'billings') }}" class="w-full inline-block text-center bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-2 px-4 rounded">
                                    <i class="fas fa-file-download mr-2"></i>Download Template
                                </a>
                            </div>
                        </div>

                        <!-- Import Section -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800 mb-4">
                                <i class="fas fa-upload mr-2"></i>Import Data Tagihan
                            </h3>
                            <form action="{{ route('excel.import', 'billings') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                           class="w-full border-gray-300 rounded-md shadow-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max: 10MB)</p>
                                </div>

                                <div class="bg-green-100 p-4 rounded">
                                    <h4 class="font-semibold text-green-800 mb-2">Petunjuk Import:</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• Kode proyek harus sudah ada di sistem</li>
                                        <li>• Format tanggal: YYYY-MM-DD</li>
                                        <li>• Persentase dalam desimal (0.3 = 30%)</li>
                                        <li>• Status default: draft</li>
                                    </ul>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-upload mr-2"></i>Import Tagihan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Timelines Tab -->
                <div id="content-timelines" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Export Section -->
                        <div class="bg-orange-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-orange-800 mb-4">
                                <i class="fas fa-download mr-2"></i>Export Data Timeline
                            </h3>
                            <form action="{{ route('excel.export', 'timelines') }}" method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                                    <select name="format" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="data">Data Lengkap</option>
                                        <option value="template">Template Kosong</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Proyek</label>
                                    <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Semua Proyek</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                                        <input type="date" name="date_from" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                                        <input type="date" name="date_to" class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-download mr-2"></i>Export Timeline
                                </button>
                            </form>

                            <div class="mt-4 pt-4 border-t border-orange-200">
                                <a href="{{ route('excel.template', 'timelines') }}" class="w-full inline-block text-center bg-orange-100 hover:bg-orange-200 text-orange-800 font-bold py-2 px-4 rounded">
                                    <i class="fas fa-file-download mr-2"></i>Download Template
                                </a>
                            </div>
                        </div>

                        <!-- Import Section -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800 mb-4">
                                <i class="fas fa-upload mr-2"></i>Import Data Timeline
                            </h3>
                            <form action="{{ route('excel.import', 'timelines') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                           class="w-full border-gray-300 rounded-md shadow-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max: 10MB)</p>
                                </div>

                                <div class="bg-green-100 p-4 rounded">
                                    <h4 class="font-semibold text-green-800 mb-2">Petunjuk Import:</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• Kode proyek harus sudah ada di sistem</li>
                                        <li>• Milestone wajib diisi</li>
                                        <li>• Format tanggal: YYYY-MM-DD</li>
                                        <li>• Progress dalam angka 0-100</li>
                                    </ul>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-upload mr-2"></i>Import Timeline
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection
