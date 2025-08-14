@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-3 sm:space-y-0">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Detail Progres Timeline</h1>
        <div class="flex space-x-2">
            <a href="{{ route('timelines.edit', $timeline) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                Edit Progres
            </a>
        </div>
    </div>

    <!-- Timeline Overview -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-6 space-y-3 sm:space-y-0">
            <div class="flex-1">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">{{ $timeline->milestone }}</h2>
                <p class="text-sm sm:text-base text-gray-600 mt-2">Project: {{ $timeline->project->name }}</p>
                <p class="text-sm sm:text-base text-gray-600">Company: {{ $timeline->project->user->company_name ?? 'N/A' }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="px-2 sm:px-3 py-1 text-xs sm:text-sm font-semibold rounded-full
                      @if($timeline->status == 'completed') bg-green-100 text-green-800
                      @elseif($timeline->status == 'in_progress') bg-blue-100 text-blue-800
                      @elseif($timeline->status == 'delayed') bg-red-100 text-red-800
                      @elseif($timeline->status == 'planned') bg-gray-100 text-gray-800
                      @else bg-purple-100 text-purple-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $timeline->status)) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Informasi Progres</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Description</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $timeline->description ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Progress</label>
                        <div class="mt-1">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $timeline->progress_percentage >= 90 ? 'green' : ($timeline->progress_percentage >= 50 ? 'blue' : 'yellow') }}-600 h-2 rounded-full"
                                         style="width: {{ $timeline->progress_percentage }}%"></div>
                                </div>
                                <span class="text-xs sm:text-sm text-gray-600 ml-2">{{ $timeline->progress_percentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Timeline Dates</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Planned Date</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $timeline->planned_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Actual Date</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $timeline->actual_date ? $timeline->actual_date->format('d M Y') : '-' }}</div>
                    </div>
                    @if($timeline->actual_date && $timeline->planned_date)
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Days Difference</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">
                            @php
                                $daysDiff = $timeline->planned_date->diffInDays($timeline->actual_date, false);
                                $daysText = $daysDiff > 0 ? '+' . $daysDiff . ' days' : $daysDiff . ' days';
                                $colorClass = $daysDiff > 0 ? 'text-red-600' : ($daysDiff < 0 ? 'text-green-600' : 'text-gray-600');
                            @endphp
                            <span class="{{ $colorClass }}">{{ $daysText }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Project Status</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Project</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $timeline->project->name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Project Status</label>
                        <div class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                  @if($timeline->project->status == 'completed') bg-green-100 text-green-800
                                  @elseif($timeline->project->status == 'on_progress') bg-blue-100 text-blue-800
                                  @elseif($timeline->project->status == 'on_hold') bg-yellow-100 text-yellow-800
                                  @elseif($timeline->project->status == 'draft') bg-gray-100 text-gray-800
                                  @else bg-purple-100 text-purple-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $timeline->project->status)) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Total Progres</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $timeline->project->timelines()->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                <div>
                    <span class="text-xs sm:text-sm text-gray-600">Created:</span>
                    <span class="text-xs sm:text-sm font-medium text-gray-900">{{ $timeline->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-xs sm:text-sm text-gray-600">Last Updated:</span>
                    <span class="text-xs sm:text-sm font-medium text-gray-900">{{ $timeline->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <form action="{{ route('timelines.update-status', $timeline) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Update Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                    <option value="planned" {{ $timeline->status == 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="in_progress" {{ $timeline->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $timeline->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="delayed" {{ $timeline->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Progress (%)</label>
                <input type="number" name="progress_percentage" value="{{ $timeline->progress_percentage }}" min="0" max="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-xs sm:text-sm">
                    Update Status
                </button>
            </div>
        </form>
    </div>

    <!-- Related Activities -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Related Activities</h2>
        <div class="space-y-4">
            @forelse($timeline->project->activities()->where('description', 'like', '%' . $timeline->milestone . '%')->latest()->limit(5)->get() as $activity)
            <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-2 sm:space-y-0">
                    <div class="flex-1">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-900">{{ $activity->description }}</h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">By {{ $activity->user->name }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $activity->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($activity->changes)
                <div class="mt-2 text-xs text-gray-600">
                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($activity->changes, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
            @empty
            <p class="text-xs sm:text-sm text-gray-500">Tidak ada aktivitas ditemukan untuk progres ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
