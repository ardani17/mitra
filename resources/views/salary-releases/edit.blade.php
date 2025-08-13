<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Rilis Gaji') }} - {{ $salaryRelease->release_code }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.salary-releases.show', $salaryRelease) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('finance.salary-releases.update', $salaryRelease) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Employee Info (Read-only) -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Informasi Karyawan</h3>
                                    <div class="mt-2 text-sm text-blue-700 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <p><span class="font-medium">Nama:</span> {{ $salaryRelease->employee->name }}</p>
                                        <p><span class="font-medium">Posisi:</span> {{ $salaryRelease->employee->position }}</p>
                                        <p><span class="font-medium">Departemen:</span> {{ $salaryRelease->employee->department }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Period Info (Read-only) -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-gray-800 mb-2">Periode Rilis Gaji</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Periode Mulai</label>
                                    <input type="text" value="{{ $salaryRelease->period_start->format('d/m/Y') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Periode Selesai</label>
                                    <input type="text" value="{{ $salaryRelease->period_end->format('d/m/Y') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Summary (Read-only) -->
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-green-800 mb-2">Ringkasan Gaji</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-green-700">
                                <div>
                                    <span class="font-medium">Jumlah Hari Kerja:</span>
                                    <span class="ml-2">{{ $salaryRelease->dailySalaries->count() }} hari</span>
                                </div>
                                <div>
                                    <span class="font-medium">Total Kotor:</span>
                                    <span class="ml-2">{{ $salaryRelease->formatted_total_amount }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Status:</span>
                                    <span class="ml-2">{!! $salaryRelease->status_badge !!}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Editable Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Deductions -->
                            <div>
                                <label for="deductions" class="block text-sm font-medium text-gray-700">
                                    Potongan
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="deductions" id="deductions" 
                                           value="{{ old('deductions', $salaryRelease->deductions) }}" 
                                           placeholder="0"
                                           min="0" step="1000"
                                           class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('deductions') border-red-500 @enderror">
                                </div>
                                @error('deductions')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Potongan seperti BPJS, pajak, pinjaman, dll</p>
                            </div>

                            <!-- Net Amount Display -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Bersih</label>
                                <div class="mt-1 p-3 bg-green-50 border border-green-200 rounded-md">
                                    <span class="text-lg font-bold text-green-600" id="net-amount-display">
                                        {{ $salaryRelease->formatted_net_amount }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Total kotor dikurangi potongan</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="4" 
                                      placeholder="Catatan tambahan untuk rilis gaji ini (opsional)"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes', $salaryRelease->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Daily Salaries Preview -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-gray-800 mb-4">
                                Gaji Harian yang Akan Dirilis ({{ $salaryRelease->dailySalaries->count() }} hari)
                            </h3>
                            
                            @if($salaryRelease->dailySalaries->count() > 0)
                                <div class="max-h-64 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($salaryRelease->dailySalaries->sortBy('work_date') as $salary)
                                                <tr class="text-sm">
                                                    <td class="px-3 py-2">{{ $salary->work_date->format('d/m/Y') }}</td>
                                                    <td class="px-3 py-2">{!! $salary->attendance_status_badge !!}</td>
                                                    <td class="px-3 py-2 font-medium">{{ $salary->formatted_total_amount }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Tidak ada gaji harian untuk periode ini</p>
                            @endif
                        </div>

                        <!-- Warning for Released Status -->
                        @if($salaryRelease->status !== 'draft')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>Rilis gaji ini sudah dalam status "{{ ucfirst($salaryRelease->status) }}". 
                                               Hanya catatan dan potongan yang dapat diubah.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('finance.salary-releases.show', $salaryRelease) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Update net amount when deductions change
        document.getElementById('deductions').addEventListener('input', function() {
            const totalGross = {{ $salaryRelease->total_amount }};
            const deductions = parseFloat(this.value) || 0;
            const netAmount = totalGross - deductions;
            
            document.getElementById('net-amount-display').textContent = 
                'Rp ' + netAmount.toLocaleString('id-ID');
        });
    </script>
    @endpush
</x-app-layout>