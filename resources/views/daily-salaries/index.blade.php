<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Gaji Harian') }}
            </h2>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('finance.daily-salaries.calendar') }}"
                   class="bg-green-500 hover:bg-green-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    <i class="fas fa-calendar-alt mr-1 sm:mr-2"></i>Kalender
                </a>
                <a href="{{ route('finance.daily-salaries.create') }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    <i class="fas fa-plus mr-1 sm:mr-2"></i>Input Gaji
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-blue-600 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500">Total Hari</p>
                                <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $summary['total_days'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500">Dikonfirmasi</p>
                                <p class="text-sm sm:text-xl lg:text-2xl font-semibold text-gray-900 break-words">
                                    Rp {{ number_format($summary['confirmed_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500">Draft</p>
                                <p class="text-sm sm:text-xl lg:text-2xl font-semibold text-gray-900 break-words">
                                    Rp {{ number_format($summary['draft_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-purple-600 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500">Total</p>
                                <p class="text-sm sm:text-xl lg:text-2xl font-semibold text-gray-900 break-words">
                                    Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('finance.daily-salaries.index') }}" class="space-y-3 sm:space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
                            <div>
                                <label for="start_date" class="block text-xs sm:text-sm font-medium text-gray-700">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs sm:text-sm font-medium text-gray-700">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                            </div>
                            <div>
                                <label for="employee_id" class="block text-xs sm:text-sm font-medium text-gray-700">Karyawan</label>
                                <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                                    <option value="">Semua Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                                </select>
                            </div>
                            <div>
                                <label for="released" class="block text-xs sm:text-sm font-medium text-gray-700">Rilis</label>
                                <select name="released" id="released" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                                    <option value="">Semua</option>
                                    <option value="yes" {{ request('released') === 'yes' ? 'selected' : '' }}>Sudah Dirilis</option>
                                    <option value="no" {{ request('released') === 'no' ? 'selected' : '' }}>Belum Dirilis</option>
                                </select>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-end space-y-2 sm:space-y-0 sm:space-x-2 sm:col-span-2 lg:col-span-1">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                                    <i class="fas fa-search mr-1 sm:mr-2"></i>Filter
                                </button>
                                <a href="{{ route('finance.daily-salaries.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daily Salaries Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    @if($dailySalaries->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="mb-3 sm:mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300">
                                <label for="select-all" class="text-xs sm:text-sm text-gray-700">Pilih Semua</label>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button" id="bulk-confirm-btn"
                                        class="bg-green-500 hover:bg-green-700 text-white font-medium py-2 px-3 sm:px-4 rounded disabled:opacity-50 text-sm sm:text-base"
                                        disabled>
                                    <i class="fas fa-check mr-1 sm:mr-2"></i>Konfirmasi Terpilih
                                </button>
                            </div>
                        </div>

                        <form id="bulk-confirm-form" method="POST" action="{{ route('finance.daily-salaries.bulk-confirm') }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="salary_ids" id="bulk-salary-ids">
                        </form>

                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" class="rounded border-gray-300">
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Karyawan
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jam Kerja
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lembur
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($dailySalaries as $salary)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                @if($salary->status === 'draft' && !$salary->is_released)
                                                    <input type="checkbox" class="salary-checkbox rounded border-gray-300"
                                                           value="{{ $salary->id }}">
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salary->work_date->format('d/m/Y') }}
                                                <div class="text-xs text-gray-500">{{ $salary->work_date->format('l') }}</div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ substr($salary->employee->name, 0, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $salary->employee->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $salary->employee->employee_code }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salary->hours_worked }} jam
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salary->overtime_hours }} jam
                                                @if($salary->overtime_amount > 0)
                                                    <br><span class="text-xs text-gray-500">{{ $salary->formatted_overtime_amount }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $salary->formatted_total_amount }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                {!! $salary->status_badge !!}
                                                @if($salary->is_released)
                                                    <br><span class="text-xs text-blue-600">Dirilis</span>
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('finance.daily-salaries.show', $salary) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('update', $salary)
                                                        <a href="{{ route('finance.daily-salaries.edit', $salary) }}"
                                                           class="text-yellow-600 hover:text-yellow-900">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $salary)
                                                        <form action="{{ route('finance.daily-salaries.destroy', $salary) }}"
                                                              method="POST" class="inline"
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data gaji ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-3">
                            @foreach($dailySalaries as $salary)
                                <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center space-x-3">
                                            @if($salary->status === 'draft' && !$salary->is_released)
                                                <input type="checkbox" class="salary-checkbox rounded border-gray-300"
                                                       value="{{ $salary->id }}">
                                            @endif
                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-xs font-medium text-gray-700">
                                                    {{ substr($salary->employee->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $salary->employee->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $salary->employee->employee_code }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">{{ $salary->formatted_total_amount }}</div>
                                            <div class="text-xs text-gray-500">{{ $salary->work_date->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                                        <div>
                                            <span class="text-gray-500">Jam Kerja:</span>
                                            <span class="font-medium">{{ $salary->hours_worked }} jam</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Lembur:</span>
                                            <span class="font-medium">{{ $salary->overtime_hours }} jam</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            {!! $salary->status_badge !!}
                                            @if($salary->is_released)
                                                <span class="text-xs text-blue-600">Dirilis</span>
                                            @endif
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('finance.daily-salaries.show', $salary) }}"
                                               class="text-indigo-600 hover:text-indigo-900 p-1">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            @can('update', $salary)
                                                <a href="{{ route('finance.daily-salaries.edit', $salary) }}"
                                                   class="text-yellow-600 hover:text-yellow-900 p-1">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $salary)
                                                <form action="{{ route('finance.daily-salaries.destroy', $salary) }}"
                                                      method="POST" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data gaji ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 sm:mt-6">
                            {{ $dailySalaries->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="text-gray-500 mb-4">
                                <i class="fas fa-calendar-times text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                                <p class="text-base sm:text-lg">Belum ada data gaji harian</p>
                            </div>
                            <a href="{{ route('finance.daily-salaries.create') }}"
                               class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                                <i class="fas fa-plus mr-1 sm:mr-2"></i>Input Gaji Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Bulk selection functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.salary-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        document.querySelectorAll('.salary-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.salary-checkbox:checked');
            const bulkConfirmBtn = document.getElementById('bulk-confirm-btn');
            
            if (checkedBoxes.length > 0) {
                bulkConfirmBtn.disabled = false;
            } else {
                bulkConfirmBtn.disabled = true;
            }
        }

        // Bulk confirm action
        document.getElementById('bulk-confirm-btn').addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.salary-checkbox:checked');
            const salaryIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (salaryIds.length === 0) {
                alert('Pilih minimal satu gaji untuk dikonfirmasi');
                return;
            }

            if (confirm(`Konfirmasi ${salaryIds.length} gaji harian?`)) {
                document.getElementById('bulk-salary-ids').value = JSON.stringify(salaryIds);
                document.getElementById('bulk-confirm-form').submit();
            }
        });

        // Auto-submit form on select change (desktop only)
        document.getElementById('employee_id').addEventListener('change', function() {
            if (window.innerWidth >= 640) {
                this.form.submit();
            }
        });
        
        document.getElementById('status').addEventListener('change', function() {
            if (window.innerWidth >= 640) {
                this.form.submit();
            }
        });
        
        document.getElementById('released').addEventListener('change', function() {
            if (window.innerWidth >= 640) {
                this.form.submit();
            }
        });
    </script>
    @endpush
</x-app-layout>