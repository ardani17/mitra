@extends('layouts.app')

@section('title', 'Detail Penagihan - ' . $projectBilling->invoice_number)

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800">Detail Penagihan</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base break-words">{{ $projectBilling->invoice_number }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            @if($projectBilling->status !== 'paid')
                <a href="{{ route('project-billings.edit', $projectBilling) }}"
                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            @endif
            <a href="{{ route('project-billings.index') }}"
               class="bg-slate-600 hover:bg-slate-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <!-- Billing Information -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-2 sm:space-y-0">
                    <h2 class="text-base sm:text-lg font-semibold text-slate-800">Informasi Penagihan</h2>
                    @php
                        $statusConfig = [
                            'draft' => ['bg-slate-100', 'text-slate-800', 'Draft'],
                            'sent' => ['bg-blue-100', 'text-blue-800', 'Terkirim'],
                            'paid' => ['bg-green-100', 'text-green-800', 'Lunas'],
                            'overdue' => ['bg-red-100', 'text-red-800', 'Terlambat']
                        ];
                        $config = $statusConfig[$projectBilling->status] ?? ['bg-slate-100', 'text-slate-800', ucfirst($projectBilling->status)];
                    @endphp
                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium {{ $config[0] }} {{ $config[1] }}">
                        {{ $config[2] }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Nomor Invoice</label>
                        <p class="text-base sm:text-lg font-semibold text-slate-900 break-words">{{ $projectBilling->invoice_number }}</p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Tipe Pembayaran</label>
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0">
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $projectBilling->getTerminLabel() }}
                            </span>
                            @if($projectBilling->isFinalTermin())
                                <span class="sm:ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Termin Terakhir
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Tanggal Penagihan</label>
                        <p class="text-slate-900 text-sm sm:text-base">{{ $projectBilling->billing_date->format('d F Y') }}</p>
                    </div>
                </div>

                @if($projectBilling->notes)
                    <div class="mt-4 sm:mt-6">
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-2">Catatan</label>
                        <div class="p-3 sm:p-4 bg-slate-50 rounded-lg">
                            <p class="text-slate-900 text-sm sm:text-base">{{ $projectBilling->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Project Information -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Informasi Proyek</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Nama Proyek</label>
                        <p class="text-base sm:text-lg font-semibold text-slate-900 break-words">{{ $projectBilling->project->name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Kode Proyek</label>
                        <p class="text-slate-900 text-sm sm:text-base">{{ $projectBilling->project->code }}</p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Klien</label>
                        <p class="text-slate-900 text-sm sm:text-base break-words">{{ $projectBilling->project->client_name ?? 'Tidak ada' }}</p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-600 mb-1">Status Proyek</label>
                        <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($projectBilling->project->status === 'completed') bg-green-100 text-green-800
                            @elseif($projectBilling->project->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($projectBilling->project->status === 'on_hold') bg-yellow-100 text-yellow-800
                            @else bg-slate-100 text-slate-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $projectBilling->project->status)) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6">
                    <a href="{{ route('projects.show', $projectBilling->project) }}"
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200 text-sm sm:text-base">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Lihat Detail Proyek
                    </a>
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Rincian Nilai</h2>
                
                <div class="space-y-3 sm:space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-slate-200">
                        <span class="text-slate-600 text-sm sm:text-base">Nilai Jasa</span>
                        <span class="font-medium text-slate-900 text-sm sm:text-base">Rp {{ number_format($projectBilling->nilai_jasa, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-slate-200">
                        <span class="text-slate-600 text-sm sm:text-base">Nilai Material</span>
                        <span class="font-medium text-slate-900 text-sm sm:text-base">Rp {{ number_format($projectBilling->nilai_material, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-slate-200">
                        <span class="text-slate-600 text-sm sm:text-base">Subtotal</span>
                        <span class="font-medium text-slate-900 text-sm sm:text-base">Rp {{ number_format($projectBilling->subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-slate-200">
                        <span class="text-slate-600 text-sm sm:text-base">PPN ({{ $projectBilling->ppn_rate }}%)</span>
                        <span class="font-medium text-slate-900 text-sm sm:text-base">Rp {{ number_format($projectBilling->ppn_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 bg-slate-50 px-3 sm:px-4 rounded-lg">
                        <span class="text-base sm:text-lg font-semibold text-slate-800">Total Nilai</span>
                        <span class="text-lg sm:text-xl font-bold text-slate-900">Rp {{ number_format($projectBilling->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Termin Progress -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Progress Termin Proyek</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Progress Termin</span>
                        <span class="font-medium text-slate-900">{{ $projectBilling->termin_number }} dari {{ $projectBilling->total_termin }}</span>
                    </div>
                    
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $projectBilling->getProjectCompletionPercentage() }}%"></div>
                    </div>
                    
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>{{ number_format($projectBilling->getProjectCompletionPercentage(), 1) }}% selesai</span>
                        @if($projectBilling->isFinalTermin())
                            <span class="text-green-600 font-medium">Termin Pelunasan</span>
                        @else
                            <span>{{ $projectBilling->total_termin - $projectBilling->termin_number }} termin tersisa</span>
                        @endif
                    </div>
                    
                    @if($projectBilling->isProjectFullyPaid())
                        <div class="mt-3 p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-green-800">Proyek sudah lunas secara keseluruhan</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Schedule (if linked to schedule) -->
            @if($projectBilling->paymentSchedule)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Jadwal Pembayaran Termin</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Termin</span>
                            <span class="text-slate-600">{{ $projectBilling->termin_number }} dari {{ $projectBilling->total_termin }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Persentase</span>
                            <span class="text-slate-900 font-medium">{{ $projectBilling->paymentSchedule->percentage }}%</span>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Status Jadwal</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($projectBilling->paymentSchedule->status === 'paid') bg-green-100 text-green-800
                                @elseif($projectBilling->paymentSchedule->status === 'overdue') bg-red-100 text-red-800
                                @elseif($projectBilling->paymentSchedule->status === 'billed') bg-blue-100 text-blue-800
                                @else bg-slate-100 text-slate-800 @endif">
                                {{ ucfirst($projectBilling->paymentSchedule->status) }}
                            </span>
                        </div>
                        
                        @if($projectBilling->is_final_termin)
                            <div class="mt-3 p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-medium text-green-800">Ini adalah termin terakhir</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Aksi Cepat</h3>
                
                <div class="space-y-3">
                    @if($projectBilling->status === 'draft')
                        <form action="{{ route('project-billings.update', $projectBilling) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="sent">
                            <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                                <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim Invoice
                            </button>
                        </form>
                    @endif

                    @if($projectBilling->status === 'sent')
                        <form action="{{ route('project-billings.update', $projectBilling) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="paid">
                            <input type="hidden" name="paid_date" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="invoice_number" value="{{ $projectBilling->invoice_number }}">
                            <input type="hidden" name="nilai_jasa" value="{{ $projectBilling->nilai_jasa }}">
                            <input type="hidden" name="nilai_material" value="{{ $projectBilling->nilai_material }}">
                            <input type="hidden" name="subtotal" value="{{ $projectBilling->subtotal }}">
                            <input type="hidden" name="ppn_rate" value="{{ $projectBilling->ppn_rate }}">
                            <input type="hidden" name="ppn_calculation" value="{{ $projectBilling->ppn_calculation }}">
                            <input type="hidden" name="ppn_amount" value="{{ $projectBilling->ppn_amount }}">
                            <input type="hidden" name="total_amount" value="{{ $projectBilling->total_amount }}">
                            <input type="hidden" name="billing_date" value="{{ $projectBilling->billing_date->format('Y-m-d') }}">
                            <input type="hidden" name="description" value="{{ $projectBilling->notes }}">
                            <button type="submit"
                                    onclick="return confirm('Tandai tagihan ini sebagai sudah dibayar?')"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                                <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tandai Lunas
                            </button>
                        </form>
                    @endif

                    <button onclick="window.print()"
                            class="w-full bg-slate-600 hover:bg-slate-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                        <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Invoice
                    </button>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Timeline</h3>
                
                <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm font-medium text-slate-900">Penagihan dibuat</p>
                            <p class="text-xs text-slate-500">{{ $projectBilling->created_at->format('d F Y, H:i') }}</p>
                        </div>
                    </div>

                    @if($projectBilling->status !== 'draft')
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 bg-green-600 rounded-full mt-2"></div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-slate-900">Invoice terkirim</p>
                                <p class="text-xs text-slate-500">{{ $projectBilling->updated_at->format('d F Y, H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($projectBilling->status === 'paid')
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 bg-emerald-600 rounded-full mt-2"></div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-slate-900">Pembayaran diterima</p>
                                <p class="text-xs text-slate-500">{{ $projectBilling->updated_at->format('d F Y, H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Information -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-4">Informasi Terkait</h3>
                
                <div class="space-y-3 text-xs sm:text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Dibuat oleh</span>
                        <span class="text-slate-900">{{ $projectBilling->created_by ?? 'System' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-slate-600">Terakhir diupdate</span>
                        <span class="text-slate-900">{{ $projectBilling->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    @if($projectBilling->isTerminPayment())
                        <div class="flex justify-between">
                            <span class="text-slate-600">ID Jadwal</span>
                            <span class="text-slate-900">#{{ $projectBilling->parent_schedule_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .container {
        max-width: none !important;
        padding: 0 !important;
    }
}
</style>
@endpush
@endsection