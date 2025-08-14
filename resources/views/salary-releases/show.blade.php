<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Rilis Gaji') }} - {{ $salaryRelease->release_code }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $salaryRelease)
                    @if($salaryRelease->status === 'draft')
                        <a href="{{ route('finance.salary-releases.edit', $salaryRelease) }}" 
                           class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        @if(auth()->user()->role === 'direktur')
                            <form action="{{ route('finance.salary-releases.release', $salaryRelease) }}" 
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Rilis gaji ini? Setelah dirilis akan tercatat di cashflow.')">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-paper-plane mr-2"></i>Rilis Gaji
                                </button>
                            </form>
                        @endif
                    @endif
                    
                    @if($salaryRelease->status === 'released')
                        <form action="{{ route('finance.salary-releases.mark-as-paid', $salaryRelease) }}" 
                              method="POST" class="inline"
                              onsubmit="return confirm('Tandai gaji ini sebagai dibayar?')">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-money-check-alt mr-2"></i>Tandai Dibayar
                            </button>
                        </form>
                    @endif
                @endcan
                
                <a href="{{ route('finance.salary-releases.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Salary Release Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Rilis Gaji</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Kode Rilis:</span>
                                    <span class="text-sm text-gray-900">{{ $salaryRelease->release_code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Karyawan:</span>
                                    <span class="text-sm text-gray-900">{{ $salaryRelease->employee->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Periode:</span>
                                    <span class="text-sm text-gray-900">{{ $salaryRelease->period_label }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    <span class="text-sm">{!! $salaryRelease->status_badge !!}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Dibuat:</span>
                                    <span class="text-sm text-gray-900">{{ $salaryRelease->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($salaryRelease->released_at)
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500">Dirilis:</span>
                                        <span class="text-sm text-gray-900">{{ $salaryRelease->released_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500">Dirilis oleh:</span>
                                        <span class="text-sm text-gray-900">{{ $salaryRelease->releasedBy->name ?? '-' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Finansial</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Total Kotor:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $salaryRelease->formatted_total_amount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Potongan:</span>
                                    <span class="text-sm text-red-600">{{ $salaryRelease->formatted_deductions }}</span>
                                </div>
                                <div class="border-t pt-2">
                                    <div class="flex justify-between">
                                        <span class="text-base font-medium text-gray-900">Total Bersih:</span>
                                        <span class="text-base font-bold text-green-600">{{ $salaryRelease->formatted_net_amount }}</span>
                                    </div>
                                </div>
                                @if($salaryRelease->cashflowEntry)
                                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            <span class="text-sm text-green-800">Tercatat di Cashflow</span>
                                        </div>
                                        <p class="text-xs text-green-600 mt-1">
                                            Entry ID: {{ $salaryRelease->cashflowEntry->id }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($salaryRelease->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Catatan:</h4>
                            <p class="text-sm text-gray-600">{{ $salaryRelease->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daily Salaries Detail -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Detail Gaji Harian ({{ $salaryRelease->dailySalaries->count() }} hari)
                    </h3>
                    
                    @if($salaryRelease->dailySalaries->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status Kehadiran
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gaji Pokok
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tunjangan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lembur
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Potongan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($salaryRelease->dailySalaries->sortBy('work_date') as $salary)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $salary->work_date->format('d/m/Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $salary->day_name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {!! $salary->attendance_status_badge !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salary->formatted_basic_salary }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="text-xs space-y-1">
                                                    <div>Makan: {{ $salary->formatted_meal_allowance }}</div>
                                                    <div class="{{ $salary->attendance_bonus >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        Absen: {{ $salary->formatted_attendance_bonus }}
                                                    </div>
                                                    <div>Pulsa: {{ $salary->formatted_phone_allowance }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salary->formatted_overtime_amount }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                {{ $salary->formatted_deductions }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $salary->formatted_total_amount }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            Total:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $salaryRelease->formatted_total_amount }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>Tidak ada data gaji harian untuk rilis ini</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @can('delete', $salaryRelease)
                @if($salaryRelease->status === 'draft')
                    <div class="mt-4 sm:mt-6 flex justify-end">
                        <form action="{{ route('finance.salary-releases.destroy', $salaryRelease) }}"
                              method="POST" class="inline w-full sm:w-auto"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus rilis gaji ini? Semua gaji harian akan dikembalikan ke status belum dirilis.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full sm:w-auto bg-red-500 hover:bg-red-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                                <i class="fas fa-trash mr-1 sm:mr-2"></i>Hapus Rilis Gaji
                            </button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>