@extends('layouts.app')

@section('content')
<div class="container mx-auto px-8 lg:px-16 xl:px-24 py-8 max-w-6xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Review Pengeluaran</h1>
        <a href="{{ route('expense-approvals.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Kembali ke Daftar Approval
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detail Pengeluaran -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Detail Pengeluaran</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Proyek</label>
                        <div class="text-lg font-semibold text-gray-900">{{ $expense->project->name }}</div>
                        <div class="text-sm text-gray-500">{{ $expense->project->code }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($expense->category) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                        <div class="text-2xl font-bold text-green-600">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengeluaran</label>
                        <div class="text-lg text-gray-900">
                            {{ \Carbon\Carbon::parse($expense->expense_date)->format('d F Y') }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pengaju</label>
                        <div class="text-lg text-gray-900">{{ $expense->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $expense->user->email }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Saat Ini</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($expense->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($expense->status === 'approved') bg-green-100 text-green-800
                            @elseif($expense->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($expense->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <div class="bg-gray-50 rounded-md p-4">
                        <p class="text-gray-900">{{ $expense->description }}</p>
                    </div>
                </div>
                
                @if($expense->receipt_number)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kwitansi</label>
                    <div class="text-lg text-gray-900">{{ $expense->receipt_number }}</div>
                </div>
                @endif
                
                @if($expense->vendor)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <div class="text-lg text-gray-900">{{ $expense->vendor }}</div>
                </div>
                @endif
                
                @if($expense->notes)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <div class="bg-gray-50 rounded-md p-4">
                        <p class="text-gray-900">{{ $expense->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Form Approval -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Proses Approval</h2>
                
                <!-- Status Approval Workflow -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Status Workflow</h3>
                    <div class="space-y-3">
                        @foreach($expense->approvals as $approvalItem)
                            <div class="flex items-center justify-between p-3 rounded-lg
                                @if($approvalItem->status === 'approved') bg-green-50 border border-green-200
                                @elseif($approvalItem->status === 'rejected') bg-red-50 border border-red-200
                                @else bg-yellow-50 border border-yellow-200 @endif">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $approvalItem->level)) }}
                                    </div>
                                    @if($approvalItem->approver)
                                        <div class="text-xs text-gray-500">{{ $approvalItem->approver->name }}</div>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
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
                <form action="{{ route('expense-approvals.process', $approval) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keputusan *</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="status" value="approved" required
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-900">Setujui</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="status" value="rejected" required
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-900">Tolak</span>
                            </label>
                        </div>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Berikan catatan untuk keputusan Anda (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Proses Approval
                        </button>
                    </div>
                </form>
                @else
                <div class="text-center py-4">
                    <div class="text-sm text-gray-500">
                        Approval ini sudah diproses sebelumnya.
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Info Workflow -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Informasi Workflow</h3>
                        <div class="mt-2 text-sm text-blue-700">
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
