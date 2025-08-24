<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Hari Libur - {{ $employee->name }}
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
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period Filter & Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <!-- Mobile Layout -->
                    <div class="block sm:hidden space-y-4">
                        <!-- Period Filter -->
                        <form method="GET" action="{{ route('finance.employees.custom-off-days.index', $employee) }}" class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Periode:</label>
                                <select name="month" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @foreach($monthOptions as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @foreach($yearOptions as $yearOption)
                                        <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                            {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="flex-1 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded text-sm">
                                    <i class="fas fa-search mr-1"></i>Filter
                                </button>
                                <a href="{{ route('finance.employees.custom-off-days.calendar', $employee) }}?year={{ $year }}&month={{ $month }}"
                                   class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                    <i class="fas fa-calendar mr-1"></i>Kalender
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Desktop Layout -->
                    <div class="hidden sm:block">
                        <form method="GET" action="{{ route('finance.employees.custom-off-days.index', $employee) }}" class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Periode:</label>
                                <select name="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($monthOptions as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $monthNum == $month ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($yearOptions as $yearOption)
                                        <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                            {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-search mr-2"></i>Filter
                                </button>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('finance.employees.custom-off-days.calendar', $employee) }}?year={{ $year }}&month={{ $month }}"
                                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-calendar mr-2"></i>Kalender
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Current Period Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                                <i class="fas fa-calendar-times mr-2"></i>{{ $monthOptions[$month] }} {{ $year }}
                            </h3>
                            <p class="text-blue-700">
                                Total hari libur: <strong>{{ $offDays->count() }} hari</strong>
                            </p>
                        </div>
                        @if($offDays->count() > 0)
                            <form action="{{ route('finance.employees.custom-off-days.bulk-delete', $employee) }}" 
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Hapus semua hari libur untuk periode ini?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="period_month" value="{{ $month }}">
                                <input type="hidden" name="period_year" value="{{ $year }}">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                    <i class="fas fa-trash mr-1"></i>Hapus Semua
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Off Days List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-list mr-2"></i>Daftar Hari Libur
                    </h3>

                    @if($offDays->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hari
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Alasan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($offDays as $offDay)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $offDay->formatted_off_date }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $offDay->day_name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $offDay->reason_or_default }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {!! $offDay->status_badge !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('finance.employees.custom-off-days.show', [$employee, $offDay]) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('finance.employees.custom-off-days.edit', [$employee, $offDay]) }}" 
                                                       class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('finance.employees.custom-off-days.destroy', [$employee, $offDay]) }}" 
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
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
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg mb-4">
                                <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                <p>Belum ada hari libur untuk periode {{ $monthOptions[$month] }} {{ $year }}</p>
                                <p class="text-sm mt-2">Gunakan fitur "Tambah Cepat" di bawah untuk menambahkan hari libur</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Add Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Cepat
                    </h3>
                    <form id="quickAddForm" action="{{ route('finance.employees.custom-off-days.quick-add', $employee) }}" method="POST" class="flex items-end space-x-4">
                        @csrf
                        <div>
                            <label for="quickOffDate" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Libur
                            </label>
                            <input type="date" id="quickOffDate" name="off_date" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="quickReason" class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan (Opsional)
                            </label>
                            <input type="text" id="quickReason" name="reason" placeholder="Alasan libur..."
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-plus mr-2"></i>Tambah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Quick add form handler with AJAX and fallback
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('quickAddForm');
            if (!form) return;
            
            form.addEventListener('submit', function(e) {
                const offDate = document.getElementById('quickOffDate').value;
                const reason = document.getElementById('quickReason').value;
                
                if (!offDate) {
                    alert('Tanggal libur harus diisi');
                    e.preventDefault();
                    return;
                }
                
                // Try AJAX first
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menambah...';
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Hari libur berhasil ditambahkan!');
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Gagal menambah hari libur');
                    }
                })
                .catch(error => {
                    console.error('AJAX failed, falling back to regular form submit:', error);
                    // Fallback to regular form submission
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    form.submit();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>