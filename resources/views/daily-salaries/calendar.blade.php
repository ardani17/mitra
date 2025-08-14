<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                <i class="fas fa-calendar-alt mr-2"></i>
                Kalender Gaji Harian - {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </h2>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('finance.daily-salaries.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-list mr-1"></i>
                    Daftar Gaji
                </a>
                <a href="{{ route('finance.daily-salaries.create') }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Gaji
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <!-- Filter Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 sm:mb-6">
                        <div>
                            <label for="employee_filter" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm"
                                    id="employee_filter" name="employee_id">
                                <option value="">Semua Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $selectedEmployee && $selectedEmployee->id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="month_filter" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm"
                                    id="month_filter" name="month">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="year_filter" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm"
                                    id="year_filter" name="year">
                                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button"
                                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-xs sm:text-sm"
                                    id="apply_filter">
                                <i class="fas fa-filter mr-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Section - Desktop -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Minggu</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Senin</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Selasa</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Rabu</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Kamis</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Jumat</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sabtu</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $currentDate = $startDate->copy();
                                    $currentDate->startOfWeek(\Carbon\Carbon::SUNDAY);
                                @endphp
                                
                                @while($currentDate->lte($endDate->copy()->endOfWeek(\Carbon\Carbon::SATURDAY)))
                                    <tr>
                                        @for($day = 0; $day < 7; $day++)
                                            @php
                                                $isCurrentMonth = $currentDate->month == $month;
                                                $dateKey = $currentDate->format('Y-m-d');
                                                $dayData = [];
                                                
                                                if ($selectedEmployee) {
                                                    $key = $selectedEmployee->id . '-' . $dateKey;
                                                    if (isset($dailySalaries[$key])) {
                                                        $dayData[] = $dailySalaries[$key];
                                                    }
                                                } else {
                                                    foreach($dailySalaries as $salary) {
                                                        if ($salary->work_date->format('Y-m-d') == $dateKey) {
                                                            $dayData[] = $salary;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            
                                            <td class="calendar-day {{ !$isCurrentMonth ? 'text-gray-400' : '' }} border-r border-gray-200 p-2 align-top"
                                                data-date="{{ $dateKey }}"
                                                style="height: 120px; min-width: 120px;">
                                                
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="font-bold text-sm">{{ $currentDate->day }}</span>
                                                    @if($isCurrentMonth)
                                                        <a href="{{ route('finance.daily-salaries.create', ['date' => $dateKey]) }}"
                                                           class="text-blue-600 hover:text-blue-800 text-xs p-1"
                                                           title="Tambah gaji untuk tanggal ini">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                
                                                @if(!empty($dayData))
                                                    <div class="space-y-1">
                                                        @foreach($dayData as $salary)
                                                            <div class="salary-entry p-1 rounded text-xs cursor-pointer hover:opacity-80 {{ $salary->status == 'confirmed' ? 'bg-green-100 border border-green-200' : 'bg-yellow-100 border border-yellow-200' }}">
                                                                <div class="font-bold truncate">{{ $salary->employee->name }}</div>
                                                                <div class="text-xs">{{ $salary->formatted_total_amount }}</div>
                                                                <div>
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $salary->status == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                        {{ $salary->status == 'confirmed' ? 'Konfirmasi' : 'Draft' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            @php $currentDate->addDay(); @endphp
                                        @endfor
                                    </tr>
                                @endwhile
                            </tbody>
                        </table>
                    </div>

                    <!-- Calendar Section - Mobile -->
                    <div class="lg:hidden">
                        @php
                            $currentDate = $startDate->copy();
                            $currentDate->startOfWeek(\Carbon\Carbon::SUNDAY);
                        @endphp
                        
                        @while($currentDate->lte($endDate->copy()->endOfWeek(\Carbon\Carbon::SATURDAY)))
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">
                                    Minggu {{ $currentDate->format('d M') }} - {{ $currentDate->copy()->endOfWeek(\Carbon\Carbon::SATURDAY)->format('d M Y') }}
                                </h3>
                                <div class="grid grid-cols-1 gap-2">
                                    @for($day = 0; $day < 7; $day++)
                                        @php
                                            $isCurrentMonth = $currentDate->month == $month;
                                            $dateKey = $currentDate->format('Y-m-d');
                                            $dayData = [];
                                            
                                            if ($selectedEmployee) {
                                                $key = $selectedEmployee->id . '-' . $dateKey;
                                                if (isset($dailySalaries[$key])) {
                                                    $dayData[] = $dailySalaries[$key];
                                                }
                                            } else {
                                                foreach($dailySalaries as $salary) {
                                                    if ($salary->work_date->format('Y-m-d') == $dateKey) {
                                                        $dayData[] = $salary;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="border border-gray-200 rounded-lg p-3 {{ !$isCurrentMonth ? 'bg-gray-50' : 'bg-white' }}">
                                            <div class="flex justify-between items-center mb-2">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-sm {{ !$isCurrentMonth ? 'text-gray-400' : 'text-gray-900' }}">
                                                        {{ $currentDate->format('l, d M') }}
                                                    </span>
                                                </div>
                                                @if($isCurrentMonth)
                                                    <a href="{{ route('finance.daily-salaries.create', ['date' => $dateKey]) }}"
                                                       class="text-blue-600 hover:text-blue-800 p-1"
                                                       title="Tambah gaji untuk tanggal ini">
                                                        <i class="fas fa-plus text-sm"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            
                                            @if(!empty($dayData))
                                                <div class="space-y-2">
                                                    @foreach($dayData as $salary)
                                                        <div class="salary-entry p-2 rounded border cursor-pointer hover:shadow-sm {{ $salary->status == 'confirmed' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                                                            <div class="flex justify-between items-start">
                                                                <div class="flex-1">
                                                                    <div class="font-medium text-sm">{{ $salary->employee->name }}</div>
                                                                    <div class="text-sm text-gray-600">{{ $salary->formatted_total_amount }}</div>
                                                                </div>
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $salary->status == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                    {{ $salary->status == 'confirmed' ? 'Konfirmasi' : 'Draft' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 italic">Tidak ada data gaji</div>
                                            @endif
                                        </div>
                                        
                                        @php $currentDate->addDay(); @endphp
                                    @endfor
                                </div>
                            </div>
                        @endwhile
                    </div>

                    <!-- Legend -->
                    <div class="mt-4 sm:mt-6">
                        <div class="flex flex-wrap gap-3 sm:gap-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-100 border border-green-200 rounded mr-2"></div>
                                <span class="text-xs sm:text-sm text-gray-700">Dikonfirmasi</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-yellow-100 border border-yellow-200 rounded mr-2"></div>
                                <span class="text-xs sm:text-sm text-gray-700">Draft</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        document.getElementById('apply_filter').addEventListener('click', function() {
            const employeeId = document.getElementById('employee_filter').value;
            const month = document.getElementById('month_filter').value;
            const year = document.getElementById('year_filter').value;
            
            const params = new URLSearchParams();
            if (employeeId) params.append('employee_id', employeeId);
            params.append('month', month);
            params.append('year', year);
            
            window.location.href = `{{ route('finance.daily-salaries.calendar') }}?${params.toString()}`;
        });

        // Make salary entries clickable
        document.querySelectorAll('.salary-entry').forEach(function(entry) {
            entry.addEventListener('click', function() {
                // You can add functionality to view/edit salary details here
                console.log('Salary entry clicked');
            });
        });
    });
    </script>
    @endpush
</x-app-layout>