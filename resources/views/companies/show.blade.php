@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 break-words">{{ $company->name }}</h1>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <a href="{{ route('companies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Kembali ke Perusahaan</span>
                <span class="sm:hidden">Kembali</span>
            </a>
            <a href="{{ route('companies.edit', $company) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Edit Perusahaan</span>
                <span class="sm:hidden">Edit</span>
            </a>
        </div>
    </div>

    <!-- Company Details -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Perusahaan</h3>
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Nama</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900 break-words">{{ $company->name }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Email</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900 break-words">{{ $company->email ?? '-' }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Telepon</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $company->phone ?? '-' }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Kontak Person</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900 break-words">{{ $company->contact_person ?? '-' }}</div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Alamat</h3>
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Alamat Lengkap</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900 whitespace-pre-line break-words">{{ $company->address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                <div>
                    <span class="text-xs sm:text-sm text-gray-600">Dibuat:</span>
                    <span class="text-xs sm:text-sm font-medium text-gray-900">{{ $company->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-xs sm:text-sm text-gray-600">Terakhir Diperbarui:</span>
                    <span class="text-xs sm:text-sm font-medium text-gray-900">{{ $company->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Statistics -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4">Statistik Perusahaan</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                <div class="text-xs sm:text-sm text-blue-600">Total Proyek</div>
                <div class="text-lg sm:text-2xl font-bold text-blue-800">{{ $company->projects()->count() }}</div>
            </div>
            <div class="bg-green-50 p-3 sm:p-4 rounded-lg">
                <div class="text-xs sm:text-sm text-green-600">Proyek Aktif</div>
                <div class="text-lg sm:text-2xl font-bold text-green-800">{{ $company->projects()->whereIn('status', ['planning', 'on_progress'])->count() }}</div>
            </div>
            <div class="bg-yellow-50 p-3 sm:p-4 rounded-lg">
                <div class="text-xs sm:text-sm text-yellow-600">Proyek Selesai</div>
                <div class="text-lg sm:text-2xl font-bold text-yellow-800">{{ $company->projects()->where('status', 'completed')->count() }}</div>
            </div>
            <div class="bg-purple-50 p-3 sm:p-4 rounded-lg">
                <div class="text-xs sm:text-sm text-purple-600">Total Pendapatan</div>
                <div class="text-sm sm:text-2xl font-bold text-purple-800 break-words">Rp {{ number_format($company->projects()->with('revenues')->get()->sum(function($project) { return $project->revenues->sum('total_amount'); }), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button class="tab-button active text-blue-600 border-blue-600 px-3 sm:px-4 py-2 font-medium text-xs sm:text-sm border-b-2" data-tab="projects">
                    Proyek ({{ $company->projects()->count() }})
                </button>
            </nav>
        </div>

        <!-- Projects Tab -->
        <div class="tab-content active p-4 sm:p-6" id="projects-tab">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Proyek Perusahaan</h3>
                <a href="{{ route('projects.create') }}?company={{ $company->id }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-xs sm:text-sm text-center">
                    <span class="hidden sm:inline">Buat Proyek Baru</span>
                    <span class="sm:hidden">Tambah Proyek</span>
                </a>
            </div>
            
            @if($company->projects->count() > 0)
                <!-- Mobile Card View -->
                <div class="block sm:hidden space-y-4">
                    @foreach($company->projects as $project)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">{{ $project->name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($project->description, 60) }}</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2
                                  @if($project->type == 'fiber_optic') bg-blue-100 text-blue-800
                                  @elseif($project->type == 'pole_planting') bg-green-100 text-green-800
                                  @elseif($project->type == 'tower_installation') bg-purple-100 text-purple-800
                                  @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $project->type)) }}
                            </span>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Budget Rencana:</span>
                                <span class="text-sm text-gray-900 break-words">Rp {{ number_format($project->planned_budget, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Budget Aktual:</span>
                                <span class="text-sm text-gray-900 break-words">Rp {{ number_format($project->actual_budget, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Status:</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                      @if($project->status == 'completed') bg-green-100 text-green-800
                                      @elseif($project->status == 'on_progress') bg-blue-100 text-blue-800
                                      @elseif($project->status == 'on_hold') bg-yellow-100 text-yellow-800
                                      @elseif($project->status == 'draft') bg-gray-100 text-gray-800
                                      @else bg-purple-100 text-purple-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </div>
                            @if($project->start_date || $project->end_date)
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Tanggal:</span>
                                <div class="text-sm text-gray-900 text-right">
                                    @if($project->start_date)
                                        <div>Mulai: {{ $project->start_date->format('d M Y') }}</div>
                                    @endif
                                    @if($project->end_date)
                                        <div>Selesai: {{ $project->end_date->format('d M Y') }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="mt-3 pt-3 border-t flex space-x-2">
                            <a href="{{ route('projects.show', $project) }}"
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                Lihat
                            </a>
                            <a href="{{ route('projects.edit', $project) }}"
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                Edit
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($company->projects as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                          @if($project->type == 'fiber_optic') bg-blue-100 text-blue-800
                                          @elseif($project->type == 'pole_planting') bg-green-100 text-green-800
                                          @elseif($project->type == 'tower_installation') bg-purple-100 text-purple-800
                                          @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $project->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rencana: Rp {{ number_format($project->planned_budget, 0, ',', '.') }}</div>
                                    <div class="text-sm text-gray-500">Aktual: Rp {{ number_format($project->actual_budget, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                          @if($project->status == 'completed') bg-green-100 text-green-800
                                          @elseif($project->status == 'on_progress') bg-blue-100 text-blue-800
                                          @elseif($project->status == 'on_hold') bg-yellow-100 text-yellow-800
                                          @elseif($project->status == 'draft') bg-gray-100 text-gray-800
                                          @else bg-purple-100 text-purple-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($project->start_date)
                                        <div>Mulai: {{ $project->start_date->format('d M Y') }}</div>
                                    @endif
                                    @if($project->end_date)
                                        <div>Selesai: {{ $project->end_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-900 mr-3">Lihat</a>
                                    <a href="{{ route('projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 sm:py-12 px-4">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada proyek</h3>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">Belum ada proyek untuk perusahaan ini.</p>
                    <div class="mt-4">
                        <a href="{{ route('projects.create') }}?company={{ $company->id }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Buat Proyek Baru
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active', 'text-blue-600', 'border-blue-600'));
            tabButtons.forEach(btn => btn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'));
            tabContents.forEach(content => content.style.display = 'none');
            
            // Add active class to clicked button
            this.classList.add('active', 'text-blue-600', 'border-blue-600');
            this.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            
            // Show corresponding content
            const tabId = this.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).style.display = 'block';
        });
    });
});
</script>
@endsection
