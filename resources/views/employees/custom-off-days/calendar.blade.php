<x-app-layout>
    <x-slot name="header">
        <!-- Mobile Header -->
        <div class="block sm:hidden">
            <div class="flex flex-col space-y-3">
                <div class="text-center">
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                        Kalender Kehadiran
                    </h2>
                    <p class="text-sm text-gray-600">
                        {{ $employee->name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $employee->employee_code }} | {{ $employee->position }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}?year={{ $year }}&month={{ $month }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                        <i class="fas fa-list mr-1"></i>Daftar
                    </a>
                    <a href="{{ route('finance.employees.show', $employee) }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden sm:flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kalender Kehadiran - {{ $employee->name }}
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
        <div class="max-w-2xl sm:max-w-4xl lg:max-w-6xl xl:max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period Filter & Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <!-- Mobile Layout -->
                    <div class="block sm:hidden space-y-4">
                        <!-- Period Filter -->
                        <form method="GET" action="{{ route('finance.employees.custom-off-days.calendar', $employee) }}" class="flex items-center space-x-2">
                            <select name="month" class="text-sm rounded border-gray-300 flex-1">
                                @foreach($monthOptions as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                        {{ $monthName }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="text-sm rounded border-gray-300 flex-1">
                                @foreach($yearOptions as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                        </form>
                        
                        <!-- Navigation Buttons -->
                        <div class="flex space-x-2">
                            <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}?year={{ $year }}&month={{ $month }}"
                               class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                <i class="fas fa-list mr-1"></i>Daftar
                            </a>
                            <a href="{{ route('finance.employees.custom-off-days.calendar', $employee) }}?year={{ $year }}&month={{ $month }}"
                               class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                <i class="fas fa-calendar mr-1"></i>Kalender
                            </a>
                        </div>
                        
                        <!-- Legend -->
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                                <span>Hadir</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded mr-1"></div>
                                <span>Telat</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                                <span>Libur</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                                <span>Sakit</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-gray-400 rounded mr-1"></div>
                                <span>Belum diisi</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop Layout -->
                    <div class="hidden sm:block">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center space-x-4">
                                <!-- Period Filter -->
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
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">
                                        <i class="fas fa-search mr-1"></i>Filter
                                    </button>
                                </form>
                                
                                <!-- Navigation Buttons -->
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}?year={{ $year }}&month={{ $month }}"
                                       class="bg-gray-500 hover:bg-gray-700 text-white py-1 px-3 rounded text-xs">
                                        <i class="fas fa-list mr-2"></i>Daftar
                                    </a>
                                    <a href="{{ route('finance.employees.custom-off-days.calendar', $employee) }}?year={{ $year }}&month={{ $month }}"
                                       class="bg-green-500 hover:bg-green-700 text-white py-1 px-3 rounded text-xs">
                                        <i class="fas fa-calendar mr-2"></i>Kalender
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Legend -->
                            <div class="flex items-center space-x-4 text-xs flex-wrap lg:justify-start lg:pl-6">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    <span>Hadir</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                                    <span>Telat</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                    <span>Libur</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    <span>Sakit</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-400 rounded mr-2"></div>
                                    <span>Belum diisi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Layout: Calendar and Statistics Side by Side -->
            <div class="lg:grid lg:grid-cols-3 lg:gap-6 space-y-6 lg:space-y-0">
                <!-- Calendar -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold">{{ $monthOptions[$month] }} {{ $year }}</h3>
                            <p class="text-sm text-gray-600">Klik tanggal untuk mengatur status kehadiran</p>
                        </div>

                        <!-- Responsive Calendar Grid -->
                        <div class="w-full max-w-sm sm:max-w-md lg:max-w-4xl xl:max-w-5xl mx-auto lg:px-12 xl:px-16">
                            <!-- Day Headers - Force horizontal layout with responsive sizing -->
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 2px; background-color: #e5e7eb;">
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Su</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Mo</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Tu</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">We</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Th</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Fr</div>
                                <div class="h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base font-medium text-gray-600 bg-gray-50 border border-gray-300">Sa</div>
                            </div>

                            <!-- Calendar Days - Force horizontal layout with responsive sizing -->
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background-color: #e5e7eb;">
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
                                    
                                    // Find attendance status for this date
                                    $attendanceStatus = null;
                                    foreach($calendar as $calendarDay) {
                                        if($calendarDay['date']->isSameDay($currentDate)) {
                                            $attendanceStatus = $calendarDay['attendance_status'];
                                            break;
                                        }
                                    }
                                    
                                    // Determine responsive classes based on attendance status
                                    $baseClasses = 'h-8 sm:h-10 lg:h-14 xl:h-16 flex items-center justify-center text-xs sm:text-sm lg:text-base xl:text-lg cursor-pointer transition-all duration-200 border border-gray-300';
                                    
                                    if (!$isCurrentMonth) {
                                        $classes = $baseClasses . ' text-gray-400 bg-gray-50';
                                    } else {
                                        // Color coding based on attendance status
                                        switch($attendanceStatus) {
                                            case 'present':
                                                $classes = $baseClasses . ' bg-green-500 text-white border-green-600 hover:bg-green-600'; // Hijau - Hadir
                                                break;
                                            case 'late':
                                                $classes = $baseClasses . ' bg-yellow-500 text-white border-yellow-600 hover:bg-yellow-600'; // Kuning - Telat
                                                break;
                                            case 'absent':
                                                $classes = $baseClasses . ' bg-red-500 text-white border-red-600 hover:bg-red-600'; // Merah - Libur
                                                break;
                                            case 'sick':
                                                $classes = $baseClasses . ' bg-blue-500 text-white border-blue-600 hover:bg-blue-600'; // Biru - Sakit
                                                break;
                                            default:
                                                $classes = $baseClasses . ' bg-gray-400 text-white border-gray-500 hover:bg-gray-500'; // Abu-abu - Belum diisi
                                                break;
                                        }
                                    }
                                    
                                    if ($isToday) {
                                        $classes .= ' ring-2 ring-blue-500 ring-inset';
                                    }
                                    
                                    // Status text for tooltip
                                    $statusText = match($attendanceStatus) {
                                        'present' => 'Hadir',
                                        'late' => 'Telat',
                                        'absent' => 'Libur',
                                        'sick' => 'Sakit',
                                        default => 'Belum diisi'
                                    };
                                    
                                    $dayCount++;
                                @endphp

                                <div class="{{ $classes }}"
                                     @if($isCurrentMonth)
                                     onclick="showAttendanceModal('{{ $currentDate->format('Y-m-d') }}', '{{ $attendanceStatus }}')"
                                     title="{{ $statusText }} - {{ $currentDate->format('d/m/Y') }}"
                                     @endif>
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

                <!-- Statistics - Desktop: Sidebar, Mobile: Below Calendar -->
                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h4 class="text-lg font-semibold mb-4 text-center lg:text-left lg:pl-6">Statistik Kehadiran</h4>
                    @php
                        $totalDaysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                        $presentCount = 0;
                        $lateCount = 0;
                        $absentCount = 0;
                        $sickCount = 0;
                        $emptyCount = 0;
                        
                        foreach($calendar as $calendarDay) {
                            switch($calendarDay['attendance_status']) {
                                case 'present':
                                    $presentCount++;
                                    break;
                                case 'late':
                                    $lateCount++;
                                    break;
                                case 'absent':
                                    $absentCount++;
                                    break;
                                case 'sick':
                                    $sickCount++;
                                    break;
                                default:
                                    $emptyCount++;
                                    break;
                            }
                        }
                    @endphp
                    
                        <!-- Mobile: 5 columns, Desktop: 1 column -->
                        <div class="grid grid-cols-5 lg:grid-cols-1 gap-2 lg:gap-3 text-center lg:text-left text-sm">
                            <div class="p-3 lg:p-4 bg-green-100 rounded-lg">
                                <div class="text-lg lg:text-xl font-bold text-green-800">{{ $presentCount }}</div>
                                <div class="text-xs lg:text-sm text-green-600">Hadir</div>
                            </div>
                            <div class="p-3 lg:p-4 bg-yellow-100 rounded-lg">
                                <div class="text-lg lg:text-xl font-bold text-yellow-800">{{ $lateCount }}</div>
                                <div class="text-xs lg:text-sm text-yellow-600">Telat</div>
                            </div>
                            <div class="p-3 lg:p-4 bg-red-100 rounded-lg">
                                <div class="text-lg lg:text-xl font-bold text-red-800">{{ $absentCount }}</div>
                                <div class="text-xs lg:text-sm text-red-600">Libur</div>
                            </div>
                            <div class="p-3 lg:p-4 bg-blue-100 rounded-lg">
                                <div class="text-lg lg:text-xl font-bold text-blue-800">{{ $sickCount }}</div>
                                <div class="text-xs lg:text-sm text-blue-600">Sakit</div>
                            </div>
                            <div class="p-3 lg:p-4 bg-gray-100 rounded-lg">
                                <div class="text-lg lg:text-xl font-bold text-gray-800">{{ $emptyCount }}</div>
                                <div class="text-xs lg:text-sm text-gray-600">Kosong</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Status Modal -->
            <div id="attendanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">
                                Pilih Status Kehadiran
                            </h3>
                            <button type="button" onclick="closeAttendanceModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600" id="modalDate"></p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" onclick="setAttendanceStatus('present')"
                                    class="flex items-center justify-center p-3 border-2 border-green-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-6 h-6 bg-green-500 rounded-full mx-auto mb-2"></div>
                                    <span class="text-sm font-medium text-green-700">Hadir</span>
                                </div>
                            </button>
                            
                            <button type="button" onclick="setAttendanceStatus('late')"
                                    class="flex items-center justify-center p-3 border-2 border-yellow-200 rounded-lg hover:border-yellow-400 hover:bg-yellow-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-6 h-6 bg-yellow-500 rounded-full mx-auto mb-2"></div>
                                    <span class="text-sm font-medium text-yellow-700">Telat</span>
                                </div>
                            </button>
                            
                            <button type="button" onclick="setAttendanceStatus('absent')"
                                    class="flex items-center justify-center p-3 border-2 border-red-200 rounded-lg hover:border-red-400 hover:bg-red-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-6 h-6 bg-red-500 rounded-full mx-auto mb-2"></div>
                                    <span class="text-sm font-medium text-red-700">Libur</span>
                                </div>
                            </button>
                            
                            <button type="button" onclick="setAttendanceStatus('sick')"
                                    class="flex items-center justify-center p-3 border-2 border-blue-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-6 h-6 bg-blue-500 rounded-full mx-auto mb-2"></div>
                                    <span class="text-sm font-medium text-blue-700">Sakit</span>
                                </div>
                            </button>
                        </div>
                        
                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" onclick="clearAttendanceStatus()"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded hover:bg-gray-300 transition-colors">
                                Hapus Status
                            </button>
                            <button type="button" onclick="closeAttendanceModal()"
                                    class="px-4 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden forms for backward compatibility -->
            <form id="quickToggleForm" action="{{ route('finance.employees.custom-off-days.quick-add', $employee) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="toggleDate" name="off_date">
                <input type="hidden" id="toggleReason" name="reason" value="Libur custom">
            </form>

            <form id="quickRemoveForm" action="{{ route('finance.employees.custom-off-days.quick-remove', $employee) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="removeDate" name="off_date">
            </form>

            <!-- Attendance Status Form -->
            <form id="attendanceStatusForm" action="{{ route('finance.employees.attendance-status.update', $employee) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="attendanceDate" name="work_date">
                <input type="hidden" id="attendanceStatus" name="attendance_status">
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentDate = null;
        let currentStatus = null;

        function showAttendanceModal(date, status) {
            currentDate = date;
            currentStatus = status;
            
            // Format date for display
            const dateObj = new Date(date);
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            
            const modalDateElement = document.getElementById('modalDate');
            const modalElement = document.getElementById('attendanceModal');
            
            if (!modalDateElement || !modalElement) {
                return;
            }
            
            modalDateElement.textContent = formattedDate;
            modalElement.classList.remove('hidden');
            
            // Highlight current status
            highlightCurrentStatus(status);
        }

        function closeAttendanceModal() {
            document.getElementById('attendanceModal').classList.add('hidden');
            currentDate = null;
            currentStatus = null;
        }

        function highlightCurrentStatus(status) {
            // Remove all highlights first
            const buttons = document.querySelectorAll('#attendanceModal button[onclick^="setAttendanceStatus"]');
            buttons.forEach(btn => {
                btn.classList.remove('ring-2', 'ring-offset-2');
                btn.classList.remove('ring-green-500', 'ring-yellow-500', 'ring-red-500', 'ring-blue-500');
            });
            
            // Highlight current status
            if (status) {
                const statusButton = document.querySelector(`button[onclick="setAttendanceStatus('${status}')"]`);
                if (statusButton) {
                    statusButton.classList.add('ring-2', 'ring-offset-2');
                    switch(status) {
                        case 'present':
                            statusButton.classList.add('ring-green-500');
                            break;
                        case 'late':
                            statusButton.classList.add('ring-yellow-500');
                            break;
                        case 'absent':
                            statusButton.classList.add('ring-red-500');
                            break;
                        case 'sick':
                            statusButton.classList.add('ring-blue-500');
                            break;
                    }
                }
            }
        }

        function setAttendanceStatus(status) {
            if (!currentDate) return;
            
            // Show loading state
            const modal = document.getElementById('attendanceModal');
            const originalContent = modal.innerHTML;
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
                        <p class="text-gray-600">Menyimpan status kehadiran...</p>
                    </div>
                </div>
            `;
            
            // Send AJAX request
            fetch('{{ route("finance.employees.attendance-status.update", $employee) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    work_date: currentDate,
                    attendance_status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    modal.innerHTML = `
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="text-center py-8">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check text-green-600 text-xl"></i>
                                </div>
                                <p class="text-gray-800 font-medium mb-2">Status kehadiran berhasil disimpan!</p>
                                <p class="text-gray-600 text-sm">${data.message}</p>
                            </div>
                        </div>
                    `;
                    
                    // Reload page after 1.5 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Gagal menyimpan status kehadiran');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modal.innerHTML = `
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="text-center py-8">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-times text-red-600 text-xl"></i>
                            </div>
                            <p class="text-gray-800 font-medium mb-2">Gagal menyimpan status kehadiran</p>
                            <p class="text-gray-600 text-sm mb-4">${error.message}</p>
                            <button onclick="closeAttendanceModal()" class="px-4 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                                Tutup
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        function clearAttendanceStatus() {
            if (!currentDate) return;
            
            if (confirm('Apakah Anda yakin ingin menghapus status kehadiran untuk tanggal ini?')) {
                // Show loading state
                const modal = document.getElementById('attendanceModal');
                modal.innerHTML = `
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="text-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500 mx-auto mb-4"></div>
                            <p class="text-gray-600">Menghapus status kehadiran...</p>
                        </div>
                    </div>
                `;
                
                // Send DELETE request to remove daily salary for this date
                fetch('{{ route("finance.employees.attendance-status.delete", $employee) }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        work_date: currentDate
                    })
                })
                .then(response => {
                    // Check if response is successful (200-299 range)
                    if (response.ok) {
                        // Try to parse JSON, but handle cases where response might not be JSON
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                // If not JSON, assume success if status is ok
                                return { success: true, message: 'Status kehadiran berhasil dihapus' };
                            }
                        });
                    } else {
                        // Handle error responses
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.message || 'Gagal menghapus status kehadiran');
                            } catch (e) {
                                throw new Error(`Server error: ${response.status}`);
                            }
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        modal.innerHTML = `
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="text-center py-8">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-check text-green-600 text-xl"></i>
                                    </div>
                                    <p class="text-gray-800 font-medium mb-2">Status kehadiran berhasil dihapus!</p>
                                    <p class="text-gray-600 text-sm">${data.message}</p>
                                </div>
                            </div>
                        `;
                        
                        // Reload page after 1.5 seconds
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Gagal menghapus status kehadiran');
                    }
                })
                .catch(error => {
                    modal.innerHTML = `
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="text-center py-8">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-times text-red-600 text-xl"></i>
                                </div>
                                <p class="text-gray-800 font-medium mb-2">Gagal menghapus status kehadiran</p>
                                <p class="text-gray-600 text-sm mb-4">${error.message}</p>
                                <button onclick="closeAttendanceModal()" class="px-4 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
        }

        // Legacy function for backward compatibility
        function toggleOffDay(date, hasOffDay) {
            showAttendanceModal(date, hasOffDay ? 'absent' : null);
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('attendanceModal');
            if (event.target === modal) {
                closeAttendanceModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAttendanceModal();
            }
        });

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Make functions globally available
            window.showAttendanceModal = showAttendanceModal;
        });
    </script>
    @endpush
</x-app-layout>