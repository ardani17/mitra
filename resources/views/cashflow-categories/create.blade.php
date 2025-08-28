@extends('layouts.app')

@section('title', 'Tambah Kategori Cashflow')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Tambah Kategori Cashflow</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Buat kategori baru untuk pemasukan atau pengeluaran</p>
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
        <form method="POST" action="{{ route('finance.cashflow-categories.store') }}">
            @csrf
            
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
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
    
    <!-- Information Box -->
    <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi Penting</h3>
                <div class="mt-2 text-xs sm:text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Kode kategori harus unik dan tidak dapat diubah setelah disimpan</li>
                        <li>Gunakan format kode yang konsisten (contoh: INC_XXX untuk income, EXP_XXX untuk expense)</li>
                        <li>Kategori yang sudah memiliki transaksi tidak dapat dihapus</li>
                        <li>Pilih group yang sesuai untuk memudahkan pengelompokan laporan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection