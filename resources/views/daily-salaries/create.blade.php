<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Input Gaji Harian') }}
            </h2>
            <a href="{{ route('finance.daily-salaries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('finance.daily-salaries.store') }}" class="space-y-6">
                        @csrf

                        <!-- Employee Selection -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">
                                Karyawan <span class="text-red-500">*</span>
                            </label>
                            <select name="employee_id" id="employee_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('employee_id') border-red-500 @enderror">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                            data-daily-rate="{{ $employee->daily_rate }}"
                                            {{ old('employee_id', $selectedEmployee?->id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employee Info Display -->
                        <div id="employee-info" class="hidden bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Informasi Karyawan</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p><span class="font-medium">Gaji Harian:</span> <span id="employee-daily-rate">-</span></p>
                                        <p><span class="font-medium">Posisi:</span> <span id="employee-position">-</span></p>
                                        <p><span class="font-medium">Departemen:</span> <span id="employee-department">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Work Date -->
                        <div>
                            <label for="work_date" class="block text-sm font-medium text-gray-700">
                                Tanggal Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="work_date" id="work_date" 
                                   value="{{ old('work_date', $workDate) }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('work_date') border-red-500 @enderror">
                            @error('work_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Work Hours and Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="hours_worked" class="block text-sm font-medium text-gray-700">
                                    Jam Kerja <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="hours_worked" id="hours_worked" 
                                       value="{{ old('hours_worked', 8) }}" 
                                       placeholder="8"
                                       min="0" max="24" step="0.5"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('hours_worked') border-red-500 @enderror">
                                @error('hours_worked')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Jam kerja normal per hari</p>
                            </div>

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">
                                    Jumlah Gaji <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" 
                                           value="{{ old('amount') }}" 
                                           placeholder="0"
                                           min="0" step="1000"
                                           class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Akan otomatis terisi berdasarkan gaji harian</p>
                            </div>
                        </div>

                        <!-- Overtime -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="overtime_hours" class="block text-sm font-medium text-gray-700">
                                    Jam Lembur
                                </label>
                                <input type="number" name="overtime_hours" id="overtime_hours" 
                                       value="{{ old('overtime_hours', 0) }}" 
                                       placeholder="0"
                                       min="0" max="12" step="0.5"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('overtime_hours') border-red-500 @enderror">
                                @error('overtime_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Jam lembur (opsional)</p>
                            </div>

                            <div>
                                <label for="overtime_amount" class="block text-sm font-medium text-gray-700">
                                    Jumlah Lembur
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="overtime_amount" id="overtime_amount" 
                                           value="{{ old('overtime_amount', 0) }}" 
                                           placeholder="0"
                                           min="0" step="1000"
                                           class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('overtime_amount') border-red-500 @enderror">
                                </div>
                                @error('overtime_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Akan otomatis dihitung (1.5x tarif per jam)</p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Draft dapat diubah, Dikonfirmasi tidak dapat diubah</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      placeholder="Catatan tambahan (opsional)"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total Summary -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Ringkasan</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Gaji Normal:</span>
                                    <span class="font-medium" id="normal-salary-display">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Lembur:</span>
                                    <span class="font-medium" id="overtime-salary-display">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-bold text-green-600" id="total-salary-display">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('finance.daily-salaries.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Simpan Gaji
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let employeeData = {};

        // Load employee data
        @foreach($employees as $employee)
            employeeData[{{ $employee->id }}] = {
                name: '{{ $employee->name }}',
                position: '{{ $employee->position }}',
                department: '{{ $employee->department }}',
                daily_rate: {{ $employee->daily_rate }},
                formatted_daily_rate: '{{ $employee->formatted_daily_rate }}'
            };
        @endforeach

        // Employee selection change
        document.getElementById('employee_id').addEventListener('change', function() {
            const employeeId = this.value;
            const employeeInfo = document.getElementById('employee-info');
            
            if (employeeId && employeeData[employeeId]) {
                const employee = employeeData[employeeId];
                
                // Show employee info
                document.getElementById('employee-daily-rate').textContent = employee.formatted_daily_rate;
                document.getElementById('employee-position').textContent = employee.position;
                document.getElementById('employee-department').textContent = employee.department;
                employeeInfo.classList.remove('hidden');
                
                // Auto-fill amount based on daily rate
                calculateSalary();
            } else {
                employeeInfo.classList.add('hidden');
                document.getElementById('amount').value = '';
                updateSummary();
            }
        });

        // Calculate salary based on hours worked
        document.getElementById('hours_worked').addEventListener('input', calculateSalary);
        document.getElementById('overtime_hours').addEventListener('input', calculateOvertimeSalary);

        function calculateSalary() {
            const employeeId = document.getElementById('employee_id').value;
            const hoursWorked = parseFloat(document.getElementById('hours_worked').value) || 0;
            
            if (employeeId && employeeData[employeeId]) {
                const dailyRate = employeeData[employeeId].daily_rate;
                const amount = (dailyRate / 8) * hoursWorked; // Assuming 8 hours per day
                document.getElementById('amount').value = Math.round(amount);
                updateSummary();
            }
        }

        function calculateOvertimeSalary() {
            const employeeId = document.getElementById('employee_id').value;
            const overtimeHours = parseFloat(document.getElementById('overtime_hours').value) || 0;
            
            if (employeeId && employeeData[employeeId]) {
                const dailyRate = employeeData[employeeId].daily_rate;
                const hourlyRate = dailyRate / 8;
                const overtimeAmount = overtimeHours * hourlyRate * 1.5; // 1.5x overtime rate
                document.getElementById('overtime_amount').value = Math.round(overtimeAmount);
                updateSummary();
            }
        }

        function updateSummary() {
            const normalSalary = parseFloat(document.getElementById('amount').value) || 0;
            const overtimeSalary = parseFloat(document.getElementById('overtime_amount').value) || 0;
            const total = normalSalary + overtimeSalary;

            document.getElementById('normal-salary-display').textContent = 'Rp ' + normalSalary.toLocaleString('id-ID');
            document.getElementById('overtime-salary-display').textContent = 'Rp ' + overtimeSalary.toLocaleString('id-ID');
            document.getElementById('total-salary-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Manual amount change
        document.getElementById('amount').addEventListener('input', updateSummary);
        document.getElementById('overtime_amount').addEventListener('input', updateSummary);

        // Initialize if employee is pre-selected
        if (document.getElementById('employee_id').value) {
            document.getElementById('employee_id').dispatchEvent(new Event('change'));
        }

        // Initialize summary
        updateSummary();
    </script>
    @endpush
</x-app-layout>