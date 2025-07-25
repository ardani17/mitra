@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Project Timelines</h1>
        <a href="{{ route('timelines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Buat Progres Baru</span>
            <span class="sm:hidden">+ Progres</span>
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('timelines.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_id" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Proyek</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ Str::limit($project->name, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>Terlambat</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm flex-1 sm:flex-none">
                    <span class="hidden sm:inline">Filter</span>
                    <svg class="w-4 h-4 sm:hidden mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                    </svg>
                </button>
                <a href="{{ route('timelines.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-sm flex-1 sm:flex-none text-center">
                    <span class="hidden sm:inline">Reset</span>
                    <svg class="w-4 h-4 sm:hidden mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Timelines Display -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($timelines as $timeline)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $timeline->project->name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($timeline->project->user->company_name ?? 'N/A', 30) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $timeline->milestone }}</div>
                            @if($timeline->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($timeline->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Planned: {{ $timeline->planned_date->format('d M Y') }}</div>
                            @if($timeline->actual_date)
                            <div class="text-sm text-gray-500">Actual: {{ $timeline->actual_date->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if($timeline->status == 'completed') bg-green-100 text-green-800
                                  @elseif($timeline->status == 'in_progress') bg-blue-100 text-blue-800
                                  @elseif($timeline->status == 'delayed') bg-red-100 text-red-800
                                  @elseif($timeline->status == 'planned') bg-gray-100 text-gray-800
                                  @else bg-purple-100 text-purple-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $timeline->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $timeline->progress_percentage >= 90 ? 'green' : ($timeline->progress_percentage >= 50 ? 'blue' : 'yellow') }}-600 h-2 rounded-full" 
                                         style="width: {{ $timeline->progress_percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 ml-2">{{ $timeline->progress_percentage }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('timelines.show', $timeline) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route('timelines.edit', $timeline) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('timelines.destroy', $timeline) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Are you sure you want to delete this timeline progres?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada progres timeline ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse($timelines as $timeline)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
                <!-- Timeline Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 truncate" title="{{ $timeline->project->name }}">
                            {{ Str::limit($timeline->project->name, 35) }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($timeline->project->user->company_name ?? 'N/A', 25) }}</p>
                    </div>
                    <div class="ml-3 flex flex-col items-end space-y-1">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                              @if($timeline->status == 'completed') bg-green-100 text-green-800
                              @elseif($timeline->status == 'in_progress') bg-blue-100 text-blue-800
                              @elseif($timeline->status == 'delayed') bg-red-100 text-red-800
                              @elseif($timeline->status == 'planned') bg-gray-100 text-gray-800
                              @else bg-purple-100 text-purple-800 @endif">
                            @if($timeline->status == 'planned') Direncanakan
                            @elseif($timeline->status == 'in_progress') Berjalan
                            @elseif($timeline->status == 'completed') Selesai
                            @elseif($timeline->status == 'delayed') Terlambat
                            @else {{ ucfirst($timeline->status) }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Milestone Info -->
                <div class="mb-3">
                    <label class="text-xs font-medium text-gray-500">Milestone</label>
                    <div class="mt-1">
                        <div class="text-sm font-medium text-gray-900">{{ $timeline->milestone }}</div>
                        @if($timeline->description)
                        <div class="text-xs text-gray-500 mt-1">{{ Str::limit($timeline->description, 60) }}</div>
                        @endif
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-medium text-gray-500">Progress</label>
                        <span class="text-sm font-semibold text-gray-900">{{ $timeline->progress_percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all duration-300 
                                  @if($timeline->progress_percentage >= 90) bg-green-500
                                  @elseif($timeline->progress_percentage >= 50) bg-blue-500
                                  @else bg-yellow-500 @endif" 
                             style="width: {{ $timeline->progress_percentage }}%"></div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Tanggal Rencana</label>
                        <div class="text-sm text-gray-900 mt-1">{{ $timeline->planned_date->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Tanggal Aktual</label>
                        <div class="text-sm text-gray-900 mt-1">
                            @if($timeline->actual_date)
                                {{ $timeline->actual_date->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2 pt-3 border-t border-gray-100">
                    <a href="{{ route('timelines.show', $timeline) }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                        Lihat
                    </a>
                    <a href="{{ route('timelines.edit', $timeline) }}" 
                       class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200">
                        Edit
                    </a>
                    <form action="{{ route('timelines.destroy', $timeline) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors duration-200"
                                onclick="return confirm('Yakin ingin menghapus progres timeline ini?')">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm">Tidak ada progres timeline ditemukan.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-t border-blue-200 sm:px-6">
            <div class="pagination-wrapper">
                {{ $timelines->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
