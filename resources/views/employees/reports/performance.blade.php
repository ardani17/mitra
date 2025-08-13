<!-- Performance Report -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Laporan Performa Karyawan</h3>
            <div class="flex space-x-2">
                <button onclick="printReport()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <button onclick="exportReport('excel')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ranking
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Skor Performa
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rating
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rata-rata Gaji
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Masa Kerja
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hari Kerja
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ $index < 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="text-sm font-bold">{{ $index + 1 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full object-cover" 
                                         src="{{ $item['employee']->avatar_url }}" 
                                         alt="{{ $item['employee']->name }}">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['employee']->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['employee']->department }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-{{ $item['performance_score'] >= 80 ? 'green' : ($item['performance_score'] >= 60 ? 'yellow' : 'red') }}-600 h-2 rounded-full" 
                                             style="width: {{ $item['performance_score'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $item['performance_score'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $item['rating'] === 'Excellent' ? 'bg-green-100 text-green-800' : 
                                       ($item['rating'] === 'Good' ? 'bg-blue-100 text-blue-800' : 
                                       ($item['rating'] === 'Average' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ $item['rating'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($item['average_monthly_salary'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['work_duration'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['total_work_days'] }} hari
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Performance Distribution -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $ratings = collect($data)->groupBy('rating');
            @endphp
            @foreach(['Excellent', 'Good', 'Average', 'Poor'] as $rating)
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold 
                        {{ $rating === 'Excellent' ? 'text-green-600' : 
                           ($rating === 'Good' ? 'text-blue-600' : 
                           ($rating === 'Average' ? 'text-yellow-600' : 'text-red-600')) }}">
                        {{ $ratings->get($rating, collect())->count() }}
                    </div>
                    <div class="text-sm text-gray-600">{{ $rating }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>