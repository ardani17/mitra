<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Jadwal Kerja - {{ $employee->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.work-schedules.edit', [$employee, $workSchedule]) }}" 
                   class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('finance.employees.work-schedules.index', $employee) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Content Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column - Schedule Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Jadwal</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Karyawan</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $employee->name }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tipe Jadwal</label>
                                    <div class="mt-1">
                                        @switch($workSchedule->schedule_type)
                                            @case('standard')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    Standard (Senin-Jumat)
                                                </span>
                                                @break
                                            @case('custom')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Custom Fixed Days
                                                </span>
                                                @break
                                            @case('flexible')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Flexible Schedule
                                                </span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>

                                @if($workSchedule->schedule_type === 'custom')
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Hari Kerja</label>
                                    <div class="mt-1">
                                        @php
                                            $workDays = json_decode($workSchedule->work_days, true) ?? [];
                                            $dayNames = [
                                                'monday' => 'Senin',
                                                'tuesday' => 'Selasa', 
                                                'wednesday' => 'Rabu',
                                                'thursday' => 'Kamis',
                                                'friday' => 'Jumat',
                                                'saturday' => 'Sabtu',
                                                'sunday' => 'Minggu'
                                            ];
                                        @endphp
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($workDays as $day)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $dayNames[$day] ?? $day }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($workSchedule->schedule_type === 'flexible')
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Target Hari Kerja/Bulan</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $workSchedule->target_work_days }} hari</p>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <div class="mt-1">
                                        @if($workSchedule->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-pause-circle mr-1"></i>Tidak Aktif
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Berlaku Mulai</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $workSchedule->effective_from ? $workSchedule->effective_from->format('d/m/Y') : '-' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Berlaku Sampai</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $workSchedule->effective_until ? $workSchedule->effective_until->format('d/m/Y') : 'Tidak terbatas' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $workSchedule->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Diupdate</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $workSchedule->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Additional Info -->
                        <div class="space-y-6">
                            @if($workSchedule->notes)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Catatan</h4>
                                <p class="text-sm text-gray-700">{{ $workSchedule->notes }}</p>
                            </div>
                            @endif

                            @if($workSchedule->schedule_type === 'flexible' && $workSchedule->customOffDays->count() > 0)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">Hari Libur Custom</h4>
                                    <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}" 
                                       class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-xs">
                                        Kelola
                                    </a>
                                </div>
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($workSchedule->customOffDays->take(5) as $offDay)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                                    {{ $offDay->off_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-900">
                                                    {{ $offDay->reason ?? '-' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($workSchedule->customOffDays->count() > 5)
                                    <p class="text-xs text-gray-500 mt-2 px-3">
                                        Dan {{ $workSchedule->customOffDays->count() - 5 }} hari libur lainnya...
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Info Box -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-800">Informasi</h4>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                <li><strong>Standard:</strong> Senin-Jumat kerja, Sabtu-Minggu libur</li>
                                                <li><strong>Custom:</strong> Pilih hari kerja sesuai kebutuhan</li>
                                                <li><strong>Flexible:</strong> Target hari kerja per bulan dengan libur fleksibel</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-2">
                            <form action="{{ route('finance.employees.work-schedules.destroy', [$employee, $workSchedule]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal kerja ini?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    <i class="fas fa-trash mr-2"></i>Hapus Jadwal
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex space-x-2">
                            @if(!$workSchedule->is_active)
                            <form action="{{ route('finance.employees.work-schedules.update', [$employee, $workSchedule]) }}" 
                                  method="POST" 
                                  class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="1">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    <i class="fas fa-play mr-2"></i>Aktifkan
                                </button>
                            </form>
                            @else
                            <form action="{{ route('finance.employees.work-schedules.update', [$employee, $workSchedule]) }}" 
                                  method="POST" 
                                  class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="0">
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    <i class="fas fa-pause mr-2"></i>Nonaktifkan
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>