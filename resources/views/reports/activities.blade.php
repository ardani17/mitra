@extends('layouts.app')

@section('title', 'Laporan Aktivitas')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Laporan Aktivitas</h1>
    </div>

    <!-- Filter Section -->
    <div class="card p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('reports.activities') }}" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari aktivitas..."
                           class="form-input text-sm">
                </div>

                <!-- Project Filter -->
                <div>
                    <label for="project_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Proyek</label>
                    <select name="project_id" id="project_id" class="form-select text-sm">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->code }} - {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Activity Type Filter -->
                <div>
                    <label for="activity_type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tipe Aktivitas</label>
                    <select name="activity_type" id="activity_type" class="form-select text-sm">
                        <option value="">Semua Tipe</option>
                        @foreach($activityTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('activity_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label for="user_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" id="user_id" class="form-select text-sm">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="form-input text-sm">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                    <input type="date"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="form-input text-sm">
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Urutkan Berdasarkan</label>
                    <select name="sort_by" id="sort_by" class="form-select text-sm">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal</option>
                        <option value="activity_type" {{ request('sort_by') == 'activity_type' ? 'selected' : '' }}>Tipe Aktivitas</option>
                        <option value="description" {{ request('sort_by') == 'description' ? 'selected' : '' }}>Deskripsi</option>
                    </select>
                </div>

                <!-- Sort Direction -->
                <div>
                    <label for="sort_direction" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Arah Urutan</label>
                    <select name="sort_direction" id="sort_direction" class="form-select text-sm">
                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button type="submit" class="btn-primary text-sm sm:text-base py-2 px-3 sm:px-4">
                    <i class="fas fa-search mr-1 sm:mr-2"></i>Filter
                </button>
                <a href="{{ route('reports.activities') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
                    <i class="fas fa-times mr-1 sm:mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Activities List -->
    <div class="card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                Daftar Aktivitas ({{ $activities->total() }} total)
            </h2>
        </div>

        @if($activities->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Proyek
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipe Aktivitas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deskripsi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>
                                        <div class="font-medium">{{ $activity->created_at->format('d/m/Y') }}</div>
                                        <div class="text-gray-500">{{ $activity->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($activity->project)
                                        <div>
                                            <div class="font-medium">{{ $activity->project->code }}</div>
                                            <div class="text-gray-500">{{ Str::limit($activity->project->name, 30) }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'project_created' => 'bg-green-100 text-green-800',
                                            'project_updated' => 'bg-blue-100 text-blue-800',
                                            'expense_created' => 'bg-yellow-100 text-yellow-800',
                                            'expense_approval' => 'bg-purple-100 text-purple-800',
                                            'billing_created' => 'bg-indigo-100 text-indigo-800',
                                            'billing_batch_created' => 'bg-indigo-100 text-indigo-800',
                                            'billing_status_changed' => 'bg-orange-100 text-orange-800',
                                            'document_uploaded' => 'bg-gray-100 text-gray-800',
                                            'timeline_created' => 'bg-teal-100 text-teal-800',
                                            'timeline_updated' => 'bg-teal-100 text-teal-800',
                                            'data_import' => 'bg-pink-100 text-pink-800',
                                            'data_export' => 'bg-pink-100 text-pink-800',
                                            'profit_analysis_updated' => 'bg-red-100 text-red-800'
                                        ];
                                        $colorClass = $typeColors[$activity->activity_type] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                        {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        {{ Str::limit($activity->description, 80) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($activity->user)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ substr($activity->user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('reports.activities.show', $activity->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden">
                @foreach($activities as $activity)
                <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
                    <!-- Activity Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                @php
                                    $typeColors = [
                                        'project_created' => 'bg-green-100 text-green-800',
                                        'project_updated' => 'bg-blue-100 text-blue-800',
                                        'expense_created' => 'bg-yellow-100 text-yellow-800',
                                        'expense_approval' => 'bg-purple-100 text-purple-800',
                                        'billing_created' => 'bg-indigo-100 text-indigo-800',
                                        'billing_batch_created' => 'bg-indigo-100 text-indigo-800',
                                        'billing_status_changed' => 'bg-orange-100 text-orange-800',
                                        'document_uploaded' => 'bg-gray-100 text-gray-800',
                                        'timeline_created' => 'bg-teal-100 text-teal-800',
                                        'timeline_updated' => 'bg-teal-100 text-teal-800',
                                        'data_import' => 'bg-pink-100 text-pink-800',
                                        'data_export' => 'bg-pink-100 text-pink-800',
                                        'profit_analysis_updated' => 'bg-red-100 text-red-800'
                                    ];
                                    $colorClass = $typeColors[$activity->activity_type] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorClass }}">
                                    {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-900 break-words">{{ Str::limit($activity->description, 60) }}</p>
                        </div>
                        <div class="ml-3 text-right">
                            <div class="text-xs text-gray-500">{{ $activity->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $activity->created_at->format('H:i') }}</div>
                        </div>
                    </div>

                    <!-- Activity Details -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Proyek</label>
                            @if($activity->project)
                                <div class="text-sm text-gray-900 mt-1">
                                    <div class="font-medium">{{ $activity->project->code }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ Str::limit($activity->project->name, 20) }}</div>
                                </div>
                            @else
                                <div class="text-sm text-gray-400 mt-1">-</div>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">User</label>
                            @if($activity->user)
                                <div class="text-sm text-gray-900 mt-1">
                                    <div class="font-medium">{{ Str::limit($activity->user->name, 15) }}</div>
                                </div>
                            @else
                                <div class="text-sm text-gray-400 mt-1">-</div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex justify-end">
                        <a href="{{ route('reports.activities.show', $activity->id) }}"
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
                {{ $activities->links() }}
            </div>
        @else
            <div class="px-4 sm:px-6 py-8 sm:py-12 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-history text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                    <p class="text-base sm:text-lg">Tidak ada aktivitas yang ditemukan</p>
                    <p class="text-sm">Coba ubah filter pencarian Anda</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filter changes
    const filterInputs = document.querySelectorAll('#project_id, #activity_type, #user_id, #sort_by, #sort_direction');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
