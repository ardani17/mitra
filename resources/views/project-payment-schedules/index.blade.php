@extends('layouts.app')

@section('title', 'Jadwal Pembayaran')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Jadwal Pembayaran</h1>
            <p class="mb-0 text-muted">Kelola jadwal pembayaran termin proyek</p>
        </div>
        <div>
            @can('create', App\Models\ProjectPaymentSchedule::class)
                <a href="{{ route('project-payment-schedules.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Jadwal
                </a>
            @endcan
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Jadwal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_schedules']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_schedules']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['overdue_schedules']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Nilai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('project-payment-schedules.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="project_id">Proyek</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">Semua Proyek</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" 
                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->code }} - {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="billed" {{ request('status') == 'billed' ? 'selected' : '' }}>Sudah Ditagih</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="due_date_from">Dari Tanggal</label>
                            <input type="date" name="due_date_from" id="due_date_from" 
                                   class="form-control" value="{{ request('due_date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="due_date_to">Sampai Tanggal</label>
                            <input type="date" name="due_date_to" id="due_date_to" 
                                   class="form-control" value="{{ request('due_date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Pencarian</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Nama proyek atau termin..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('project-payment-schedules.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset Filter
                        </a>
                        <button type="button" class="btn btn-info ml-2" onclick="exportSchedules()">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal Pembayaran</h6>
        </div>
        <div class="card-body">
            @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Proyek</th>
                                <th>Termin</th>
                                <th>Persentase</th>
                                <th>Jumlah</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr class="{{ $schedule->isOverdue() ? 'table-danger' : '' }}">
                                    <td>
                                        <div class="font-weight-bold">{{ $schedule->project->name }}</div>
                                        <small class="text-muted">{{ $schedule->project->code }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ $schedule->termin_name }}</div>
                                        @if($schedule->description)
                                            <small class="text-muted">{{ $schedule->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ number_format($schedule->percentage, 1) }}%</td>
                                    <td>Rp {{ number_format($schedule->amount, 0, ',', '.') }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($schedule->due_date)->format('d/m/Y') }}
                                        @if($schedule->isOverdue())
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Terlambat {{ $schedule->getDueDateDifference() }} hari
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $schedule->getBadgeColor() }}">
                                            {{ $schedule->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view', $schedule)
                                                <a href="{{ route('project-payment-schedules.show', $schedule) }}" 
                                                   class="btn btn-sm btn-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            
                                            @can('update', $schedule)
                                                @if($schedule->status === 'pending')
                                                    <a href="{{ route('project-payment-schedules.edit', $schedule) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            @endcan

                                            @if($schedule->status === 'pending')
                                                <a href="{{ route('project-billings.create-termin', $schedule) }}" 
                                                   class="btn btn-sm btn-success" title="Buat Tagihan">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                            @endif

                                            @can('delete', $schedule)
                                                @if($schedule->status === 'pending')
                                                    <form method="POST" 
                                                          action="{{ route('project-payment-schedules.destroy', $schedule) }}" 
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $schedules->firstItem() }} - {{ $schedules->lastItem() }} 
                        dari {{ $schedules->total() }} jadwal
                    </div>
                    <div>
                        {{ $schedules->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Tidak ada jadwal pembayaran</h5>
                    <p class="text-muted">Belum ada jadwal pembayaran yang sesuai dengan filter yang dipilih.</p>
                    @can('create', App\Models\ProjectPaymentSchedule::class)
                        <a href="{{ route('project-payment-schedules.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Jadwal Pertama
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportSchedules() {
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'excel');
    
    const exportUrl = '{{ route("project-payment-schedules.export") }}?' + params.toString();
    window.open(exportUrl, '_blank');
}

// Auto-submit form on select change
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('#project_id, #status');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
@endsection