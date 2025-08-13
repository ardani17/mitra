<!-- Summary Report -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Laporan Ringkasan Karyawan</h3>
            <div class="flex space-x-2">
                <button onclick="printReport()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <button onclick="exportReport('excel')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $data['total_employees'] }}</div>
                <div class="text-sm text-blue-600">Total Karyawan</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $data['active_employees'] }}</div>
                <div class="text-sm text-green-600">Karyawan Aktif</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ $data['inactive_employees'] }}</div>
                <div class="text-sm text-red-600">Tidak Aktif</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $data['contract_expiring'] }}</div>
                <div class="text-sm text-yellow-600">Kontrak Berakhir</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Department Distribution -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-4">Distribusi per Departemen</h4>
                <div class="space-y-2">
                    @foreach($data['by_department'] as $department => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ $department }}</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($count / $data['total_employees']) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Employment Type Distribution -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-4">Tipe Karyawan</h4>
                <div class="space-y-2">
                    @foreach($data['by_employment_type'] as $type => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ ucfirst($type) }}</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($count / $data['total_employees']) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-lg font-semibold text-green-800">Rata-rata Gaji Harian</div>
                <div class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($data['average_daily_rate'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-lg font-semibold text-blue-800">Total Gaji Harian</div>
                <div class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($data['total_daily_rate'], 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Employee List -->
        <div class="mt-8">
            <h4 class="text-md font-medium text-gray-900 mb-4">Daftar Karyawan</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Karyawan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Departemen
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipe
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gaji Harian
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['employees'] as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-8 w-8 rounded-full object-cover" 
                                             src="{{ $employee->avatar_url }}" 
                                             alt="{{ $employee->name }}">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $employee->employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $employee->department }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {!! $employee->employment_type_badge !!}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $employee->formatted_daily_rate }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {!! $employee->status_badge !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>