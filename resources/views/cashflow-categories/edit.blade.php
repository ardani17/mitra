@extends('layouts.app')

@section('title', 'Edit Kategori Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Edit Kategori Cashflow</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Perbarui informasi kategori</p>
        </div>
        <a href="{{ route('finance.cashflow-categories.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">Kembali ke Daftar</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <form method="POST" action="{{ route('finance.cashflow-categories.update', $cashflowCategory) }}">
            @csrf
            @method('PUT')
            
            @include('cashflow-categories._form')
            
            <!-- Action Buttons -->
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('finance.cashflow-categories.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base order-1 sm:order-2">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    
    <!-- Category Info -->
    @if($cashflowCategory->cashflowEntries()->count() > 0)
    <div class="mt-4 sm:mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                <div class="mt-2 text-xs sm:text-sm text-yellow-700">
                    <p>Kategori ini memiliki {{ $cashflowCategory->cashflowEntries()->count() }} transaksi terkait.</p>
                    <p class="mt-1">Perubahan pada kategori ini akan mempengaruhi semua transaksi tersebut.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection