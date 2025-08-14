<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tambah Hari Libur - {{ $employee->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $employee->employee_code }} | {{ $employee->position }} | {{ $employee->department }}
                </p>
            </div>
            <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('finance.employees.custom-off-days.store', $employee) }}" method="POST" id="customOffDayForm">
                        @csrf
                        
                        <!-- Period Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="period_month" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bulan Periode <span class="text-red-500">*</span>
                                </label>
                                <select id="period_month" name="period_month" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($monthOptions as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('period_month')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="period_year" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tahun Periode <span class="text-red-500">*</span>
                                </label>
                                <select id="period_year" name="period_year" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($yearOptions as $yearOption)
                                        <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                            {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('period_year')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Off Dates Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Tanggal Libur <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <div>
                                    <input type="date" name="off_dates[]" required
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            @error('off_dates')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('off_dates.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reason -->
                        <div class="mb-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Libur
                            </label>
                            <input type="text" id="reason" name="reason" 
                                   value="{{ old('reason') }}"
                                   placeholder="Cuti, sakit, keperluan pribadi, dll..."
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ada alasan khusus</p>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quick Date Selection -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Pilihan Cepat</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <button type="button" onclick="addWeekendDates()" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-calendar-week mr-1"></i>Weekend Bulan Ini
                                </button>
                                <button type="button" onclick="addMondays()" 
                                        class="bg-purple-500 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-calendar-day mr-1"></i>Semua Senin
                                </button>
                                <button type="button" onclick="addFridays()" 
                                        class="bg-indigo-500 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-calendar-day mr-1"></i>Semua Jumat
                                </button>
                                <button type="button" onclick="clearAllDates()" 
                                        class="bg-red-500 hover:bg-red-700 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-trash mr-1"></i>Hapus Semua
                                </button>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Simpan Hari Libur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function addWeekendDates() {
            const year = document.getElementById('period_year').value;
            const month = document.getElementById('period_month').value;
            
            // Get all weekend dates for the month
            const daysInMonth = new Date(year, month, 0).getDate();
            const weekendDates = [];
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month - 1, day);
                if (date.getDay() === 0 || date.getDay() === 6) { // Sunday or Saturday
                    weekendDates.push(date.toISOString().split('T')[0]);
                }
            }
            
            // Set first weekend date to the input
            if (weekendDates.length > 0) {
                document.querySelector('input[name="off_dates[]"]').value = weekendDates[0];
            }
        }

        function addMondays() {
            addSpecificWeekday(1); // Monday
        }

        function addFridays() {
            addSpecificWeekday(5); // Friday
        }

        function addSpecificWeekday(weekday) {
            const year = document.getElementById('period_year').value;
            const month = document.getElementById('period_month').value;
            
            // Get all specific weekday dates for the month
            const daysInMonth = new Date(year, month, 0).getDate();
            const weekdayDates = [];
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month - 1, day);
                if (date.getDay() === weekday) {
                    weekdayDates.push(date.toISOString().split('T')[0]);
                }
            }
            
            // Set first weekday date to the input
            if (weekdayDates.length > 0) {
                document.querySelector('input[name="off_dates[]"]').value = weekdayDates[0];
            }
        }

        function clearAllDates() {
            // Clear the input
            document.querySelector('input[name="off_dates[]"]').value = '';
        }
    </script>
    @endpush
</x-app-layout>