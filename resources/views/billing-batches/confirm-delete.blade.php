@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Konfirmasi Hapus Batch Penagihan</h1>
            <p class="text-gray-600 mt-2">Tindakan ini tidak dapat dibatalkan</p>
        </div>

        <!-- Batch Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Batch yang akan Dihapus</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-sm text-gray-600">Kode Batch:</span>
                    <p class="font-medium text-lg">{{ $billingBatch->batch_code }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Tanggal Billing:</span>
                    <p class="font-medium">{{ $billingBatch->billing_date->format('d M Y') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($billingBatch->status_color == 'green') bg-green-100 text-green-800
                          @elseif($billingBatch->status_color == 'blue') bg-blue-100 text-blue-800
                          @elseif($billingBatch->status_color == 'yellow') bg-yellow-100 text-yellow-800
                          @elseif($billingBatch->status_color == 'red') bg-red-100 text-red-800
                          @elseif($billingBatch->status_color == 'purple') bg-purple-100 text-purple-800
                          @elseif($billingBatch->status_color == 'indigo') bg-indigo-100 text-indigo-800
                          @else bg-gray-100 text-gray-800 @endif">
                        {{ $billingBatch->status_label }}
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Tipe Klien:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($billingBatch->client_type == 'wapu') bg-blue-100 text-blue-800
                          @else bg-green-100 text-green-800 @endif">
                        {{ $billingBatch->client_type == 'wapu' ? 'WAPU' : 'Non-WAPU' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-600">Total Billing:</span>
                    <p class="font-bold text-xl text-blue-600">Rp {{ number_format($billingBatch->total_billing_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Jumlah Proyek:</span>
                    <p class="font-medium text-lg">{{ $billingBatch->projectBillings->count() }} proyek</p>
                </div>
            </div>
        </div>

        <!-- Deletion Summary -->
        @php
            $deletionSummary = $billingBatch->getDeletionSummary();
        @endphp

        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">
                <svg class="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Data yang akan Terhapus
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-white p-3 rounded border">
                    <span class="text-gray-600">Penagihan Proyek:</span>
                    <p class="font-semibold text-red-600">{{ $deletionSummary['project_billings_count'] }}</p>
                </div>
                <div class="bg-white p-3 rounded border">
                    <span class="text-gray-600">Dokumen:</span>
                    <p class="font-semibold text-red-600">{{ $deletionSummary['documents_count'] }}</p>
                </div>
                <div class="bg-white p-3 rounded border">
                    <span class="text-gray-600">Log Status:</span>
                    <p class="font-semibold text-red-600">{{ $deletionSummary['status_logs_count'] }}</p>
                </div>
            </div>

            @if($deletionSummary['documents_count'] > 0)
            <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded">
                <p class="text-yellow-800 text-sm">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Perhatian:</strong> {{ $deletionSummary['documents_count'] }} file dokumen akan dihapus secara permanen dari server.
                </p>
            </div>
            @endif
        </div>

        <!-- Affected Projects -->
        @if($billingBatch->projectBillings->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Proyek yang Terpengaruh</h3>
            <div class="space-y-3">
                @foreach($billingBatch->projectBillings as $billing)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded border">
                    <div>
                        <p class="font-medium">{{ $billing->project->code }} - {{ $billing->project->name }}</p>
                        <p class="text-sm text-gray-600">
                            Nilai: Rp {{ number_format($billing->total_amount, 0, ',', '.') }}
                            • Status: {{ ucfirst($billing->status) }}
                        </p>
                    </div>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                          @if($billing->project->client_type == 'wapu') bg-blue-100 text-blue-800
                          @else bg-green-100 text-green-800 @endif">
                        {{ $billing->project->client_type == 'wapu' ? 'WAPU' : 'Non-WAPU' }}
                    </span>
                </div>
                @endforeach
            </div>
            
            <div class="mt-4 p-3 bg-blue-100 border border-blue-300 rounded">
                <p class="text-blue-800 text-sm">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Catatan:</strong> Penagihan proyek akan dikembalikan ke status individual (tidak tergabung dalam batch).
                </p>
            </div>
        </div>
        @endif

        <!-- Validation Checks -->
        @php
            $canDelete = $billingBatch->canBeDeleted();
        @endphp

        @if(!$canDelete['can_delete'])
        <div class="bg-red-100 border border-red-400 rounded-lg p-4 mb-6">
            <h3 class="text-red-800 font-semibold mb-2">Batch Tidak Dapat Dihapus</h3>
            <ul class="text-red-700 text-sm space-y-1">
                @foreach($canDelete['blockers'] as $blocker)
                <li>• {{ $blocker }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($canDelete['warnings']))
        <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-4 mb-6">
            <h3 class="text-yellow-800 font-semibold mb-2">Peringatan</h3>
            <ul class="text-yellow-700 text-sm space-y-1">
                @foreach($canDelete['warnings'] as $warning)
                <li>• {{ $warning }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <a href="{{ route('billing-batches.show', $billingBatch) }}"
               class="btn-secondary text-sm sm:text-base py-2 sm:py-3 px-4 sm:px-6 w-full sm:w-auto text-center">
                <svg class="inline w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Batal
            </a>

            @if($canDelete['can_delete'])
            <form action="{{ route('billing-batches.destroy', $billingBatch) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Apakah Anda benar-benar yakin ingin menghapus batch penagihan ini? Tindakan ini tidak dapat dibatalkan!')"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 sm:py-3 px-4 sm:px-6 rounded-lg text-sm sm:text-base w-full sm:w-auto">
                    <svg class="inline w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Ya, Hapus Batch
                </button>
            </form>
            @else
            <button disabled class="bg-gray-400 text-white font-bold py-2 sm:py-3 px-4 sm:px-6 rounded-lg cursor-not-allowed text-sm sm:text-base w-full sm:w-auto">
                <svg class="inline w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Tidak Dapat Dihapus
            </button>
            @endif
        </div>

        <!-- Additional Information -->
        <div class="mt-6 sm:mt-8 p-3 sm:p-4 bg-gray-100 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-2 text-sm sm:text-base">Informasi Tambahan:</h4>
            <ul class="text-xs sm:text-sm text-gray-600 space-y-1">
                <li>• Batch penagihan hanya dapat dihapus jika statusnya masih "Draft"</li>
                <li>• Setelah dihapus, penagihan proyek akan dikembalikan ke status individual</li>
                <li>• Semua dokumen yang terkait dengan batch ini akan dihapus permanen</li>
                <li>• Riwayat status batch akan hilang dan tidak dapat dipulihkan</li>
                <li>• Proyek yang terkait tidak akan terhapus, hanya referensi batch-nya yang dihilangkan</li>
            </ul>
        </div>
    </div>
</div>
@endsection
