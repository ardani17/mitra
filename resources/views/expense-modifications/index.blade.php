@extends('layouts.app')

@section('title', 'Permintaan Modifikasi Pengeluaran')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Permintaan Modifikasi Pengeluaran</h1>
            <p class="text-slate-600 mt-1 text-sm sm:text-base">Kelola permintaan edit dan hapus pengeluaran</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="{{ route('expenses.index') }}"
               class="btn-secondary-mobile sm:btn-secondary sm:w-auto flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pengeluaran
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 sm:p-6 mb-6 sm:mb-8">
        <form method="GET" action="{{ route('expense-modifications.index') }}" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <h3 class="text-base sm:text-lg font-medium text-slate-800">Filter Permintaan</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('expense-modifications.index', ['status' => 'pending']) }}"
                       class="px-3 py-2 text-xs sm:text-sm {{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} rounded-md transition-colors duration-200">
                        Pending
                    </a>
                    <a href="{{ route('expense-modifications.index', ['status' => 'approved']) }}"
                       class="px-3 py-2 text-xs sm:text-sm {{ request('status') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} rounded-md transition-colors duration-200">
                        Disetujui
                    </a>
                    <a href="{{ route('expense-modifications.index', ['status' => 'rejected']) }}"
                       class="px-3 py-2 text-xs sm:text-sm {{ request('status') === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} rounded-md transition-colors duration-200">
                        Ditolak
                    </a>
                    <a href="{{ route('expense-modifications.index') }}"
                       class="px-3 py-2 text-xs sm:text-sm {{ !request('status') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} rounded-md transition-colors duration-200">
                        Semua
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div>
                    <label for="action_type" class="block text-sm font-medium text-slate-700 mb-1">Jenis Aksi</label>
                    <select name="action_type" id="action_type" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Jenis</option>
                        <option value="edit" {{ request('action_type') === 'edit' ? 'selected' : '' }}>Edit</option>
                        <option value="delete" {{ request('action_type') === 'delete' ? 'selected' : '' }}>Hapus</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modifications Table -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-slate-800">Daftar Permintaan Modifikasi</h3>
                <div class="text-sm text-slate-600">
                    Total: {{ $modifications->total() }} permintaan
                </div>
            </div>
        </div>

        @if($modifications->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pengeluaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jenis Aksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pengaju</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($modifications as $modification)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $modification->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ Str::limit($modification->expense->description, 40) }}</div>
                                    <div class="text-sm text-slate-500">{{ $modification->expense->project->name }}</div>
                                    <div class="text-sm text-slate-500">Rp {{ number_format($modification->expense->amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->action_type_badge_class }}">
                                        {{ $modification->formatted_action_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $modification->requester->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modification->status_badge_class }}">
                                        {{ $modification->formatted_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('expense-modifications.show', $modification) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                        @if($modification->isPending() && auth()->user()->hasAnyRole(['finance_manager', 'direktur', 'project_manager']))
                                            <form method="POST" action="{{ route('expense-modifications.approve', $modification) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Yakin ingin menyetujui permintaan ini?')">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('expense-modifications.reject', $modification) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menolak permintaan ini?')">Tolak</button>
                                            </form>
                                        @endif
                                        @if($modification->isPending() && $modification->requested_by === auth()->id())
                                            <form method="POST" action="{{ route('expense-modifications.cancel', $modification) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900" onclick="return confirm('Yakin ingin membatalkan permintaan ini?')">Batal</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden space-y-3 p-4">
                @foreach($modifications as $modification)
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="font-medium text-slate-900">{{ Str::limit($modification->expense->description, 30) }}</div>
                                <div class="text-sm text-slate-600">{{ $modification->expense->project->name }}</div>
                                <div class="text-sm text-slate-600">Rp {{ number_format($modification->expense->amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $modification->action_type_badge_class }}">
                                    {{ $modification->formatted_action_type }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                            <div>
                                <div class="text-slate-500">Pengaju</div>
                                <div class="font-medium">{{ $modification->requester->name }}</div>
                            </div>
                            <div>
                                <div class="text-slate-500">Status</div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $modification->status_badge_class }}">
                                    {{ $modification->formatted_status }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="text-xs text-slate-500 mb-3">
                            {{ $modification->created_at->format('d M Y H:i') }}
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('expense-modifications.show', $modification) }}" class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-xs">Lihat</a>
                            @if($modification->isPending() && auth()->user()->hasAnyRole(['finance_manager', 'direktur', 'project_manager']))
                                <form method="POST" action="{{ route('expense-modifications.approve', $modification) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-100 text-green-800 rounded text-xs" onclick="return confirm('Yakin ingin menyetujui?')">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('expense-modifications.reject', $modification) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-red-100 text-red-800 rounded text-xs" onclick="return confirm('Yakin ingin menolak?')">Tolak</button>
                                </form>
                            @endif
                            @if($modification->isPending() && $modification->requested_by === auth()->id())
                                <form method="POST" action="{{ route('expense-modifications.cancel', $modification) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-orange-100 text-orange-800 rounded text-xs" onclick="return confirm('Yakin ingin membatalkan?')">Batal</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $modifications->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">Tidak ada permintaan modifikasi</h3>
                <p class="mt-1 text-sm text-slate-500">Belum ada permintaan edit atau hapus pengeluaran.</p>
            </div>
        @endif
    </div>
</div>
@endsection