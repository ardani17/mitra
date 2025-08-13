@extends('layouts.app')

@section('title', 'Kalender Gaji Harian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Kalender Gaji Harian - {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('finance.daily-salaries.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-1"></i>
                            Daftar Gaji
                        </a>
                        <a href="{{ route('finance.daily-salaries.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Tambah Gaji
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="employee_filter" class="form-label">Karyawan</label>
                            <select class="form-select" id="employee_filter" name="employee_id">
                                <option value="">Semua Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $selectedEmployee && $selectedEmployee->id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month_filter" class="form-label">Bulan</label>
                            <select class="form-select" id="month_filter" name="month">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year_filter" class="form-label">Tahun</label>
                            <select class="form-select" id="year_filter" name="year">
                                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary" id="apply_filter">
                                <i class="fas fa-filter me-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered calendar-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Minggu</th>
                                    <th>Senin</th>
                                    <th>Selasa</th>
                                    <th>Rabu</th>
                                    <th>Kamis</th>
                                    <th>Jumat</th>
                                    <th>Sabtu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $currentDate = $startDate->copy();
                                    $currentDate->startOfWeek(\Carbon\Carbon::SUNDAY);
                                @endphp
                                
                                @while($currentDate->lte($endDate->copy()->endOfWeek(\Carbon\Carbon::SATURDAY)))
                                    <tr>
                                        @for($day = 0; $day < 7; $day++)
                                            @php
                                                $isCurrentMonth = $currentDate->month == $month;
                                                $dateKey = $currentDate->format('Y-m-d');
                                                $dayData = [];
                                                
                                                if ($selectedEmployee) {
                                                    $key = $selectedEmployee->id . '-' . $dateKey;
                                                    if (isset($dailySalaries[$key])) {
                                                        $dayData[] = $dailySalaries[$key];
                                                    }
                                                } else {
                                                    foreach($dailySalaries as $salary) {
                                                        if ($salary->work_date->format('Y-m-d') == $dateKey) {
                                                            $dayData[] = $salary;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            
                                            <td class="calendar-day {{ !$isCurrentMonth ? 'text-muted' : '' }}" 
                                                data-date="{{ $dateKey }}" 
                                                style="height: 120px; vertical-align: top; position: relative;">
                                                
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold">{{ $currentDate->day }}</span>
                                                    @if($isCurrentMonth)
                                                        <a href="{{ route('finance.daily-salaries.create', ['date' => $dateKey]) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           style="font-size: 10px; padding: 2px 6px;"
                                                           title="Tambah gaji untuk tanggal ini">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                
                                                @if(!empty($dayData))
                                                    <div class="salary-entries">
                                                        @foreach($dayData as $salary)
                                                            <div class="salary-entry mb-1 p-1 rounded" 
                                                                 style="background-color: {{ $salary->status == 'confirmed' ? '#d4edda' : '#fff3cd' }}; font-size: 11px;">
                                                                <div class="fw-bold">{{ $salary->employee->name }}</div>
                                                                <div>{{ $salary->formatted_total_amount }}</div>
                                                                <div>
                                                                    <span class="badge badge-sm {{ $salary->status == 'confirmed' ? 'bg-success' : 'bg-warning' }}">
                                                                        {{ $salary->status == 'confirmed' ? 'Konfirmasi' : 'Draft' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            @php $currentDate->addDay(); @endphp
                                        @endfor
                                    </tr>
                                @endwhile
                            </tbody>
                        </table>
                    </div>

                    <!-- Legend -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width: 20px; height: 20px; background-color: #d4edda; border-radius: 3px;"></div>
                                    <span>Dikonfirmasi</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width: 20px; height: 20px; background-color: #fff3cd; border-radius: 3px;"></div>
                                    <span>Draft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-table td {
    width: 14.28%;
    min-height: 120px;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.salary-entry {
    border: 1px solid #dee2e6;
    cursor: pointer;
}

.salary-entry:hover {
    opacity: 0.8;
}

.badge-sm {
    font-size: 9px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    document.getElementById('apply_filter').addEventListener('click', function() {
        const employeeId = document.getElementById('employee_filter').value;
        const month = document.getElementById('month_filter').value;
        const year = document.getElementById('year_filter').value;
        
        const params = new URLSearchParams();
        if (employeeId) params.append('employee_id', employeeId);
        params.append('month', month);
        params.append('year', year);
        
        window.location.href = `{{ route('finance.daily-salaries.calendar') }}?${params.toString()}`;
    });

    // Make salary entries clickable
    document.querySelectorAll('.salary-entry').forEach(function(entry) {
        entry.addEventListener('click', function() {
            // You can add functionality to view/edit salary details here
            console.log('Salary entry clicked');
        });
    });
});
</script>
@endsection