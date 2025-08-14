@extends('layouts.app')

@section('title', 'Detail Aktivitas')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detail Aktivitas</h1>
        <a href="{{ route('reports.activities') }}" class="btn-secondary text-sm sm:text-base py-2 px-3 sm:px-4 text-center">
            <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Kembali
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Informasi Aktivitas</h2>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Basic Information -->
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Waktu</label>
                        <div class="text-sm text-gray-900">
                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                            <span class="text-gray-500 block sm:inline">({{ $activity->created_at->diffForHumans() }})</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tipe Aktivitas</label>
                        @php
                            $activityTypes = [
                                'project_created' => 'Proyek Dibuat',
                                'project_updated' => 'Proyek Diperbarui',
                                'expense_created' => 'Pengeluaran Dibuat',
                                'expense_approval' => 'Approval Pengeluaran',
                                'billing_created' => 'Invoice Dibuat',
                                'billing_batch_created' => 'Batch Billing Dibuat',
                                'billing_status_changed' => 'Status Invoice Berubah',
                                'document_uploaded' => 'Dokumen Diunggah',
                                'timeline_created' => 'Timeline Dibuat',
                                'timeline_updated' => 'Timeline Diperbarui',
                                'data_import' => 'Import Data',
                                'data_export' => 'Export Data',
                                'profit_analysis_updated' => 'Analisis Profit Diperbarui'
                            ];
                            
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
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $colorClass }}">
                            {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">User</label>
                        @if($activity->user)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                    <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm sm:text-lg font-medium text-gray-700">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $activity->user->name }}</div>
                                    <div class="text-xs sm:text-sm text-gray-500 truncate">{{ $activity->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400">User tidak ditemukan</span>
                        @endif
                    </div>
                </div>

                <!-- Project Information -->
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Proyek</label>
                        @if($activity->project)
                            <div class="border rounded-lg p-3 sm:p-4 bg-gray-50">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-2 sm:space-y-0">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-base sm:text-lg font-medium text-gray-900 break-words">{{ $activity->project->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $activity->project->code }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500 mt-1 break-words">{{ Str::limit($activity->project->description, 100) }}</p>
                                    </div>
                                    <a href="{{ route('projects.show', $activity->project->id) }}"
                                       class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm whitespace-nowrap">
                                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Proyek
                                    </a>
                                </div>
                                
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                                    <div>
                                        <span class="text-gray-500">Status:</span>
                                        @php
                                            $statusLabels = [
                                                'planning' => 'Perencanaan',
                                                'in_progress' => 'Sedang Berjalan',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan'
                                            ];
                                            $statusColors = [
                                                'planning' => 'bg-yellow-100 text-yellow-800',
                                                'in_progress' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $statusColors[$activity->project->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$activity->project->status] ?? $activity->project->status }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Budget:</span>
                                        <span class="ml-2 font-medium">Rp {{ number_format($activity->project->planned_budget ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400">Tidak terkait dengan proyek tertentu</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-4 sm:mt-6">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                    <p class="text-sm sm:text-base text-gray-900 break-words">{{ $activity->description }}</p>
                </div>
            </div>

            <!-- Changes (if available) -->
            @if($activity->changes && is_array($activity->changes) && count($activity->changes) > 0)
                <div class="mt-4 sm:mt-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Detail Perubahan</label>
                    <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                        <div class="space-y-2 sm:space-y-3">
                            @foreach($activity->changes as $key => $value)
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-2 border-b border-gray-200 last:border-b-0 space-y-1 sm:space-y-0">
                                    <span class="text-xs sm:text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}:
                                    </span>
                                    <span class="text-xs sm:text-sm text-gray-900 break-words">
                                        @if(is_array($value))
                                            {{ json_encode($value) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Metadata</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-gray-500">ID Aktivitas:</span>
                        <span class="ml-1 sm:ml-2 font-mono break-all">{{ $activity->id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Dibuat:</span>
                        <span class="ml-1 sm:ml-2">{{ $activity->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Diperbarui:</span>
                        <span class="ml-1 sm:ml-2">{{ $activity->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Activities (if project exists) -->
    @if($activity->project)
        <div class="mt-4 sm:mt-6 card overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Aktivitas Terkait di Proyek Ini</h3>
            </div>
            
            <div class="p-4 sm:p-6">
                @php
                    $relatedActivities = $activity->project->activities()
                        ->where('id', '!=', $activity->id)
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($relatedActivities->count() > 0)
                    <div class="space-y-2 sm:space-y-3">
                        @foreach($relatedActivities as $relatedActivity)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-gray-50 rounded-lg space-y-2 sm:space-y-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-3">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$relatedActivity->activity_type] ?? 'bg-gray-100 text-gray-800' }} w-fit">
                                            {{ $activityTypes[$relatedActivity->activity_type] ?? $relatedActivity->activity_type }}
                                        </span>
                                        <span class="text-xs sm:text-sm text-gray-900 break-words">{{ Str::limit($relatedActivity->description, 60) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $relatedActivity->created_at->format('d/m/Y H:i') }}
                                        @if($relatedActivity->user)
                                            oleh {{ $relatedActivity->user->name }}
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('reports.activities.show', $relatedActivity->id) }}"
                                   class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm self-end sm:self-center">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-3 sm:mt-4 text-center">
                        <a href="{{ route('reports.activities', ['project_id' => $activity->project->id]) }}"
                           class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm">
                            <i class="fas fa-list mr-1"></i>Lihat semua aktivitas proyek ini
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-3 sm:py-4 text-sm">Tidak ada aktivitas terkait lainnya</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
