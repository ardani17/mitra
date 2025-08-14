<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Hari Libur - {{ $employee->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.custom-off-days.edit', [$employee, $customOffDay]) }}"
                   class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('finance.employees.custom-off-days.index', $employee) }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Content Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column - Off Day Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Hari Libur</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Karyawan</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $employee->name }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tanggal Libur</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $customOffDay->off_date->format('d/m/Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $customOffDay->off_date->format('l') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Alasan/Keterangan</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $customOffDay->reason ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tipe Libur</label>
                                    <div class="mt-1">
                                        @if($customOffDay->type)
                                            @switch($customOffDay->type)
                                                @case('personal')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-user mr-1"></i>Personal/Cuti Pribadi
                                                    </span>
                                                    @break
                                                @case('sick')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-thermometer-half mr-1"></i>Sakit
                                                    </span>
                                                    @break
                                                @case('emergency')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>Darurat
                                                    </span>
                                                    @break
                                                @case('religious')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        <i class="fas fa-pray mr-1"></i>Keagamaan
                                                    </span>
                                                    @break
                                                @case('family')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-home mr-1"></i>Keluarga
                                                    </span>
                                                    @break
                                                @case('other')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-question mr-1"></i>Lainnya
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($customOffDay->type) }}
                                                    </span>
                                            @endswitch
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status Pembayaran</label>
                                    <div class="mt-1">
                                        @if($customOffDay->is_paid)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Libur Berbayar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>Libur Tidak Berbayar
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customOffDay->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Diupdate</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customOffDay->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Additional Info -->
                        <div class="space-y-6">
                            @if($customOffDay->notes)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Catatan Tambahan</h4>
                                <p class="text-sm text-gray-700">{{ $customOffDay->notes }}</p>
                            </div>
                            @endif

                            <!-- Employee Info -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">
                                    <i class="fas fa-user mr-1"></i>Informasi Karyawan
                                </h4>
                                <div class="text-sm text-blue-700">
                                    <p class="mb-1">
                                        <strong>Posisi:</strong> {{ $employee->position }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Departemen:</strong> {{ $employee->department }}
                                    </p>
                                    <p class="text-xs">
                                        <strong>Gaji Harian:</strong> {{ $employee->formatted_daily_rate }}
                                    </p>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-yellow-800">Informasi</h4>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Hari libur akan dikecualikan dari perhitungan hari kerja</li>
                                                <li>Libur berbayar akan tetap dihitung dalam kalkulasi gaji</li>
                                                <li>Sistem otomatis menghitung hari kerja = total hari - weekend - hari libur</li>
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
                            <form action="{{ route('finance.employees.custom-off-days.destroy', [$employee, $customOffDay]) }}"
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    <i class="fas fa-trash mr-2"></i>Hapus Hari Libur
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('finance.employees.custom-off-days.edit', [$employee, $customOffDay]) }}"
                               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-edit mr-2"></i>Edit Hari Libur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>