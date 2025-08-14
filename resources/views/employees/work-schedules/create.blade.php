<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Buat Jadwal Kerja - {{ $employee->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $employee->employee_code }} | {{ $employee->position }} | {{ $employee->department }}
                </p>
            </div>
            <a href="{{ route('finance.employees.work-schedules.index', $employee) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('finance.employees.work-schedules.store', $employee) }}" method="POST" id="scheduleForm">
                        @csrf
                        
                        <!-- Schedule Type Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Jadwal Kerja</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="relative">
                                    <input type="radio" id="standard" name="schedule_type" value="standard" 
                                           class="sr-only peer" {{ old('schedule_type', 'standard') === 'standard' ? 'checked' : '' }}>
                                    <label for="standard" class="flex flex-col p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-semibold text-gray-900">Standard</span>
                                            <i class="fas fa-calendar-week text-blue-500"></i>
                                        </div>
                                        <p class="text-sm text-gray-600">Jadwal kerja standar dengan hari libur tetap (biasanya weekend)</p>
                                    </label>
                                </div>
                                
                                <div class="relative">
                                    <input type="radio" id="custom" name="schedule_type" value="custom" 
                                           class="sr-only peer" {{ old('schedule_type') === 'custom' ? 'checked' : '' }}>
                                    <label for="custom" class="flex flex-col p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-yellow-500 peer-checked:bg-yellow-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-semibold text-gray-900">Custom</span>
                                            <i class="fas fa-cog text-yellow-500"></i>
                                        </div>
                                        <p class="text-sm text-gray-600">Jadwal kerja dengan hari libur custom yang dapat ditentukan</p>
                                    </label>
                                </div>
                                
                                <div class="relative">
                                    <input type="radio" id="flexible" name="schedule_type" value="flexible" 
                                           class="sr-only peer" {{ old('schedule_type') === 'flexible' ? 'checked' : '' }}>
                                    <label for="flexible" class="flex flex-col p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-semibold text-gray-900">Flexible</span>
                                            <i class="fas fa-clock text-green-500"></i>
                                        </div>
                                        <p class="text-sm text-gray-600">Jadwal kerja fleksibel dengan target hari kerja per bulan</p>
                                    </label>
                                </div>
                            </div>
                            @error('schedule_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Standard/Custom Off Days Configuration -->
                        <div id="offDaysConfig" class="mb-6" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Hari Libur</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($dayOptions as $dayValue => $dayName)
                                    <div class="relative">
                                        <input type="checkbox" id="day_{{ $dayValue }}" name="standard_off_days[]" 
                                               value="{{ $dayValue }}" class="sr-only peer"
                                               {{ in_array($dayValue, old('standard_off_days', [0, 6])) ? 'checked' : '' }}>
                                        <label for="day_{{ $dayValue }}" class="flex items-center justify-center p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-red-500 peer-checked:bg-red-50">
                                            <span class="text-sm font-medium text-gray-900">{{ $dayName }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Pilih hari-hari yang akan menjadi hari libur tetap</p>
                            @error('standard_off_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flexible Work Days Configuration -->
                        <div id="flexibleConfig" class="mb-6" style="display: none;">
                            <label for="work_days_per_month" class="block text-sm font-medium text-gray-700 mb-2">
                                Target Hari Kerja per Bulan
                            </label>
                            <div class="relative">
                                <input type="number" id="work_days_per_month" name="work_days_per_month" 
                                       value="{{ old('work_days_per_month', 26) }}" min="1" max="31"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">hari</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Jumlah hari kerja yang diharapkan per bulan. Hari libur dapat diatur secara fleksibel melalui menu "Hari Libur Custom".
                            </p>
                            @error('work_days_per_month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Effective Period -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">
                                    Berlaku Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="effective_from" name="effective_from"
                                       value="{{ old('effective_from', now()->format('Y-m-d')) }}" required
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('effective_from')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="effective_until" class="block text-sm font-medium text-gray-700 mb-2">
                                    Berlaku Sampai
                                </label>
                                <input type="date" id="effective_until" name="effective_until"
                                       value="{{ old('effective_until') }}"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-sm text-gray-500">Kosongkan jika berlaku selamanya</p>
                                @error('effective_until')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Catatan tambahan tentang jadwal kerja ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Set as Active -->
                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="set_as_active" name="set_as_active" value="1"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       {{ old('set_as_active', true) ? 'checked' : '' }}>
                                <label for="set_as_active" class="ml-2 block text-sm text-gray-900">
                                    Aktifkan jadwal ini dan nonaktifkan jadwal lainnya
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Jika dicentang, jadwal kerja lain untuk karyawan ini akan dinonaktifkan secara otomatis.
                            </p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('finance.employees.work-schedules.index', $employee) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Simpan Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scheduleTypeInputs = document.querySelectorAll('input[name="schedule_type"]');
            const offDaysConfig = document.getElementById('offDaysConfig');
            const flexibleConfig = document.getElementById('flexibleConfig');
            
            function toggleConfigurations() {
                const selectedType = document.querySelector('input[name="schedule_type"]:checked').value;
                
                // Hide all configurations first
                offDaysConfig.style.display = 'none';
                flexibleConfig.style.display = 'none';
                
                // Show relevant configuration
                if (selectedType === 'standard' || selectedType === 'custom') {
                    offDaysConfig.style.display = 'block';
                    
                    // Set default values for standard type
                    if (selectedType === 'standard') {
                        // Check Sunday (0) and Saturday (6) by default for standard
                        const sundayCheckbox = document.getElementById('day_0');
                        const saturdayCheckbox = document.getElementById('day_6');
                        if (sundayCheckbox && !sundayCheckbox.checked) sundayCheckbox.checked = true;
                        if (saturdayCheckbox && !saturdayCheckbox.checked) saturdayCheckbox.checked = true;
                    }
                } else if (selectedType === 'flexible') {
                    flexibleConfig.style.display = 'block';
                }
            }
            
            // Initialize on page load
            toggleConfigurations();
            
            // Add event listeners
            scheduleTypeInputs.forEach(input => {
                input.addEventListener('change', toggleConfigurations);
            });
            
            // Form validation
            document.getElementById('scheduleForm').addEventListener('submit', function(e) {
                const selectedType = document.querySelector('input[name="schedule_type"]:checked').value;
                
                if (selectedType === 'standard' || selectedType === 'custom') {
                    const checkedDays = document.querySelectorAll('input[name="standard_off_days[]"]:checked');
                    if (checkedDays.length === 0) {
                        e.preventDefault();
                        alert('Pilih minimal satu hari libur untuk jadwal ' + selectedType);
                        return false;
                    }
                } else if (selectedType === 'flexible') {
                    const workDays = document.getElementById('work_days_per_month').value;
                    if (!workDays || workDays < 1 || workDays > 31) {
                        e.preventDefault();
                        alert('Target hari kerja per bulan harus antara 1-31 hari');
                        return false;
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>