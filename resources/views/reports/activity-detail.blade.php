@extends('layouts.app')

@section('title', 'Detail Aktivitas')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Detail Aktivitas</h1>
        <a href="{{ route('reports.activities') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Aktivitas</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                        <div class="text-sm text-gray-900">
                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                            <span class="text-gray-500">({{ $activity->created_at->diffForHumans() }})</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Aktivitas</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                        @if($activity->user)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-lg font-medium text-gray-700">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $activity->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400">User tidak ditemukan</span>
                        @endif
                    </div>
                </div>

                <!-- Project Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Proyek</label>
                        @if($activity->project)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $activity->project->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $activity->project->code }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($activity->project->description, 100) }}</p>
                                    </div>
                                    <a href="{{ route('projects.show', $activity->project->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm">
                                        <i class="fas fa-external-link-alt"></i> Lihat Proyek
                                    </a>
                                </div>
                                
                                <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
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
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-900">{{ $activity->description }}</p>
                </div>
            </div>

            <!-- Changes (if available) -->
            @if($activity->changes && is_array($activity->changes) && count($activity->changes) > 0)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Detail Perubahan</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-3">
                            @foreach($activity->changes as $key => $value)
                                <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                                    <span class="text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}:
                                    </span>
                                    <span class="text-sm text-gray-900">
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
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Metadata</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">ID Aktivitas:</span>
                        <span class="ml-2 font-mono">{{ $activity->id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Dibuat:</span>
                        <span class="ml-2">{{ $activity->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Diperbarui:</span>
                        <span class="ml-2">{{ $activity->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Activities (if project exists) -->
    @if($activity->project)
        <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terkait di Proyek Ini</h3>
            </div>
            
            <div class="p-6">
                @php
                    $relatedActivities = $activity->project->activities()
                        ->where('id', '!=', $activity->id)
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($relatedActivities->count() > 0)
                    <div class="space-y-3">
                        @foreach($relatedActivities as $relatedActivity)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$relatedActivity->activity_type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $activityTypes[$relatedActivity->activity_type] ?? $relatedActivity->activity_type }}
                                        </span>
                                        <span class="text-sm text-gray-900">{{ Str::limit($relatedActivity->description, 60) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $relatedActivity->created_at->format('d/m/Y H:i') }} 
                                        @if($relatedActivity->user)
                                            oleh {{ $relatedActivity->user->name }}
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('reports.activities.show', $relatedActivity->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('reports.activities', ['project_id' => $activity->project->id]) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm">
                            <i class="fas fa-list mr-1"></i>Lihat semua aktivitas proyek ini
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Tidak ada aktivitas terkait lainnya</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
