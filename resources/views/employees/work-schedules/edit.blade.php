<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Jadwal Kerja - {{ $employee->name }}
            </h2>
            <a href="{{ route('finance.employees.work-schedules.show', [$employee, $workSchedule]) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('finance.employees.work-schedules.update', [$employee, $workSchedule]) }}" 
                          method="POST" 
                          id="workScheduleForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column - Main Form -->
                            <div class="space-y-6">
                                <div>
                                    <label for="schedule_type" class="block text-sm font-medium text-gray-700">
                                        Tipe Jadwal Kerja
                                    </label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('schedule_type') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                            id="schedule_type" 
                                            name="schedule_type" 
                                            required>
                                        <option value="">Pilih Tipe Jadwal</option>
                                        <option value="standard" {{ old('schedule_type', $workSchedule->schedule_type) == 'standard' ? 'selected' : '' }}>
                                            Standard (Senin-Jumat)
                                        </option>
                                        <option value="custom" {{ old('schedule_type', $workSchedule->schedule_type) == 'custom' ? 'selected' : '' }}>
                                            Custom Fixed Days
                                        </option>
                                        <option value="flexible" {{ old('schedule_type', $workSchedule->schedule_type) == 'flexible' ? 'selected' : '' }}>
                                            Flexible Schedule
                                        </option>
                                    </select>
                                    @error('schedule_type')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Custom Work Days (only for custom type) -->
                                <div id="customWorkDays" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Hari Kerja</label>
                                    @php
                                        $currentWorkDays = json_decode($workSchedule->work_days, true) ?? [];
                                        $days = [
                                            'monday' => 'Senin',
                                            'tuesday' => 'Selasa',
                                            'wednesday' => 'Rabu', 
                                            'thursday' => 'Kamis',
                                            'friday' => 'Jumat',
                                            'saturday' => 'Sabtu',
                                            'sunday' => 'Minggu'
                                        ];
                                    @endphp
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach($days as $value => $label)
                                        <div class="flex items-center">
                                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                                   type="checkbox" 
                                                   name="work_days[]" 
                                                   value="{{ $value }}" 
                                                   id="day_{{ $value }}"
                                                   {{ in_array($value, old('work_days', $currentWorkDays)) ? 'checked' : '' }}>
                                            <label class="ml-2 block text-sm text-gray-900" for="day_{{ $value }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @error('work_days')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Target Work Days (only for flexible type) -->
                                <div id="targetWorkDays" style="display: none;">
                                    <label for="target_work_days" class="block text-sm font-medium text-gray-700">
                                        Target Hari Kerja per Bulan
                                    </label>
                                    <input type="number" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('target_work_days') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                           id="target_work_days" 
                                           name="target_work_days" 
                                           min="1" 
                                           max="31"
                                           value="{{ old('target_work_days', $workSchedule->target_work_days) }}">
                                    @error('target_work_days')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-sm text-gray-500">Jumlah hari kerja yang diharapkan dalam satu bulan</p>
                                </div>

                                <div>
                                    <label for="effective_from" class="block text-sm font-medium text-gray-700">
                                        Berlaku Mulai
                                    </label>
                                    <input type="date" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('effective_from') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                           id="effective_from" 
                                           name="effective_from"
                                           value="{{ old('effective_from', $workSchedule->effective_from?->format('Y-m-d')) }}">
                                    @error('effective_from')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="effective_until" class="block text-sm font-medium text-gray-700">
                                        Berlaku Sampai
                                    </label>
                                    <input type="date" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('effective_until') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                           id="effective_until" 
                                           name="effective_until"
                                           value="{{ old('effective_until', $workSchedule->effective_until?->format('Y-m-d')) }}">
                                    @error('effective_until')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-sm text-gray-500">Kosongkan jika tidak ada batas waktu</p>
                                </div>
                            </div>

                            <!-- Right Column - Additional Settings -->
                            <div class="space-y-6">
                                <div>
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $workSchedule->is_active) ? 'checked' : '' }}>
                                        <label class="ml-2 block text-sm font-medium text-gray-700" for="is_active">
                                            Jadwal Aktif
                                        </label>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Hanya satu jadwal yang bisa aktif per karyawan</p>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                                    <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="4"
                                              placeholder="Catatan tambahan tentang jadwal kerja ini...">{{ old('notes', $workSchedule->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Info Box -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-blue-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-blue-800">Informasi</h4>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <ul class="list-disc list-inside space-y-1">
                                                    <li><strong>Standard:</strong> Senin-Jumat kerja, Sabtu-Minggu libur</li>
                                                    <li><strong>Custom:</strong> Pilih hari kerja sesuai kebutuhan</li>
                                                    <li><strong>Flexible:</strong> Target hari kerja per bulan dengan libur fleksibel</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="button" 
                                    onclick="window.history.back()"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                <i class="fas fa-times mr-2"></i>Batal
                            </button>
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Update Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const scheduleTypeSelect = document.getElementById('schedule_type');
        const customWorkDays = document.getElementById('customWorkDays');
        const targetWorkDays = document.getElementById('targetWorkDays');

        function toggleScheduleFields() {
            const selectedType = scheduleTypeSelect.value;
            
            // Hide all conditional fields
            customWorkDays.style.display = 'none';
            targetWorkDays.style.display = 'none';
            
            // Show relevant fields based on selection
            if (selectedType === 'custom') {
                customWorkDays.style.display = 'block';
            } else if (selectedType === 'flexible') {
                targetWorkDays.style.display = 'block';
            }
        }

        // Initialize on page load
        toggleScheduleFields();
        
        // Listen for changes
        scheduleTypeSelect.addEventListener('change', toggleScheduleFields);

        // Form validation
        document.getElementById('workScheduleForm').addEventListener('submit', function(e) {
            const scheduleType = scheduleTypeSelect.value;
            
            if (scheduleType === 'custom') {
                const checkedDays = document.querySelectorAll('input[name="work_days[]"]:checked');
                if (checkedDays.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu hari kerja untuk jadwal custom.');
                    return false;
                }
            }
            
            if (scheduleType === 'flexible') {
                const targetDays = document.getElementById('target_work_days').value;
                if (!targetDays || targetDays < 1 || targetDays > 31) {
                    e.preventDefault();
                    alert('Target hari kerja harus antara 1-31 hari.');
                    return false;
                }
            }
        });
    });
    </script>
</x-app-layout>