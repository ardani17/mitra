@extends('layouts.app')

@section('title', 'Kelola Jadwal Termin - ' . $project->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Kelola Jadwal Termin</h1>
            <p class="text-slate-600 mt-1">{{ $project->name }} ({{ $project->code }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('projects.show', $project) }}" 
               class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Proyek
            </a>
        </div>
    </div>

    <!-- Project Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Ringkasan Proyek</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Total Nilai Proyek</label>
                <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($project->total_value, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sudah Ditagih</label>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($project->billings->sum('total_amount'), 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sisa Tagihan</label>
                <p class="text-2xl font-bold text-orange-600">Rp {{ number_format($project->total_value - $project->billings->sum('total_amount'), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Current Termin Schedules -->
    @if($project->paymentSchedules->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Jadwal Termin Saat Ini</h2>
                <div class="text-sm text-slate-600">
                    Total: {{ $project->paymentSchedules->sum('percentage') }}%
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Termin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Persentase</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($project->paymentSchedules->sortBy('termin_number') as $schedule)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">Termin {{ $schedule->termin_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900">{{ $schedule->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ $schedule->percentage }}%</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">Rp {{ number_format($schedule->calculateAmount($project), 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($schedule->billings->where('status', 'paid')->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Lunas
                                        </span>
                                    @elseif($schedule->billings->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Tertagih
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Belum Ditagih
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($schedule->billings->count() === 0)
                                        <a href="{{ route('project-billings.create', ['project' => $project->id, 'schedule' => $schedule->id]) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Buat Tagihan</a>
                                    @else
                                        <a href="{{ route('project-billings.show', $schedule->billings->first()) }}" 
                                           class="text-green-600 hover:text-green-900 mr-3">Lihat Tagihan</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($project->paymentSchedules->sum('percentage') != 100)
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Total persentase termin saat ini adalah {{ $project->paymentSchedules->sum('percentage') }}%. 
                                   Untuk jadwal termin yang lengkap, total persentase harus 100%.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Create New Termin Schedule Form -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">
            @if($project->paymentSchedules->count() === 0)
                Buat Jadwal Termin Baru
            @else
                Tambah Termin
            @endif
        </h2>
        
        <form action="{{ route('project-billings.store-termin-schedule', $project) }}" method="POST" id="termin-form">
            @csrf
            
            <div id="termin-schedules">
                @if(old('schedules'))
                    @foreach(old('schedules') as $index => $schedule)
                        <div class="termin-item border border-slate-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-md font-medium text-slate-800">Termin {{ $index + 1 }}</h3>
                                @if($index > 0)
                                    <button type="button" class="remove-termin text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Persentase <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="schedules[{{ $index }}][percentage]" required min="1" max="100" step="0.01"
                                               value="{{ $schedule['percentage'] ?? '' }}"
                                               class="w-full px-3 py-2 pr-8 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent percentage-input">
                                        <span class="absolute right-3 top-2 text-slate-500">%</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Nilai Estimasi</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-500">Rp</span>
                                        <input type="text" readonly
                                               class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md bg-slate-50 estimated-amount">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Deskripsi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="schedules[{{ $index }}][description]" required rows="2"
                                          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Deskripsi untuk termin ini...">{{ $schedule['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="termin-item border border-slate-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-md font-medium text-slate-800">Termin 1</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Persentase <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="schedules[0][percentage]" required min="1" max="100" step="0.01"
                                           class="w-full px-3 py-2 pr-8 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent percentage-input">
                                    <span class="absolute right-3 top-2 text-slate-500">%</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Nilai Estimasi</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-500">Rp</span>
                                    <input type="text" readonly
                                           class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md bg-slate-50 estimated-amount">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Deskripsi <span class="text-red-500">*</span>
                            </label>
                            <textarea name="schedules[0][description]" required rows="2"
                                      class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Deskripsi untuk termin ini..."></textarea>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Summary -->
            <div class="bg-slate-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-slate-700">Total Persentase:</span>
                    <span id="total-percentage" class="text-lg font-bold text-slate-900">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-700">Total Nilai Estimasi:</span>
                    <span id="total-estimated" class="text-lg font-bold text-slate-900">Rp 0</span>
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Persentase yang sudah ada: {{ $project->paymentSchedules->sum('percentage') }}%
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <button type="button" id="add-termin" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Termin
                </button>
                
                <div class="flex space-x-3">
                    <a href="{{ route('projects.show', $project) }}" 
                       class="px-6 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 rounded-lg font-medium transition-colors duration-200">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Simpan Jadwal Termin
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectTotalValue = {{ $project->total_value }};
    const existingPercentage = {{ $project->paymentSchedules->sum('percentage') }};
    let terminCount = document.querySelectorAll('.termin-item').length;

    function updateEstimatedAmounts() {
        const percentageInputs = document.querySelectorAll('.percentage-input');
        const estimatedAmounts = document.querySelectorAll('.estimated-amount');
        let totalPercentage = existingPercentage;
        let totalEstimated = 0;

        percentageInputs.forEach((input, index) => {
            const percentage = parseFloat(input.value) || 0;
            const estimatedAmount = (projectTotalValue * percentage) / 100;
            
            estimatedAmounts[index].value = estimatedAmount.toLocaleString('id-ID');
            totalPercentage += percentage;
            totalEstimated += estimatedAmount;
        });

        document.getElementById('total-percentage').textContent = totalPercentage.toFixed(2) + '%';
        document.getElementById('total-estimated').textContent = 'Rp ' + totalEstimated.toLocaleString('id-ID');

        // Validate total percentage
        const totalPercentageElement = document.getElementById('total-percentage');
        if (totalPercentage > 100) {
            totalPercentageElement.classList.add('text-red-600');
            totalPercentageElement.classList.remove('text-slate-900');
        } else {
            totalPercentageElement.classList.remove('text-red-600');
            totalPercentageElement.classList.add('text-slate-900');
        }
    }

    function updateTerminNumbers() {
        const terminItems = document.querySelectorAll('.termin-item');
        terminItems.forEach((item, index) => {
            const title = item.querySelector('h3');
            title.textContent = `Termin ${index + 1}`;
            
            // Update input names
            const inputs = item.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
                }
            });
        });
    }

    // Add event listeners to existing percentage inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('percentage-input')) {
            updateEstimatedAmounts();
        }
    });

    // Add termin button
    document.getElementById('add-termin').addEventListener('click', function() {
        const terminSchedules = document.getElementById('termin-schedules');
        const newTerminHTML = `
            <div class="termin-item border border-slate-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-medium text-slate-800">Termin ${terminCount + 1}</h3>
                    <button type="button" class="remove-termin text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Persentase <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="schedules[${terminCount}][percentage]" required min="1" max="100" step="0.01"
                                   class="w-full px-3 py-2 pr-8 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent percentage-input">
                            <span class="absolute right-3 top-2 text-slate-500">%</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nilai Estimasi</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-500">Rp</span>
                            <input type="text" readonly
                                   class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md bg-slate-50 estimated-amount">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <textarea name="schedules[${terminCount}][description]" required rows="2"
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Deskripsi untuk termin ini..."></textarea>
                </div>
            </div>
        `;
        
        terminSchedules.insertAdjacentHTML('beforeend', newTerminHTML);
        terminCount++;
        updateTerminNumbers();
        updateEstimatedAmounts();
    });

    // Remove termin button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-termin')) {
            e.target.closest('.termin-item').remove();
            terminCount--;
            updateTerminNumbers();
            updateEstimatedAmounts();
        }
    });

    // Form validation
    document.getElementById('termin-form').addEventListener('submit', function(e) {
        const percentageInputs = document.querySelectorAll('.percentage-input');
        let totalPercentage = existingPercentage;
        
        percentageInputs.forEach(input => {
            totalPercentage += parseFloat(input.value) || 0;
        });

        if (totalPercentage > 100) {
            e.preventDefault();
            alert('Total persentase tidak boleh melebihi 100%');
            return false;
        }

        if (percentageInputs.length === 0) {
            e.preventDefault();
            alert('Minimal harus ada satu termin');
            return false;
        }
    });

    // Initial calculation
    updateEstimatedAmounts();
});
</script>
@endpush
@endsection