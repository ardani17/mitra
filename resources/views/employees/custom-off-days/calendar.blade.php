<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kalender Hari Libur - {{ $employee->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $employee->employee_code }} | {{ $employee->position }} | {{ $employee->department }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}?year={{ $year }}&month={{ $month }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-list mr-2"></i>Daftar
                </a>
                <a href="{{ route('finance.employees.show', $employee) }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <!-- Period Filter & Legend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <form method="GET" action="{{ route('finance.employees.custom-off-days.calendar', $employee) }}" class="flex items-center space-x-2">
                            <select name="month" class="text-sm rounded border-gray-300">
                                @foreach($monthOptions as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                        {{ $monthName }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="text-sm rounded border-gray-300">
                                @foreach($yearOptions as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                Filter
                            </button>
                        </form>
                        
                        <div class="flex items-center space-x-3 text-xs">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                                <span>Kerja</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                                <span>Libur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-semibold">{{ $monthOptions[$month] }} {{ $year }}</h3>
                        <p class="text-sm text-gray-600">Klik tanggal untuk toggle hari libur</p>
                    </div>

                    <!-- Calendar Grid with explicit CSS -->
                    <div class="w-full max-w-sm mx-auto">
                        <!-- Day Headers - Force horizontal layout -->
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; margin-bottom: 1px; background-color: #e5e7eb;">
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Su</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Mo</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Tu</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">We</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Th</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Fr</div>
                            <div style="height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 1px solid #e5e7eb;">Sa</div>
                        </div>

                        <!-- Calendar Days - Force horizontal layout -->
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: #e5e7eb;">
                            @php
                                $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
                                $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                                $endOfMonth = $startOfMonth->copy()->endOfMonth();
                                $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                                
                                $currentDate = $startOfCalendar->copy();
                                $dayCount = 0;
                            @endphp

                            @while($currentDate <= $endOfCalendar)
                                @php
                                    $isCurrentMonth = $currentDate->month == $month;
                                    $isToday = $currentDate->isToday();
                                    
                                    // Find custom off day for this date
                                    $hasCustomOff = false;
                                    foreach($calendar as $calendarDay) {
                                        if($calendarDay['date']->isSameDay($currentDate) && $calendarDay['has_custom_off']) {
                                            $hasCustomOff = true;
                                            break;
                                        }
                                    }
                                    
                                    // Determine classes
                                    $baseStyle = 'height: 32px; display: flex; align-items: center; justify-content: center; font-size: 14px; cursor: pointer; transition: all 0.2s; border: 1px solid #e5e7eb;';
                                    
                                    if (!$isCurrentMonth) {
                                        $style = $baseStyle . ' color: #d1d5db; background-color: #f9fafb;';
                                    } else {
                                        if ($hasCustomOff) {
                                            $style = $baseStyle . ' background-color: #ef4444; color: white; border-color: #dc2626;';
                                        } else {
                                            $style = $baseStyle . ' background-color: #10b981; color: white; border-color: #059669;';
                                        }
                                    }
                                    
                                    if ($isToday) {
                                        $style .= ' box-shadow: inset 0 0 0 2px #3b82f6;';
                                    }
                                    
                                    $dayCount++;
                                @endphp

                                <div style="{{ $style }}"
                                     @if($isCurrentMonth)
                                     onclick="toggleOffDay('{{ $currentDate->format('Y-m-d') }}', {{ $hasCustomOff ? 'true' : 'false' }})"
                                     title="{{ $hasCustomOff ? 'Hari Libur' : 'Hari Kerja' }} - {{ $currentDate->format('d/m/Y') }}"
                                     @endif
                                     onmouseover="this.style.opacity='0.8'"
                                     onmouseout="this.style.opacity='1'">
                                    {{ $currentDate->day }}
                                </div>

                                @php
                                    $currentDate->addDay();
                                @endphp
                            @endwhile
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    @php
                        $totalDaysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                        $offDaysCount = 0;
                        foreach($calendar as $calendarDay) {
                            if($calendarDay['has_custom_off']) {
                                $offDaysCount++;
                            }
                        }
                        $workingDaysCount = $totalDaysInMonth - $offDaysCount;
                    @endphp
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="p-3 bg-gray-100 rounded">
                            <div class="text-xl font-bold text-gray-800">{{ $totalDaysInMonth }}</div>
                            <div class="text-xs text-gray-600">Total Hari</div>
                        </div>
                        <div class="p-3 bg-green-100 rounded">
                            <div class="text-xl font-bold text-green-800">{{ $workingDaysCount }}</div>
                            <div class="text-xs text-green-600">Hari Kerja</div>
                        </div>
                        <div class="p-3 bg-red-100 rounded">
                            <div class="text-xl font-bold text-red-800">{{ $offDaysCount }}</div>
                            <div class="text-xs text-red-600">Hari Libur</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden forms -->
            <form id="quickToggleForm" action="{{ route('finance.employees.custom-off-days.quick-add', $employee) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="toggleDate" name="off_date">
                <input type="hidden" id="toggleReason" name="reason" value="Libur custom">
            </form>

            <form id="quickRemoveForm" action="{{ route('finance.employees.custom-off-days.quick-remove', $employee) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="removeDate" name="off_date">
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleOffDay(date, hasOffDay) {
            if (hasOffDay) {
                if (confirm('Hapus hari libur pada tanggal ' + date + '?')) {
                    document.getElementById('removeDate').value = date;
                    document.getElementById('quickRemoveForm').submit();
                }
            } else {
                const reason = prompt('Alasan libur (opsional):', 'Libur custom');
                if (reason !== null) {
                    document.getElementById('toggleDate').value = date;
                    document.getElementById('toggleReason').value = reason || 'Libur custom';
                    document.getElementById('quickToggleForm').submit();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>