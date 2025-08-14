@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Ajukan Pengeluaran Baru</h1>
        </div>
        <a href="{{ route('expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Kembali ke Pengeluaran</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proyek *</label>
                    <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="">Pilih Proyek</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ (old('project_id') == $proj->id || (isset($project) && $project->id == $proj->id)) ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pengeluaran *</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp) *</label>
                    <input type="text" name="amount" value="{{ old('amount') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                           placeholder="Masukkan jumlah..."
                           oninput="formatCurrency(this)">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-4 sm:mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                          placeholder="Jelaskan detail pengeluaran...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('expenses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base order-1 sm:order-2">
                    Ajukan Pengeluaran
                </button>
            </div>
        </form>
    </div>
    
    <!-- Bypass Information -->
    @if(isset($bypassInfo) && $bypassInfo['can_bypass'])
        <div class="mt-4 sm:mt-6 bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Director Bypass Aktif</h3>
                    <div class="mt-2 text-xs sm:text-sm text-green-700">
                        <p class="hidden sm:block">{{ $bypassInfo['status_message'] }}. Pengeluaran akan langsung disetujui tanpa perlu menunggu approval dari finance manager dan project manager.</p>
                        <p class="sm:hidden">Pengeluaran akan langsung disetujui (Director Bypass).</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif(isset($bypassInfo) && $bypassInfo['user_is_director'] && !$bypassInfo['bypass_enabled'])
        <div class="mt-4 sm:mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Fitur Bypass Tersedia</h3>
                    <div class="mt-2 text-xs sm:text-sm text-yellow-700">
                        <p class="hidden sm:block">Anda dapat mengaktifkan fitur bypass approval di <a href="{{ route('settings.index') }}" class="underline font-medium">Pengaturan Sistem</a> untuk melewati workflow approval.</p>
                        <p class="sm:hidden">Aktifkan bypass di <a href="{{ route('settings.index') }}" class="underline font-medium">Pengaturan</a> untuk melewati approval.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Catatan</h3>
                    <div class="mt-2 text-xs sm:text-sm text-blue-700">
                        <p class="hidden sm:block">Pengeluaran akan dibuat dengan status "Pending" dan memerlukan persetujuan dari finance manager dan project manager/direktur.</p>
                        <p class="sm:hidden">Pengeluaran akan dibuat dan memerlukan persetujuan.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function formatCurrency(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/\D/g, '');
    
    // Format dengan titik sebagai pemisah ribuan
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    // Set nilai yang sudah diformat
    input.value = value;
}

// Saat form disubmit, hapus format untuk mengirim angka murni
document.querySelector('form').addEventListener('submit', function(e) {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        // Hapus titik pemisah ribuan sebelum submit
        // Gunakan replace untuk menghapus semua titik (pemisah ribuan)
        let rawValue = amountInput.value.replace(/\./g, '');
        amountInput.value = rawValue;
    }
});

// Tambahkan event listener untuk memastikan format saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput && amountInput.value) {
        formatCurrency(amountInput);
    }
});
</script>
@endsection
