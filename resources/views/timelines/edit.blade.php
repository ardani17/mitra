@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Update Progress Timeline</h1>
            <p class="text-gray-600 mt-1">Proyek: {{ $project->name }} ({{ $project->code }})</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Kembali ke Proyek
        </a>
    </div>

    <!-- Project Info Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi Proyek</h3>
                <div class="mt-1 text-sm text-blue-700">
                    <p><strong>Nama:</strong> {{ $project->name }}</p>
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $project->status)) }}</p>
                    @if($project->start_date && $project->end_date)
                    <p><strong>Periode:</strong> {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('timelines.update', $timeline) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Hidden project_id field -->
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Progres *</label>
                    <input type="text" name="milestone" value="{{ old('milestone', $timeline->milestone) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Masukkan nama progres">
                    @error('milestone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Rencana *</label>
                    <input type="date" name="planned_date" value="{{ old('planned_date', $timeline->planned_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('planned_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Aktual</label>
                    <input type="date" name="actual_date" value="{{ old('actual_date', $timeline->actual_date ? $timeline->actual_date->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Akan diisi otomatis saat status diubah ke "Selesai"</p>
                    @error('actual_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="planned" {{ old('status', $timeline->status) == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                        <option value="in_progress" {{ old('status', $timeline->status) == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="completed" {{ old('status', $timeline->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="delayed" {{ old('status', $timeline->status) == 'delayed' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Progress (%) *</label>
                    <input type="number" name="progress_percentage" id="progress_percentage" value="{{ old('progress_percentage', $timeline->progress_percentage) }}" required min="0" max="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress_bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $timeline->progress_percentage }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Progress saat ini: <span id="progress_text">{{ $timeline->progress_percentage }}%</span></p>
                    </div>
                    @error('progress_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Masukkan deskripsi progres">{{ old('description', $timeline->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-8 flex justify-end">
                <a href="{{ route('projects.show', $project) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Progress
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Informasi Update Timeline</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Ketika status diubah ke "Selesai", tanggal aktual akan otomatis diisi dengan tanggal hari ini jika belum diisi.</li>
                        <li>Progress 100% akan otomatis diset ketika status diubah ke "Selesai".</li>
                        <li>Status "Terlambat" akan otomatis diset jika tanggal rencana sudah terlewat dan progress belum 100%.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressInput = document.getElementById('progress_percentage');
    const progressBar = document.getElementById('progress_bar');
    const progressText = document.getElementById('progress_text');
    const statusSelect = document.getElementById('status');
    
    // Update progress bar when input changes
    progressInput.addEventListener('input', function() {
        const value = parseInt(this.value) || 0;
        const clampedValue = Math.max(0, Math.min(100, value));
        
        progressBar.style.width = clampedValue + '%';
        progressText.textContent = clampedValue + '%';
        
        // Update progress bar color based on value
        if (clampedValue === 100) {
            progressBar.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
        } else if (clampedValue >= 75) {
            progressBar.className = 'bg-blue-600 h-2 rounded-full transition-all duration-300';
        } else if (clampedValue >= 50) {
            progressBar.className = 'bg-yellow-600 h-2 rounded-full transition-all duration-300';
        } else {
            progressBar.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
        }
        
        // Auto-update status based on progress
        if (clampedValue === 100 && statusSelect.value !== 'completed') {
            statusSelect.value = 'completed';
        } else if (clampedValue > 0 && clampedValue < 100 && statusSelect.value === 'planned') {
            statusSelect.value = 'in_progress';
        }
    });
    
    // Update progress when status changes
    statusSelect.addEventListener('change', function() {
        if (this.value === 'completed' && parseInt(progressInput.value) < 100) {
            progressInput.value = 100;
            progressInput.dispatchEvent(new Event('input'));
        } else if (this.value === 'in_progress' && parseInt(progressInput.value) === 0) {
            progressInput.value = 25;
            progressInput.dispatchEvent(new Event('input'));
        }
    });
    
    // Initialize progress bar color
    progressInput.dispatchEvent(new Event('input'));
});
</script>
@endsection
