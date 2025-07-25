@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-2 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Persetujuan Pengeluaran</h1>
        </div>
        <div class="text-xs sm:text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">
            Role: <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $userRole)) }}</span>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('expense-approvals.index') }}" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari deskripsi..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                </div>
                
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proyek</label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Min</label>
                    <input type="number" name="amount_min" value="{{ request('amount_min') }}" 
                           placeholder="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Max</label>
                    <input type="number" name="amount_max" value="{{ request('amount_max') }}" 
                           placeholder="999999999"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('expense-approvals.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    Reset
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Approvals List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($approvals->count() > 0)
            <!-- Mobile Card View -->
            <div class="block sm:hidden space-y-4 p-4">
                @foreach($approvals as $approval)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ $approval->expense->project->name }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $approval->expense->project->code }}</p>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">
                            {{ ucfirst($approval->expense->category) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs text-gray-600">Deskripsi:</span>
                            <p class="text-sm text-gray-900">{{ Str::limit($approval->expense->description, 60) }}</p>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Jumlah:</span>
                            <span class="text-sm font-medium text-gray-900">Rp {{ number_format($approval->expense->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Tanggal:</span>
                            <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($approval->expense->expense_date)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Pengaju:</span>
                            <span class="text-sm text-gray-900">{{ $approval->expense->user->name }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t">
                        <a href="{{ route('expense-approvals.show', $approval) }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-center text-sm block">
                            Review
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Proyek
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deskripsi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pengaju
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($approvals as $approval)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $approval->expense->project->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $approval->expense->project->code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ Str::limit($approval->expense->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($approval->expense->category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($approval->expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($approval->expense->expense_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $approval->expense->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('expense-approvals.show', $approval) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-3 sm:px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $approvals->links() }}
            </div>
        @else
            <div class="text-center py-8 sm:py-12 px-4">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada approval pending</h3>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">Saat ini tidak ada pengeluaran yang menunggu persetujuan Anda.</p>
            </div>
        @endif
    </div>
</div>
@endsection
