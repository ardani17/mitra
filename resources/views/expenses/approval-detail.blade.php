@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8 max-w-6xl">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Review Pengeluaran</h1>
        </div>
        <a href="{{ route('expense-approvals.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Kembali ke Daftar Approval</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Detail Pengeluaran -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Detail Pengeluaran</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Proyek</label>
                        <div class="text-base sm:text-lg font-semibold text-gray-900 break-words">{{ $expense->project->name }}</div>
                        <div class="text-xs sm:text-sm text-gray-500">{{ $expense->project->code }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <span class="px-2 sm:px-3 py-1 inline-flex text-xs sm:text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($expense->category) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                        <div class="text-lg sm:text-2xl font-bold text-green-600 break-words">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Tanggal Pengeluaran</label>
                        <div class="text-sm sm:text-lg text-gray-900">
                            {{ \Carbon\Carbon::parse($expense->expense_date)->format('d F Y') }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Pengaju</label>
                        <div class="text-sm sm:text-lg text-gray-900 break-words">{{ $expense->user->name }}</div>
                        <div class="text-xs sm:text-sm text-gray-500 break-words">{{ $expense->user->email }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status Saat Ini</label>
                        <span class="px-2 sm:px-3 py-1 inline-flex text-xs sm:text-sm leading-5 font-semibold rounded-full
                            @if($expense->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($expense->status === 'approved') bg-green-100 text-green-800
                            @elseif($expense->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($expense->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-4 sm:mt-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <div class="bg-gray-50 rounded-md p-3 sm:p-4">
                        <p class="text-gray-900 text-sm sm:text-base break-words">{{ $expense->description }}</p>
                    </div>
                </div>
                
                @if($expense->receipt_number)
                <div class="mt-3 sm:mt-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Nomor Kwitansi</label>
                    <div class="text-sm sm:text-lg text-gray-900 break-words">{{ $expense->receipt_number }}</div>
                </div>
                @endif
                
                @if($expense->vendor)
                <div class="mt-3 sm:mt-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <div class="text-sm sm:text-lg text-gray-900 break-words">{{ $expense->vendor }}</div>
                </div>
                @endif
                
                @if($expense->notes)
                <div class="mt-3 sm:mt-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <div class="bg-gray-50 rounded-md p-3 sm:p-4">
                        <p class="text-gray-900 text-sm sm:text-base break-words">{{ $expense->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Form Approval -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Proses Approval</h2>
                
                <!-- Status Approval Workflow -->
                <div class="mb-4 sm:mb-6">
                    <h3 class="text-xs sm:text-sm font-medium text-gray-700 mb-2 sm:mb-3">Status Workflow</h3>
                    <div class="space-y-2 sm:space-y-3">
                        @foreach($expense->approvals as $approvalItem)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-2 sm:p-3 rounded-lg space-y-2 sm:space-y-0
                                @if($approvalItem->status === 'approved') bg-green-50 border border-green-200
                                @elseif($approvalItem->status === 'rejected') bg-red-50 border border-red-200
                                @else bg-yellow-50 border border-yellow-200 @endif">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $approvalItem->level)) }}
                                    </div>
                                    @if($approvalItem->approver)
                                        <div class="text-xs text-gray-500 break-words">{{ $approvalItem->approver->name }}</div>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full self-start sm:self-center
                                    @if($approvalItem->status === 'approved') bg-green-100 text-green-800
                                    @elseif($approvalItem->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($approvalItem->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Form Approval -->
                @if($approval->status === 'pending')
                <form action="{{ route('expense-approvals.process', $approval) }}" method="POST" class="space-y-3 sm:space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Keputusan *</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="status" value="approved" required
                                       class="h-3 w-3 sm:h-4 sm:w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                <span class="ml-2 text-xs sm:text-sm text-gray-900">Setujui</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="status" value="rejected" required
                                       class="h-3 w-3 sm:h-4 sm:w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                <span class="ml-2 text-xs sm:text-sm text-gray-900">Tolak</span>
                            </label>
                        </div>
                        @error('status')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                  placeholder="Berikan catatan untuk keputusan Anda (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex space-x-2 sm:space-x-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-xs sm:text-sm">
                            Proses Approval
                        </button>
                    </div>
                </form>
                @else
                <div class="text-center py-3 sm:py-4">
                    <div class="text-xs sm:text-sm text-gray-500">
                        Approval ini sudah diproses sebelumnya.
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Info Workflow -->
            <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <h3 class="text-xs sm:text-sm font-medium text-blue-800">Informasi Workflow</h3>
                        <div class="mt-1 sm:mt-2 text-xs sm:text-sm text-blue-700">
                            <p>
                                @if($expense->amount > 10000000)
                                    Pengeluaran > Rp 10.000.000 memerlukan persetujuan Finance Manager dan Direktur.
                                @else
                                    Pengeluaran ≤ Rp 10.000.000 memerlukan persetujuan Finance Manager dan Project Manager.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
