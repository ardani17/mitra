@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-3 sm:space-y-0">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Tambah Progres Timeline</h1>
            @if(isset($project))
            <p class="text-sm sm:text-base text-gray-600 mt-1">Proyek: {{ $project->name }} ({{ $project->code }})</p>
            @endif
        </div>
        <a href="{{ isset($project) ? route('projects.show', $project) : route('timelines.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
            {{ isset($project) ? 'Kembali ke Proyek' : 'Kembali ke Timeline' }}
        </a>
    </div>

    @if(isset($project))
    <!-- Project Info Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-6">
        <div class="flex items-start sm:items-center">
            <div class="flex-shrink-0">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-xs sm:text-sm font-medium text-blue-800">Informasi Proyek</h3>
                <div class="mt-1 text-xs sm:text-sm text-blue-700 space-y-1">
                    <p><strong>Nama:</strong> {{ $project->name }}</p>
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $project->status)) }}</p>
                    @if($project->start_date && $project->end_date)
                    <p><strong>Periode:</strong> {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <form action="{{ route('timelines.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                @if(isset($project))
                <!-- Hidden project field when coming from project page -->
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="lg:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Proyek</label>
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 text-xs sm:text-sm">
                        {{ $project->name }} ({{ $project->code }})
                    </div>
                </div>
                @else
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Proyek *</label>
                    <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                        <option value="">Pilih Proyek</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }} ({{ $proj->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Nama Progres *</label>
                    <input type="text" name="milestone" value="{{ old('milestone') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                           placeholder="Masukkan nama progres">
                    @error('milestone')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tanggal Rencana *</label>
                    <input type="date" name="planned_date" value="{{ old('planned_date') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                    @error('planned_date')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                        <option value="planned" {{ old('status', 'planned') == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="delayed" {{ old('status') == 'delayed' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Progress (%) *</label>
                    <input type="number" name="progress_percentage" id="progress_percentage" value="{{ old('progress_percentage', 0) }}" required min="0" max="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress_bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Progress saat ini: <span id="progress_text">0%</span></p>
                    </div>
                    @error('progress_percentage')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-4 sm:mt-6">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                          placeholder="Masukkan deskripsi progres">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ isset($project) ? route('projects.show', $project) : route('timelines.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-xs sm:text-sm text-center">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-xs sm:text-sm">
                    Tambah Progres
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-xs sm:text-sm font-medium text-blue-800">Informasi Timeline Management</h3>
                <div class="mt-2 text-xs sm:text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Progres timeline membantu tracking progress proyek secara terstruktur.</li>
                        <li>Progress dapat diupdate seiring berjalannya waktu untuk monitoring yang akurat.</li>
                        <li>Status akan otomatis menyesuaikan berdasarkan progress yang diinput.</li>
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
    
    // Initialize progress bar
    progressInput.dispatchEvent(new Event('input'));
});
</script>
@endsection
