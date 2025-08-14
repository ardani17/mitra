@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manajemen Perusahaan</h1>
        </div>
        <a href="{{ route('companies.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Buat Perusahaan Baru</span>
            <span class="sm:hidden">Tambah</span>
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('companies.index') }}" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                           placeholder="Cari berdasarkan nama, email, atau telepon">
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                    Filter
                </button>
                <a href="{{ route('companies.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Companies List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($companies->count() > 0)
            <!-- Mobile Card View -->
            <div class="block sm:hidden space-y-4 p-4">
                @foreach($companies as $company)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ $company->name }}</h4>
                            @if($company->address)
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($company->address, 60) }}</p>
                            @endif
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">
                            {{ $company->projects()->count() }} proyek
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        @if($company->email)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Email:</span>
                            <span class="text-sm text-gray-900 break-words">{{ $company->email }}</span>
                        </div>
                        @endif
                        @if($company->phone)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Telepon:</span>
                            <span class="text-sm text-gray-900">{{ $company->phone }}</span>
                        </div>
                        @endif
                        @if($company->contact_person)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Kontak:</span>
                            <span class="text-sm text-gray-900">{{ $company->contact_person }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-3 pt-3 border-t flex flex-col space-y-2">
                        <a href="{{ route('companies.show', $company) }}"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                            Lihat Detail
                        </a>
                        <div class="flex space-x-2">
                            <a href="{{ route('companies.edit', $company) }}"
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded text-center text-sm">
                                Edit
                            </a>
                            @if($company->projects()->count() == 0)
                            <form action="{{ route('companies.destroy', $company) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm"
                                        onclick="return confirm('Yakin ingin menghapus perusahaan ini?')">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak Person</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $company->name }}</div>
                                @if($company->address)
                                <div class="text-sm text-gray-500">{{ Str::limit($company->address, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($company->email)
                                <div class="text-sm text-gray-900">{{ $company->email }}</div>
                                @endif
                                @if($company->phone)
                                <div class="text-sm text-gray-500">{{ $company->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $company->contact_person ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $company->projects()->count() }} proyek
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('companies.show', $company) }}" class="text-blue-600 hover:text-blue-900 mr-3">Lihat</a>
                                <a href="{{ route('companies.edit', $company) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                @if($company->projects()->count() == 0)
                                <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Yakin ingin menghapus perusahaan ini?')">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-3 sm:px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $companies->links() }}
            </div>
        @else
            <div class="text-center py-8 sm:py-12 px-4">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada perusahaan</h3>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">Mulai dengan membuat perusahaan baru.</p>
                <div class="mt-4">
                    <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Buat Perusahaan Baru
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
