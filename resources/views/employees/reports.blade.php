<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan Karyawan') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.dashboard') }}" 
                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-chart-pie mr-2"></i>Dashboard
                </a>
                <a href="{{ route('finance.employees.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-list mr-2"></i>Daftar Karyawan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Report Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Laporan</h3>
                    <form method="GET" action="{{ route('finance.employees.reports') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="summary" {{ $reportType === 'summary' ? 'selected' : '' }}>Ringkasan</option>
                                    <option value="salary" {{ $reportType === 'salary' ? 'selected' : '' }}>Gaji</option>
                                    <option value="attendance" {{ $reportType === 'attendance' ? 'selected' : '' }}>Kehadiran</option>
                                    <option value="performance" {{ $reportType === 'performance' ? 'selected' : '' }}>Performa</option>
                                </select>
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">Departemen</label>
                                <select name="department" id="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    <i class="fas fa-search mr-2"></i>Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Content -->
            @if($reportType === 'summary')
                @include('employees.reports.summary', ['data' => $reportData])
            @elseif($reportType === 'salary')
                @include('employees.reports.salary', ['data' => $reportData])
            @elseif($reportType === 'attendance')
                @include('employees.reports.attendance', ['data' => $reportData])
            @elseif($reportType === 'performance')
                @include('employees.reports.performance', ['data' => $reportData])
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-submit form on type change
        document.getElementById('type').addEventListener('change', function() {
            this.form.submit();
        });

        // Print functionality
        function printReport() {
            window.print();
        }

        // Export functionality (placeholder)
        function exportReport(format) {
            alert(`Export ke ${format.toUpperCase()} akan segera tersedia`);
        }
    </script>
    @endpush
</x-app-layout>