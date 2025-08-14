<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Hari Libur - {{ $employee->name }}
            </h2>
            <a href="{{ route('finance.employees.custom-off-days.show', [$employee, $customOffDay]) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('finance.employees.custom-off-days.update', [$employee, $customOffDay]) }}" 
                          method="POST" 
                          id="customOffDayForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column - Main Form -->
                            <div class="space-y-6">
                                <div>
                                    <label for="off_date" class="block text-sm font-medium text-gray-700">
                                        Tanggal Libur
                                    </label>
                                    <input type="date" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('off_date') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                           id="off_date" 
                                           name="off_date" 
                                           value="{{ old('off_date', $customOffDay->off_date->format('Y-m-d')) }}"
                                           required>
                                    @error('off_date')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">
                                        Alasan/Keterangan
                                    </label>
                                    <input type="text" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('reason') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                           id="reason" 
                                           name="reason" 
                                           value="{{ old('reason', $customOffDay->reason) }}"
                                           placeholder="Contoh: Cuti pribadi, Sakit, dll">
                                    @error('reason')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">
                                        Tipe Libur
                                    </label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('type') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                            id="type" 
                                            name="type">
                                        <option value="">Pilih Tipe</option>
                                        <option value="personal" {{ old('type', $customOffDay->type) == 'personal' ? 'selected' : '' }}>
                                            Personal/Cuti Pribadi
                                        </option>
                                        <option value="sick" {{ old('type', $customOffDay->type) == 'sick' ? 'selected' : '' }}>
                                            Sakit
                                        </option>
                                        <option value="emergency" {{ old('type', $customOffDay->type) == 'emergency' ? 'selected' : '' }}>
                                            Darurat
                                        </option>
                                        <option value="religious" {{ old('type', $customOffDay->type) == 'religious' ? 'selected' : '' }}>
                                            Keagamaan
                                        </option>
                                        <option value="family" {{ old('type', $customOffDay->type) == 'family' ? 'selected' : '' }}>
                                            Keluarga
                                        </option>
                                        <option value="other" {{ old('type', $customOffDay->type) == 'other' ? 'selected' : '' }}>
                                            Lainnya
                                        </option>
                                    </select>
                                    @error('type')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                               type="checkbox" 
                                               id="is_paid" 
                                               name="is_paid" 
                                               value="1"
                                               {{ old('is_paid', $customOffDay->is_paid) ? 'checked' : '' }}>
                                        <label class="ml-2 block text-sm font-medium text-gray-700" for="is_paid">
                                            Libur Berbayar
                                        </label>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Centang jika hari libur ini tetap dibayar</p>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">
                                        Catatan Tambahan
                                    </label>
                                    <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="4"
                                              placeholder="Catatan tambahan tentang hari libur ini...">{{ old('notes', $customOffDay->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column - Additional Info -->
                            <div class="space-y-6">
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
                                                    <li>Hari libur custom hanya berlaku untuk karyawan dengan jadwal flexible</li>
                                                    <li>Tanggal yang dipilih akan dikecualikan dari perhitungan hari kerja</li>
                                                    <li>Libur berbayar akan tetap dihitung dalam kalkulasi gaji</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Schedule Info -->
                                @if($employee->activeWorkSchedule)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Jadwal Kerja Aktif
                                    </h4>
                                    <div class="text-sm text-gray-700">
                                        <p class="mb-1">
                                            <strong>Tipe:</strong> 
                                            @switch($employee->activeWorkSchedule->schedule_type)
                                                @case('standard')
                                                    Standard (Senin-Jumat)
                                                    @break
                                                @case('custom')
                                                    Custom Fixed Days
                                                    @break
                                                @case('flexible')
                                                    Flexible Schedule
                                                    @break
                                            @endswitch
                                        </p>
                                        @if($employee->activeWorkSchedule->schedule_type === 'flexible')
                                        <p class="text-xs text-gray-600">
                                            Target: {{ $employee->activeWorkSchedule->target_work_days }} hari kerja/bulan
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Type Helper -->
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-yellow-800 mb-2">Panduan Tipe Libur</h4>
                                    <div class="text-xs text-yellow-700 space-y-1">
                                        <p><strong>Personal:</strong> Cuti pribadi, liburan</p>
                                        <p><strong>Sakit:</strong> Tidak masuk karena sakit</p>
                                        <p><strong>Darurat:</strong> Keperluan mendesak</p>
                                        <p><strong>Keagamaan:</strong> Ibadah, hari raya</p>
                                        <p><strong>Keluarga:</strong> Acara keluarga, duka</p>
                                        <p><strong>Lainnya:</strong> Alasan lain yang tidak tercantum</p>
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
                                <i class="fas fa-save mr-2"></i>Update Hari Libur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        document.getElementById('customOffDayForm').addEventListener('submit', function(e) {
            const offDate = document.getElementById('off_date').value;
            
            if (!offDate) {
                e.preventDefault();
                alert('Tanggal libur harus diisi.');
                return false;
            }
            
            // Check if date is not in the past (optional validation)
            const selectedDate = new Date(offDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                const confirmPast = confirm('Tanggal yang dipilih sudah berlalu. Apakah Anda yakin ingin melanjutkan?');
                if (!confirmPast) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Auto-fill reason based on type selection
        document.getElementById('type').addEventListener('change', function() {
            const reasonField = document.getElementById('reason');
            const selectedType = this.value;
            
            if (!reasonField.value) { // Only auto-fill if reason is empty
                switch(selectedType) {
                    case 'personal':
                        reasonField.value = 'Cuti pribadi';
                        break;
                    case 'sick':
                        reasonField.value = 'Sakit';
                        break;
                    case 'emergency':
                        reasonField.value = 'Keperluan darurat';
                        break;
                    case 'religious':
                        reasonField.value = 'Keperluan keagamaan';
                        break;
                    case 'family':
                        reasonField.value = 'Keperluan keluarga';
                        break;
                }
            }
        });
    });
    </script>
</x-app-layout>