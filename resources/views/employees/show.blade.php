<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Karyawan') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.edit', $employee) }}" 
                   class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('finance.employees.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Employee Profile Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <img class="h-24 w-24 rounded-full object-cover" 
                                 src="{{ $employee->avatar_url }}" 
                                 alt="{{ $employee->name }}">
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h3>
                                    <p class="text-gray-600">{{ $employee->employee_code }}</p>
                                    <p class="text-sm text-gray-500">{{ $employee->position }} - {{ $employee->department }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Gaji Harian</p>
                                    <p class="text-xl font-semibold text-green-600">{{ $employee->formatted_daily_rate }}</p>
                                    <p class="text-sm text-gray-500">{{ ucfirst($employee->employment_type) }} - {!! $employee->status_badge !!}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kontak</p>
                                    <p class="text-sm text-gray-900">{{ $employee->email ?: '-' }}</p>
                                    <p class="text-sm text-gray-900">{{ $employee->phone ?: '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Month Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <!-- Custom Month/Year Selector -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Pilih Bulan:</label>
                                <select id="month-selector" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($monthOptions as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                <select id="year-selector" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($yearOptions as $yearOption)
                                        <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                            {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                                <button onclick="goToSelectedDate()" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded text-sm">
                                    <i class="fas fa-arrow-right"></i> Lihat
                                </button>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}"
                               class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-calendar-times mr-2"></i>Hari Libur
                            </a>
                            <button onclick="exportSalaryData()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-download mr-2"></i>Export
                            </button>
                            <button onclick="showSalaryReleaseModal()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-paper-plane mr-2"></i>Rilis Gaji
                            </button>
                            <button onclick="addDailySalary()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-plus mr-2"></i>Tambah Gaji
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Hari Kerja</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $monthlyStats['total_work_days'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                                <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($monthlyStats['total_earnings'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-chart-line text-yellow-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Rata-rata Harian</p>
                                <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($monthlyStats['average_daily'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-check text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Kehadiran</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <span class="text-green-600">{{ $monthlyStats['present_days'] }}</span> / 
                                    <span class="text-yellow-600">{{ $monthlyStats['late_days'] }}</span> / 
                                    <span class="text-red-600">{{ $monthlyStats['absent_days'] }}</span>
                                </p>
                                <p class="text-xs text-gray-500">Hadir / Telat / Libur</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salary Calendar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Kalender Gaji - {{ $currentMonthName }} {{ $year }}
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Pokok</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uang Makan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uang Absen</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uang Pulsa</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lembur</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Potongan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($monthlyCalendar as $dayData)
                                    <tr class="{{ $dayData['is_weekend'] ? 'bg-gray-50' : 'hover:bg-gray-50' }}">
                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $dayData['day_number'] }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $dayData['day_name'] }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if($dayData['salary'])
                                                {!! $dayData['salary']->attendance_status_badge !!}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_basic_salary }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_meal_allowance }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($dayData['salary'])
                                                <span class="{{ $dayData['salary']->attendance_bonus >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $dayData['salary']->formatted_attendance_bonus }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_phone_allowance }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_overtime_amount }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-red-600">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_deductions }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @if($dayData['salary'])
                                                {{ $dayData['salary']->formatted_total_amount_new }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-1">
                                                @if($dayData['salary'])
                                                    <button onclick="editDailySalary({{ $dayData['salary']->id }})" 
                                                            class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteDailySalary({{ $dayData['salary']->id }})" 
                                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button onclick="addDailySalaryForDate('{{ $dayData['date']->format('Y-m-d') }}')"
                                                            class="text-blue-600 hover:text-blue-900" title="Tambah Gaji">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Salary Releases Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Rilis Gaji</h3>
                        <button onclick="showSalaryReleaseModal()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                            <i class="fas fa-plus mr-2"></i>Buat Rilis Gaji
                        </button>
                    </div>
                    
                    <div id="salary-releases-list">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Memuat data rilis gaji...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Salary Modal -->
    <div id="dailySalaryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Gaji Harian</h3>
                    <button onclick="closeDailySalaryModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="dailySalaryForm">
                    <input type="hidden" id="salaryId" name="salary_id">
                    <input type="hidden" id="employeeId" name="employee_id" value="{{ $employee->id }}">
                    
                    <div class="mb-4">
                        <label for="workDate" class="block text-sm font-medium text-gray-700">Tanggal Kerja</label>
                        <input type="date" id="workDate" name="work_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="attendanceStatus" class="block text-sm font-medium text-gray-700">Status Kehadiran</label>
                        <select id="attendanceStatus" name="attendance_status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="present">Hadir</option>
                            <option value="late">Telat</option>
                            <option value="absent">Libur</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="basicSalary" class="block text-sm font-medium text-gray-700">Gaji Pokok</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="basicSalary" name="basic_salary" min="0" required
                                   value="{{ number_format($employee->daily_rate, 0, ',', '.') }}"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="mealAllowance" class="block text-sm font-medium text-gray-700">Uang Makan</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="mealAllowance" name="meal_allowance" min="0"
                                   value="10.000"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="attendanceBonus" class="block text-sm font-medium text-gray-700">Uang Absen</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="attendanceBonus" name="attendance_bonus"
                                   value="20.000"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Bonus kehadiran (positif) atau potongan keterlambatan (negatif)</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phoneAllowance" class="block text-sm font-medium text-gray-700">Uang Pulsa</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="phoneAllowance" name="phone_allowance" min="0"
                                   value="5.000"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="overtimeAmount" class="block text-sm font-medium text-gray-700">Lembur (Rupiah)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="overtimeAmount" name="overtime_amount" min="0"
                                   value="0"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Input manual jumlah lembur dalam rupiah</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="deductions" class="block text-sm font-medium text-gray-700">Potongan</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" id="deductions" name="deductions" min="0"
                                   value="0"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Potongan gaji (BPJS, pinjaman, dll)</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeDailySalaryModal()"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Salary Release Modal -->
    <div id="salaryReleaseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Buat Rilis Gaji</h3>
                    <button onclick="closeSalaryReleaseModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="salaryReleaseForm">
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    
                    <div class="mb-4">
                        <label for="periodStart" class="block text-sm font-medium text-gray-700">Periode Mulai</label>
                        <input type="date" id="periodStart" name="period_start" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="periodEnd" class="block text-sm font-medium text-gray-700">Periode Selesai</label>
                        <input type="date" id="periodEnd" name="period_end" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="releaseDeductions" class="block text-sm font-medium text-gray-700">Potongan</label>
                        <input type="number" id="releaseDeductions" name="deductions" step="0.01" min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="releaseNotes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea id="releaseNotes" name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div id="salaryPreview" class="mb-4 p-3 bg-gray-50 rounded hidden">
                        <h4 class="font-medium text-gray-900 mb-2">Preview Gaji:</h4>
                        <div id="previewContent"></div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeSalaryReleaseModal()"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="button" onclick="previewSalaryRelease()"
                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            Preview
                        </button>
                        <button type="submit"
                                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Buat Rilis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Format number with thousand separators (dots)
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Remove formatting from number (remove dots)
        function unformatNumber(str) {
            return str.toString().replace(/\./g, '');
        }
        
        // Format input field on blur
        function formatInputField(field) {
            const value = unformatNumber(field.value);
            if (value && !isNaN(value)) {
                field.value = formatNumber(value);
            }
        }
        
        // Unformat input field on focus
        function unformatInputField(field) {
            field.value = unformatNumber(field.value);
        }
        
        // Navigate to selected month/year
        function goToSelectedDate() {
            const selectedMonth = document.getElementById('month-selector').value;
            const selectedYear = document.getElementById('year-selector').value;
            const url = `{{ route('finance.employees.show', $employee->id) }}?year=${selectedYear}&month=${selectedMonth}`;
            console.log('Navigating to:', url);
            console.log('Selected Month:', selectedMonth, 'Selected Year:', selectedYear);
            window.location.href = url;
        }

        // Export salary data
        function exportSalaryData() {
            const url = `{{ route('finance.employees.export') }}?employee_id={{ $employee->id }}&year={{ $year }}&month={{ $month }}`;
            window.open(url, '_blank');
        }


        // Daily Salary Modal Functions
        function addDailySalary() {
            document.getElementById('modalTitle').textContent = 'Tambah Gaji Harian';
            document.getElementById('dailySalaryForm').reset();
            document.getElementById('salaryId').value = '';
            document.getElementById('workDate').value = '';
            document.getElementById('basicSalary').value = '{{ number_format($employee->daily_rate, 0, ",", ".") }}';
            document.getElementById('mealAllowance').value = '10.000';
            document.getElementById('attendanceBonus').value = '20.000';
            document.getElementById('phoneAllowance').value = '5.000';
            document.getElementById('overtimeAmount').value = '0';
            document.getElementById('deductions').value = '0';
            document.getElementById('dailySalaryModal').classList.remove('hidden');
        }

        function addDailySalaryForDate(date) {
            document.getElementById('modalTitle').textContent = 'Tambah Gaji Harian';
            document.getElementById('dailySalaryForm').reset();
            document.getElementById('salaryId').value = '';
            document.getElementById('workDate').value = date;
            document.getElementById('basicSalary').value = '{{ number_format($employee->daily_rate, 0, ",", ".") }}';
            document.getElementById('mealAllowance').value = '10.000';
            document.getElementById('attendanceBonus').value = '20.000';
            document.getElementById('phoneAllowance').value = '5.000';
            document.getElementById('overtimeAmount').value = '0';
            document.getElementById('deductions').value = '0';
            document.getElementById('dailySalaryModal').classList.remove('hidden');
        }

        function editDailySalary(salaryId) {
            document.getElementById('modalTitle').textContent = 'Edit Gaji Harian';
            
            fetch(`/finance/employees/{{ $employee->id }}/daily-salaries/${salaryId}`, {
                headers: {
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('salaryId').value = data.id;
                    document.getElementById('workDate').value = data.work_date;
                    document.getElementById('attendanceStatus').value = data.attendance_status;
                    document.getElementById('basicSalary').value = formatNumber(data.basic_salary);
                    document.getElementById('mealAllowance').value = formatNumber(data.meal_allowance || 15000);
                    document.getElementById('attendanceBonus').value = data.attendance_bonus >= 0 ? formatNumber(data.attendance_bonus) : data.attendance_bonus;
                    document.getElementById('phoneAllowance').value = formatNumber(data.phone_allowance || 5000);
                    document.getElementById('overtimeAmount').value = formatNumber(data.overtime_amount || 0);
                    document.getElementById('deductions').value = formatNumber(data.deductions || 0);
                    document.getElementById('notes').value = data.notes || '';
                    document.getElementById('dailySalaryModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data gaji');
                });
        }

        function deleteDailySalary(salaryId) {
            if (confirm('Apakah Anda yakin ingin menghapus data gaji ini?')) {
                fetch(`/finance/employees/{{ $employee->id }}/daily-salaries/${salaryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    
                    // Check if response is successful (200-299 range)
                    if (response.ok) {
                        // Try to parse JSON, but handle cases where response might not be JSON
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                // If not JSON, assume success if status is ok
                                console.log('Response is not JSON, but status is OK');
                                return { success: true, message: 'Gaji berhasil dihapus' };
                            }
                        });
                    } else {
                        // Handle error responses
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.message || 'Gagal menghapus gaji');
                            } catch (e) {
                                throw new Error(`Server error: ${response.status}`);
                            }
                        });
                    }
                })
                .then(data => {
                    console.log('Delete response data:', data);
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal menghapus gaji');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert(error.message || 'Gagal menghapus gaji');
                });
            }
        }

        function closeDailySalaryModal() {
            document.getElementById('dailySalaryModal').classList.add('hidden');
        }

        // Daily Salary Form Submit
        document.getElementById('dailySalaryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const salaryId = document.getElementById('salaryId').value;
            const isEdit = salaryId !== '';
            
            const url = isEdit
                ? `/finance/employees/{{ $employee->id }}/daily-salaries/${salaryId}`
                : `/finance/employees/{{ $employee->id }}/daily-salaries`;
            
            // Convert FormData to JSON and unformat numeric fields
            const data = {};
            const numericFields = ['basic_salary', 'meal_allowance', 'attendance_bonus', 'phone_allowance', 'overtime_amount', 'deductions'];
            formData.forEach((value, key) => {
                if (numericFields.includes(key)) {
                    data[key] = unformatNumber(value);
                } else {
                    data[key] = value;
                }
            });
            
            // Ensure employee_id is set for new entries
            if (!isEdit) {
                data.employee_id = {{ $employee->id }};
            }
            
            if (isEdit) {
                data._method = 'PUT';
            }
            
            // Debug logging
            console.log('Submitting data:', data);
            console.log('URL:', url);
            console.log('Is Edit:', isEdit);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Response is not JSON:', contentType);
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        throw new Error('Server returned HTML instead of JSON. Check server logs.');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    closeDailySalaryModal();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan gaji');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal menyimpan gaji: ' + error.message);
            });
        });

        // Salary Release Modal Functions
        function showSalaryReleaseModal() {
            // Set default period to selected month/year
            const selectedYear = {{ $year }};
            const selectedMonth = {{ $month }};
            const firstDay = new Date(selectedYear, selectedMonth - 1, 1);
            const lastDay = new Date(selectedYear, selectedMonth, 0);
            
            document.getElementById('periodStart').value = firstDay.toISOString().split('T')[0];
            document.getElementById('periodEnd').value = lastDay.toISOString().split('T')[0];
            document.getElementById('salaryReleaseModal').classList.remove('hidden');
        }

        function closeSalaryReleaseModal() {
            document.getElementById('salaryReleaseModal').classList.add('hidden');
            document.getElementById('salaryPreview').classList.add('hidden');
        }

        function previewSalaryRelease() {
            const formData = new FormData(document.getElementById('salaryReleaseForm'));
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            const params = new URLSearchParams(data);
            fetch(`/finance/api/employees/{{ $employee->id }}/unreleased-salaries?${params}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    // Store data globally for deduction calculation
                    window.currentSalaryData = data;
                    updatePreviewWithDeductions();
                    document.getElementById('salaryPreview').classList.remove('hidden');
                } else {
                    alert('Tidak ada gaji yang dapat dirilis untuk periode ini');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat preview gaji');
            });
        }

        // Function to update preview with current deductions
        function updatePreviewWithDeductions() {
            if (!window.currentSalaryData) return;
            
            const data = window.currentSalaryData;
            const deductions = parseFloat(document.getElementById('releaseDeductions').value) || 0;
            const netAmount = data.total_amount - deductions;
            
            document.getElementById('previewContent').innerHTML = `
                <div class="text-sm">
                    <p><strong>Jumlah Hari Kerja:</strong> ${data.count} hari</p>
                    <p><strong>Total Gaji:</strong> ${data.formatted_total}</p>
                    <p><strong>Potongan:</strong> Rp ${new Intl.NumberFormat('id-ID').format(deductions)}</p>
                    <p><strong>Gaji Bersih:</strong> Rp ${new Intl.NumberFormat('id-ID').format(netAmount)}</p>
                </div>
            `;
        }

        // Add event listener for deductions input
        document.addEventListener('DOMContentLoaded', function() {
            const deductionsInput = document.getElementById('releaseDeductions');
            if (deductionsInput) {
                deductionsInput.addEventListener('input', function() {
                    updatePreviewWithDeductions();
                });
            }
        });

        // Salary Release Form Submit
        document.getElementById('salaryReleaseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Debug: Log the data being sent
            console.log('Sending data:', data);
            
            fetch(`/finance/employees/{{ $employee->id }}/salary-releases`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Debug: Log response details
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // If not JSON, try to get text to see what's returned
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        // Check if it's an HTML error page
                        if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                            console.error('Received HTML instead of JSON. Possible error page.');
                            throw new Error('Server returned HTML instead of JSON. Check server logs.');
                        }
                        // Try to parse as JSON anyway
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                        }
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    closeSalaryReleaseModal();
                    loadSalaryReleases();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal membuat rilis gaji');
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                alert('Gagal membuat rilis gaji: ' + error.message);
            });
        });

        // Load salary releases
        function loadSalaryReleases() {
            fetch(`/finance/api/employees/{{ $employee->id }}/salary-releases`, {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                displaySalaryReleases(data);
            })
            .catch(error => {
                console.error('Error loading salary releases:', error);
                document.getElementById('salary-releases-list').innerHTML = `
                    <div class="text-center py-4 text-red-500">
                        <p>Gagal memuat data rilis gaji</p>
                    </div>
                `;
            });
        }

        function displaySalaryReleases(releases) {
            const container = document.getElementById('salary-releases-list');
            
            if (!releases || releases.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-money-check-alt text-4xl mb-4"></i>
                        <p>Belum ada rilis gaji untuk karyawan ini</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;

            releases.forEach(release => {
                const statusBadge = getStatusBadge(release.status);
                const actions = getReleaseActions(release);
                
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${release.release_code}</div>
                            <div class="text-sm text-gray-500">${formatDate(release.created_at)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${formatDate(release.period_start)} - ${formatDate(release.period_end)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${formatCurrency(release.net_amount)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${statusBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            ${actions}
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
        }

        function getStatusBadge(status) {
            const badges = {
                'draft': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>',
                'released': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Dirilis</span>',
                'paid': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Dibayar</span>'
            };
            return badges[status] || '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
        }

        function getReleaseActions(release) {
            let actions = `<a href="/finance/salary-releases/${release.id}" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-eye"></i></a>`;
            
            if (release.status === 'draft') {
                actions += `<button onclick="releaseGaji(${release.id})" class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-paper-plane"></i></button>`;
                actions += `<button onclick="deleteRelease(${release.id})" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>`;
            } else if (release.status === 'released') {
                actions += `<button onclick="markAsPaid(${release.id})" class="text-blue-600 hover:text-blue-900"><i class="fas fa-money-check-alt"></i></button>`;
            }
            
            return actions;
        }

        function releaseGaji(releaseId) {
            if (confirm('Rilis gaji ini? Setelah dirilis akan tercatat di cashflow.')) {
                fetch(`/finance/employees/{{ $employee->id }}/salary-releases/${releaseId}/release`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadSalaryReleases();
                        alert('Gaji berhasil dirilis dan tercatat di cashflow');
                    } else {
                        alert(data.message || 'Gagal merilis gaji');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal merilis gaji');
                });
            }
        }

        function markAsPaid(releaseId) {
            if (confirm('Tandai gaji ini sebagai dibayar?')) {
                fetch(`/finance/employees/{{ $employee->id }}/salary-releases/${releaseId}/mark-as-paid`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadSalaryReleases();
                        alert('Gaji berhasil ditandai sebagai dibayar');
                    } else {
                        alert(data.message || 'Gagal menandai gaji sebagai dibayar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal menandai gaji sebagai dibayar');
                });
            }
        }

        function deleteRelease(releaseId) {
            if (confirm('Apakah Anda yakin ingin menghapus rilis gaji ini?')) {
                fetch(`/finance/employees/{{ $employee->id }}/salary-releases/${releaseId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadSalaryReleases();
                        alert('Rilis gaji berhasil dihapus');
                    } else {
                        alert(data.message || 'Gagal menghapus rilis gaji');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal menghapus rilis gaji');
                });
            }
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('id-ID');
        }

        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        // Handle attendance status changes
        function handleAttendanceStatusChange() {
            const attendanceStatus = document.getElementById('attendanceStatus').value;
            const basicSalaryField = document.getElementById('basicSalary');
            const mealAllowanceField = document.getElementById('mealAllowance');
            const attendanceBonusField = document.getElementById('attendanceBonus');
            const phoneAllowanceField = document.getElementById('phoneAllowance');
            
            switch (attendanceStatus) {
                case 'present':
                    // Hadir: Gaji Pokok = database, Uang Makan = 10000, Uang Absen = 20000, Uang Pulsa = 5000
                    basicSalaryField.value = '{{ number_format($employee->daily_rate, 0, ",", ".") }}';
                    mealAllowanceField.value = '10.000';
                    attendanceBonusField.value = '20.000';
                    phoneAllowanceField.value = '5.000';
                    break;
                case 'late':
                    // Telat: Gaji Pokok = database, Uang Makan = 10000, Uang Absen = 0, Uang Pulsa = 5000
                    basicSalaryField.value = '{{ number_format($employee->daily_rate, 0, ",", ".") }}';
                    mealAllowanceField.value = '10.000';
                    attendanceBonusField.value = '0';
                    phoneAllowanceField.value = '5.000';
                    break;
                case 'absent':
                    // Libur: Gaji Pokok tetap sesuai database, yang lain 0
                    basicSalaryField.value = '{{ number_format($employee->daily_rate, 0, ",", ".") }}';
                    mealAllowanceField.value = '0';
                    attendanceBonusField.value = '0';
                    phoneAllowanceField.value = '0';
                    break;
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadSalaryReleases();
            
            // Add event listener for attendance status changes
            document.getElementById('attendanceStatus').addEventListener('change', handleAttendanceStatusChange);
            
            // Add formatting event listeners to numeric input fields
            const numericFields = ['basicSalary', 'mealAllowance', 'attendanceBonus', 'phoneAllowance', 'overtimeAmount', 'deductions'];
            numericFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('focus', function() {
                        unformatInputField(this);
                    });
                    field.addEventListener('blur', function() {
                        formatInputField(this);
                    });
                }
            });
        });
    </script>
</x-app-layout>