<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ __('Buat Rilis Gaji') }}
            </h2>
            <a href="{{ route('finance.salary-releases.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('finance.salary-releases.store') }}" class="space-y-4 sm:space-y-6">
                        @csrf

                        <!-- Employee Selection -->
                        <div>
                            <label for="employee_id" class="block text-xs sm:text-sm font-medium text-gray-700">
                                Karyawan <span class="text-red-500">*</span>
                            </label>
                            <select name="employee_id" id="employee_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm @error('employee_id') border-red-500 @enderror">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                            {{ old('employee_id', $selectedEmployee?->id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employee Info Display -->
                        <div id="employee-info" class="hidden bg-blue-50 border border-blue-200 rounded-md p-3 sm:p-4">
                            <div class="flex items-start sm:items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-xs sm:text-sm font-medium text-blue-800">Informasi Karyawan</h3>
                                    <div class="mt-2 text-xs sm:text-sm text-blue-700 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
                                        <p><span class="font-medium">Posisi:</span> <span id="employee-position">-</span></p>
                                        <p><span class="font-medium">Departemen:</span> <span id="employee-department">-</span></p>
                                        <p><span class="font-medium">Gaji Harian:</span> <span id="employee-daily-rate">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Period Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label for="period_start" class="block text-xs sm:text-sm font-medium text-gray-700">
                                    Periode Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_start" id="period_start"
                                       value="{{ old('period_start', $periodStart) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm @error('period_start') border-red-500 @enderror">
                                @error('period_start')
                                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="period_end" class="block text-xs sm:text-sm font-medium text-gray-700">
                                    Periode Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_end" id="period_end"
                                       value="{{ old('period_end', $periodEnd) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm @error('period_end') border-red-500 @enderror">
                                @error('period_end')
                                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Quick Period Buttons -->
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                            <button type="button" class="period-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-xs sm:text-sm"
                                    data-period="current-week">Minggu Ini</button>
                            <button type="button" class="period-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-xs sm:text-sm"
                                    data-period="last-week">Minggu Lalu</button>
                            <button type="button" class="period-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-xs sm:text-sm"
                                    data-period="current-month">Bulan Ini</button>
                            <button type="button" class="period-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-xs sm:text-sm"
                                    data-period="last-month">Bulan Lalu</button>
                        </div>

                        <!-- Unreleased Salaries Preview -->
                        <div id="salary-preview" class="hidden">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Gaji yang Akan Dirilis</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-3 sm:p-4">
                                <div id="salary-list" class="space-y-2">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                                <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-300">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-gray-900 text-sm sm:text-base">Total Kotor:</span>
                                        <span class="font-bold text-green-600 text-sm sm:text-base" id="total-gross">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div>
                            <label for="deductions" class="block text-xs sm:text-sm font-medium text-gray-700">
                                Potongan
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-xs sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="deductions" id="deductions"
                                       value="{{ old('deductions', 0) }}"
                                       placeholder="0"
                                       min="0" step="1000"
                                       class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm @error('deductions') border-red-500 @enderror">
                            </div>
                            @error('deductions')
                                <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs sm:text-sm text-gray-500">Potongan seperti BPJS, pajak, dll (opsional)</p>
                        </div>

                        <!-- Net Amount Display -->
                        <div class="bg-green-50 border border-green-200 rounded-md p-3 sm:p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm sm:text-lg font-medium text-green-800">Total Bersih:</span>
                                <span class="text-lg sm:text-2xl font-bold text-green-600" id="net-amount-display">Rp 0</span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-xs sm:text-sm font-medium text-gray-700">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      placeholder="Catatan tambahan untuk rilis gaji ini (opsional)"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 sm:pt-6 border-t border-gray-200">
                            <a href="{{ route('finance.salary-releases.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                                Batal
                            </a>
                            <button type="submit" id="submit-btn" disabled
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded disabled:opacity-50 text-sm sm:text-base">
                                <i class="fas fa-save mr-1 sm:mr-2"></i>Buat Rilis Gaji
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
                document.getElementById('employee-position').textContent = employee.position;
                document.getElementById('employee-department').textContent = employee.department;
                document.getElementById('employee-daily-rate').textContent = employee.formatted_daily_rate;
                employeeInfo.classList.remove('hidden');
                
                // Load unreleased salaries
                loadUnreleasedSalaries();
            } else {
                employeeInfo.classList.add('hidden');
                document.getElementById('salary-preview').classList.add('hidden');
                updateSubmitButton();
            }
        });

        // Period change
        document.getElementById('period_start').addEventListener('change', loadUnreleasedSalaries);
        document.getElementById('period_end').addEventListener('change', loadUnreleasedSalaries);
        document.getElementById('deductions').addEventListener('input', updateNetAmount);

        // Quick period buttons
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const period = this.dataset.period;
                const today = new Date();
                let startDate, endDate;

                switch(period) {
                    case 'current-week':
                        const currentWeekStart = new Date(today.setDate(today.getDate() - today.getDay()));
                        const currentWeekEnd = new Date(today.setDate(today.getDate() - today.getDay() + 6));
                        startDate = currentWeekStart.toISOString().split('T')[0];
                        endDate = currentWeekEnd.toISOString().split('T')[0];
                        break;
                    case 'last-week':
                        const lastWeekStart = new Date(today.setDate(today.getDate() - today.getDay() - 7));
                        const lastWeekEnd = new Date(today.setDate(today.getDate() - today.getDay() - 1));
                        startDate = lastWeekStart.toISOString().split('T')[0];
                        endDate = lastWeekEnd.toISOString().split('T')[0];
                        break;
                    case 'current-month':
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
                        break;
                    case 'last-month':
                        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
                        endDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                        break;
                }

                document.getElementById('period_start').value = startDate;
                document.getElementById('period_end').value = endDate;
                loadUnreleasedSalaries();
            });
        });

        function loadUnreleasedSalaries() {
            const employeeId = document.getElementById('employee_id').value;
            const periodStart = document.getElementById('period_start').value;
            const periodEnd = document.getElementById('period_end').value;

            if (!employeeId || !periodStart || !periodEnd) {
                document.getElementById('salary-preview').classList.add('hidden');
                updateSubmitButton();
                return;
            }

            // Make AJAX request to get unreleased salaries
            fetch(`{{ route('finance.salary-releases.get-unreleased-salaries') }}?employee_id=${employeeId}&period_start=${periodStart}&period_end=${periodEnd}`)
                .then(response => response.json())
                .then(data => {
                    displaySalaries(data);
                    updateNetAmount();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('salary-preview').classList.add('hidden');
                    updateSubmitButton();
                });
        }

        function displaySalaries(data) {
            const salaryList = document.getElementById('salary-list');
            const salaryPreview = document.getElementById('salary-preview');
            
            if (data.count === 0) {
                salaryList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada gaji yang belum dirilis untuk periode ini.</p>';
                document.getElementById('total-gross').textContent = 'Rp 0';
                salaryPreview.classList.remove('hidden');
                updateSubmitButton();
                return;
            }

            let html = '';
            data.salaries.forEach(salary => {
                const workDate = new Date(salary.work_date).toLocaleDateString('id-ID');
                const totalAmount = salary.amount + salary.overtime_amount;
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <div>
                            <span class="font-medium">${workDate}</span>
                            <span class="text-sm text-gray-500 ml-2">(${salary.hours_worked}h + ${salary.overtime_hours}h lembur)</span>
                        </div>
                        <span class="font-medium">Rp ${totalAmount.toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
            
            salaryList.innerHTML = html;
            document.getElementById('total-gross').textContent = data.formatted_total;
            salaryPreview.classList.remove('hidden');
            updateSubmitButton();
        }

        function updateNetAmount() {
            const totalGrossText = document.getElementById('total-gross').textContent;
            const totalGross = parseFloat(totalGrossText.replace(/[^\d]/g, '')) || 0;
            const deductions = parseFloat(document.getElementById('deductions').value) || 0;
            const netAmount = totalGross - deductions;

            document.getElementById('net-amount-display').textContent = 'Rp ' + netAmount.toLocaleString('id-ID');
        }

        function updateSubmitButton() {
            const employeeId = document.getElementById('employee_id').value;
            const periodStart = document.getElementById('period_start').value;
            const periodEnd = document.getElementById('period_end').value;
            const salaryList = document.getElementById('salary-list');
            const submitBtn = document.getElementById('submit-btn');
            
            const hasSalaries = salaryList.children.length > 0 && 
                              !salaryList.textContent.includes('Tidak ada gaji');
            
            if (employeeId && periodStart && periodEnd && hasSalaries) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Initialize if employee is pre-selected
        if (document.getElementById('employee_id').value) {
            document.getElementById('employee_id').dispatchEvent(new Event('change'));
        }
    </script>
    @endpush
</x-app-layout>