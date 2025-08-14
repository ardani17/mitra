@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Pengeluaran</h1>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <a href="{{ route('expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Kembali ke Pengeluaran</span>
                <span class="sm:hidden">Kembali</span>
            </a>
            @if($expense->status == 'draft')
            <a href="{{ route('expenses.edit', $expense) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                <span class="hidden sm:inline">Edit Pengeluaran</span>
                <span class="sm:hidden">Edit</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Expense Overview -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-4 sm:mb-6 space-y-4 lg:space-y-0">
            <div class="min-w-0 flex-1">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-800 break-words">{{ $expense->description }}</h2>
                <p class="text-gray-600 mt-2 text-sm sm:text-base">Proyek: {{ $expense->project->name }}</p>
                <p class="text-gray-600 text-sm sm:text-base">Dibuat oleh: {{ $expense->user->name }}</p>
            </div>
            <div class="text-left lg:text-right">
                <div class="text-xl sm:text-3xl font-bold text-gray-800 break-words">Rp {{ number_format($expense->amount, 0, ',', '.') }}</div>
                <span class="inline-block mt-2 px-3 py-1 text-xs sm:text-sm font-semibold rounded-full
                      @if($expense->status == 'approved') bg-green-100 text-green-800
                      @elseif($expense->status == 'rejected') bg-red-100 text-red-800
                      @elseif($expense->status == 'submitted') bg-yellow-100 text-yellow-800
                      @elseif($expense->status == 'draft') bg-gray-100 text-gray-800
                      @else bg-purple-100 text-purple-800 @endif">
                    @if($expense->status == 'draft') Draft
                    @elseif($expense->status == 'submitted') Diajukan
                    @elseif($expense->status == 'approved') Disetujui
                    @elseif($expense->status == 'rejected') Ditolak
                    @else {{ ucfirst($expense->status) }}
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Dasar</h3>
                <div class="space-y-2 sm:space-y-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Tanggal Pengeluaran</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $expense->expense_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Dibuat Pada</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $expense->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Terakhir Diperbarui</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ $expense->updated_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Detail Proyek</h3>
                <div class="space-y-2 sm:space-y-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Proyek</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900 break-words">{{ $expense->project->name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Tipe Proyek</label>
                        <div class="mt-1 text-xs sm:text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $expense->project->type)) }}</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-600">Status Proyek</label>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                              @if($expense->project->status == 'completed') bg-green-100 text-green-800
                              @elseif($expense->project->status == 'on_progress') bg-blue-100 text-blue-800
                              @elseif($expense->project->status == 'on_hold') bg-yellow-100 text-yellow-800
                              @elseif($expense->project->status == 'draft') bg-gray-100 text-gray-800
                              @else bg-purple-100 text-purple-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $expense->project->status)) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Aksi</h3>
                @if($expense->status == 'draft')
                <div class="space-y-2 sm:space-y-3">
                    <form action="{{ route('expenses.submit', $expense) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base"
                                onclick="return confirm('Yakin ingin mengajukan pengeluaran ini untuk persetujuan?')">
                            <span class="hidden sm:inline">Ajukan untuk Persetujuan</span>
                            <span class="sm:hidden">Ajukan</span>
                        </button>
                    </form>
                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base"
                                onclick="return confirm('Yakin ingin menghapus pengeluaran ini?')">
                            <span class="hidden sm:inline">Hapus Pengeluaran</span>
                            <span class="sm:hidden">Hapus</span>
                        </button>
                    </form>
                </div>
                @elseif($expense->status == 'submitted')
                <div class="space-y-2 sm:space-y-3">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-xs sm:text-sm text-yellow-800">Pengeluaran ini menunggu persetujuan.</p>
                    </div>
                </div>
                @elseif($expense->status == 'approved')
                <div class="space-y-2 sm:space-y-3">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <p class="text-xs sm:text-sm text-green-800">Pengeluaran ini telah disetujui.</p>
                    </div>
                    
                    @if($expense->canBeModified())
                        <!-- Edit Request Button -->
                        @can('requestModification', $expense)
                            <a href="{{ route('expense-modifications.edit-form', $expense) }}"
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded inline-block text-center transition-colors duration-200 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span class="hidden sm:inline">Request Edit</span>
                                <span class="sm:hidden">Edit</span>
                            </a>
                        @endcan
                        
                        <!-- Delete Request Button -->
                        @can('requestModification', $expense)
                            <a href="{{ route('expense-modifications.delete-form', $expense) }}"
                               class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 sm:px-4 rounded inline-block text-center transition-colors duration-200 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="hidden sm:inline">Request Delete</span>
                                <span class="sm:hidden">Hapus</span>
                            </a>
                        @endcan
                    @endif
                    
                    @if($expense->hasPendingModifications())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-xs sm:text-sm text-yellow-800 font-medium">Permintaan Modifikasi Pending</p>
                            <p class="text-xs text-yellow-700 mt-1">Pengeluaran ini memiliki permintaan modifikasi yang pending.</p>
                            <a href="{{ route('expense-modifications.history', $expense) }}"
                               class="text-yellow-800 hover:text-yellow-900 text-xs underline">
                                Lihat Riwayat
                            </a>
                        </div>
                    @endif
                </div>
                @elseif($expense->status == 'rejected')
                <div class="space-y-2 sm:space-y-3">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-xs sm:text-sm text-red-800">Pengeluaran ini telah ditolak.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approval Process -->
    @if($expense->status != 'draft')
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4">Proses Persetujuan</h2>
        <div class="space-y-3 sm:space-y-4">
            @forelse($expense->approvals as $approval)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 border border-gray-200 rounded-lg space-y-3 sm:space-y-0">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full flex items-center justify-center
                              @if($approval->status == 'approved') bg-green-100
                              @elseif($approval->status == 'rejected') bg-red-100
                              @else bg-gray-100 @endif">
                            @if($approval->status == 'approved')
                            <svg class="h-4 w-4 sm:h-6 sm:w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @elseif($approval->status == 'rejected')
                            <svg class="h-4 w-4 sm:h-6 sm:w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            @else
                            <svg class="h-4 w-4 sm:h-6 sm:w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-900">
                            {{ ucfirst(str_replace('_', ' ', $approval->level)) }}
                        </h3>
                        @if($approval->approver)
                        <p class="text-xs sm:text-sm text-gray-500">Disetujui oleh: {{ $approval->approver->name }}</p>
                        @endif
                        @if($approval->approved_at)
                        <p class="text-xs sm:text-sm text-gray-500">Tanggal: {{ $approval->approved_at->format('d M Y H:i') }}</p>
                        @endif
                        @if($approval->notes)
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 break-words">Catatan: {{ $approval->notes }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end sm:justify-start">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                          @if($approval->status == 'approved') bg-green-100 text-green-800
                          @elseif($approval->status == 'rejected') bg-red-100 text-red-800
                          @else bg-gray-100 text-gray-800 @endif">
                        @if($approval->status == 'approved') Disetujui
                        @elseif($approval->status == 'rejected') Ditolak
                        @else {{ ucfirst($approval->status) }}
                        @endif
                    </span>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Tidak ada catatan persetujuan.</p>
            @endforelse
        </div>
    </div>
    @endif

    <!-- Notes/Comments -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4">Catatan & Komentar</h2>
        <div class="space-y-3 sm:space-y-4">
            @if($expense->notes)
            <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                <p class="text-gray-700 text-sm sm:text-base break-words">{{ $expense->notes }}</p>
            </div>
            @else
            <p class="text-gray-500 text-sm">Tidak ada catatan tersedia.</p>
            @endif
        </div>
    </div>
</div>
@endsection
