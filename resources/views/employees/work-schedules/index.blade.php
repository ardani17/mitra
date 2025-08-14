<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Jadwal Kerja - {{ $employee->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $employee->employee_code }} | {{ $employee->position }} | {{ $employee->department }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.show', $employee) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <a href="{{ route('finance.employees.work-schedules.create', $employee) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Tambah Jadwal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Schedule Card -->
            @if($currentSchedule)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-green-50 border-b border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-green-800 mb-2">
                                    <i class="fas fa-check-circle mr-2"></i>Jadwal Kerja Aktif
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-green-600">Tipe Jadwal</p>
                                        <p class="font-medium text-green-800">{!! $currentSchedule->schedule_type_badge !!}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-green-600">Periode Efektif</p>
                                        <p class="font-medium text-green-800">{{ $currentSchedule->effective_period }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-green-600">Deskripsi</p>
                                        <p class="font-medium text-green-800">{{ $currentSchedule->getScheduleDescription() }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('finance.employees.work-schedules.show', [$employee, $currentSchedule]) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                                <a href="{{ route('finance.employees.work-schedules.edit', [$employee, $currentSchedule]) }}" 
                                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded text-sm">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-yellow-50 border-b border-yellow-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Belum Ada Jadwal Kerja Aktif
                                </h3>
                                <p class="text-yellow-700">Karyawan ini belum memiliki jadwal kerja aktif. Sistem akan menggunakan jadwal standar (Senin-Jumat).</p>
                            </div>
                            <form action="{{ route('finance.employees.work-schedules.create-default', $employee) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus mr-1"></i>Buat Jadwal Default
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Schedule History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-history mr-2"></i>Riwayat Jadwal Kerja
                    </h3>

                    @if($schedules->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipe & Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Periode Efektif
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deskripsi
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dibuat
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($schedules as $schedule)
                                        <tr class="hover:bg-gray-50 {{ $schedule->is_active ? 'bg-green-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    {!! $schedule->schedule_type_badge !!}
                                                    @if($schedule->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check-circle mr-1"></i>Aktif
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            Tidak Aktif
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $schedule->effective_period }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $schedule->getScheduleDescription() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $schedule->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('finance.employees.work-schedules.show', [$employee, $schedule]) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('finance.employees.work-schedules.edit', [$employee, $schedule]) }}" 
                                                       class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(!$schedule->is_active)
                                                        <form action="{{ route('finance.employees.work-schedules.activate', [$employee, $schedule]) }}" 
                                                              method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:text-green-900" title="Aktifkan">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('finance.employees.work-schedules.deactivate', [$employee, $schedule]) }}" 
                                                              method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-orange-600 hover:text-orange-900" title="Nonaktifkan">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('finance.employees.work-schedules.destroy', [$employee, $schedule]) }}" 
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal kerja ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $schedules->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg mb-4">
                                <i class="fas fa-calendar-alt text-4xl mb-4"></i>
                                <p>Belum ada riwayat jadwal kerja</p>
                            </div>
                            <a href="{{ route('finance.employees.work-schedules.create', $employee) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-plus mr-2"></i>Buat Jadwal Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>