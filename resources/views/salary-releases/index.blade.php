<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Rilis Gaji') }}
            </h2>
            <a href="{{ route('finance.salary-releases.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>Buat Rilis Gaji
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-list text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Rilis</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $summary['total_releases'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Draft</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($summary['draft_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Dirilis</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($summary['released_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-money-check-alt text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Dibayar</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($summary['paid_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calculator text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('finance.salary-releases.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Karyawan</label>
                                <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Dirilis</option>
                                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Dibayar</option>
                                </select>
                            </div>
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-search mr-2"></i>Filter
                                </button>
                                <a href="{{ route('finance.salary-releases.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Salary Releases Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    @if($salaryReleases->count() > 0)
                        <!-- Desktop Table -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kode Rilis
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Karyawan
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Periode
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Kotor
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Potongan
                                        </th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Bersih
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
                                    @foreach($salaryReleases as $release)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('finance.salary-releases.show', $release) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        {{ $release->release_code }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $release->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ substr($release->employee->name, 0, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $release->employee->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $release->employee->employee_code }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $release->period_label }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $release->formatted_total_amount }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $release->formatted_deductions }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $release->formatted_net_amount }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                {!! $release->status_badge !!}
                                                @if($release->cashflowEntry)
                                                    <br><span class="text-xs text-green-600">Tercatat di Cashflow</span>
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('finance.salary-releases.show', $release) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @can('update', $release)
                                                        @if($release->status === 'draft')
                                                            <a href="{{ route('finance.salary-releases.edit', $release) }}"
                                                               class="text-yellow-600 hover:text-yellow-900">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                        
                                                        @if($release->status === 'draft' && auth()->user()->role === 'direktur')
                                                            <form action="{{ route('finance.salary-releases.release', $release) }}"
                                                                  method="POST" class="inline"
                                                                  onsubmit="return confirm('Rilis gaji ini? Setelah dirilis akan tercatat di cashflow.')">
                                                                @csrf
                                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        
                                                        @if($release->status === 'released')
                                                            <form action="{{ route('finance.salary-releases.mark-as-paid', $release) }}"
                                                                  method="POST" class="inline"
                                                                  onsubmit="return confirm('Tandai gaji ini sebagai dibayar?')">
                                                                @csrf
                                                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                                    <i class="fas fa-money-check-alt"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                    
                                                    @can('delete', $release)
                                                        <form action="{{ route('finance.salary-releases.destroy', $release) }}"
                                                              method="POST" class="inline"
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus rilis gaji ini?')">
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
                            @foreach($salaryReleases as $release)
                                <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-xs font-medium text-gray-700">
                                                    {{ substr($release->employee->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $release->employee->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $release->employee->employee_code }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">{{ $release->formatted_net_amount }}</div>
                                            <div class="text-xs text-gray-500">{{ $release->period_label }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                                        <div>
                                            <span class="text-gray-500">Kode:</span>
                                            <a href="{{ route('finance.salary-releases.show', $release) }}"
                                               class="font-medium text-indigo-600 hover:text-indigo-900">
                                                {{ $release->release_code }}
                                            </a>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Total Kotor:</span>
                                            <span class="font-medium">{{ $release->formatted_total_amount }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Potongan:</span>
                                            <span class="font-medium">{{ $release->formatted_deductions }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Dibuat:</span>
                                            <span class="font-medium">{{ $release->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            {!! $release->status_badge !!}
                                            @if($release->cashflowEntry)
                                                <span class="text-xs text-green-600">Tercatat di Cashflow</span>
                                            @endif
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('finance.salary-releases.show', $release) }}"
                                               class="text-indigo-600 hover:text-indigo-900 p-1">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            
                                            @can('update', $release)
                                                @if($release->status === 'draft')
                                                    <a href="{{ route('finance.salary-releases.edit', $release) }}"
                                                       class="text-yellow-600 hover:text-yellow-900 p-1">
                                                        <i class="fas fa-edit text-sm"></i>
                                                    </a>
                                                @endif
                                                
                                                @if($release->status === 'draft' && auth()->user()->role === 'direktur')
                                                    <form action="{{ route('finance.salary-releases.release', $release) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('Rilis gaji ini? Setelah dirilis akan tercatat di cashflow.')">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 p-1">
                                                            <i class="fas fa-paper-plane text-sm"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($release->status === 'released')
                                                    <form action="{{ route('finance.salary-releases.mark-as-paid', $release) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('Tandai gaji ini sebagai dibayar?')">
                                                        @csrf
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900 p-1">
                                                            <i class="fas fa-money-check-alt text-sm"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                            
                                            @can('delete', $release)
                                                <form action="{{ route('finance.salary-releases.destroy', $release) }}"
                                                      method="POST" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus rilis gaji ini?')">
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
                            {{ $salaryReleases->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="text-gray-500 mb-4">
                                <i class="fas fa-money-check-alt text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                                <p class="text-base sm:text-lg">Belum ada rilis gaji</p>
                            </div>
                            <a href="{{ route('finance.salary-releases.create') }}"
                               class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                                <i class="fas fa-plus mr-1 sm:mr-2"></i>Buat Rilis Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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
    </script>
    @endpush
</x-app-layout>