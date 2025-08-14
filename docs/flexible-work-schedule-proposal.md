# Proposal: Sistem Hari Kerja Fleksibel per Karyawan

## Latar Belakang Masalah

Saat ini sistem menghitung hari kerja dengan logika standar:
- Semua karyawan menggunakan pola kerja Senin-Jumat
- Weekend (Sabtu-Minggu) otomatis dikecualikan
- Tidak ada fleksibilitas untuk karyawan dengan pola kerja berbeda

## Kebutuhan Bisnis

1. **Karyawan dengan libur Sabtu-Minggu** (pola standar)
2. **Karyawan dengan libur 4x dalam sebulan** (tidak tentu weekend)
3. **Karyawan dengan pola kerja custom** (misal: 6 hari kerja, libur bergantian)
4. **Fleksibilitas per periode** (jadwal bisa berubah setiap bulan)

## Solusi yang Diusulkan

### 1. Database Schema Baru

#### A. Tabel `employee_work_schedules`
```sql
CREATE TABLE employee_work_schedules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    schedule_type ENUM('standard', 'custom', 'flexible') DEFAULT 'standard',
    work_days_per_month INT DEFAULT NULL COMMENT 'Jumlah hari kerja per bulan untuk tipe flexible',
    standard_off_days JSON DEFAULT NULL COMMENT 'Hari libur tetap: [0,6] untuk Minggu,Sabtu',
    effective_from DATE NOT NULL,
    effective_until DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_active (employee_id, is_active),
    INDEX idx_effective_period (effective_from, effective_until)
);
```

#### B. Tabel `employee_custom_off_days`
```sql
CREATE TABLE employee_custom_off_days (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    off_date DATE NOT NULL,
    reason VARCHAR(255) NULL COMMENT 'Alasan libur: cuti, libur custom, dll',
    period_month INT NOT NULL COMMENT 'Bulan periode (1-12)',
    period_year INT NOT NULL COMMENT 'Tahun periode',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, off_date),
    INDEX idx_employee_period (employee_id, period_year, period_month)
);
```

### 2. Tipe Schedule yang Didukung

#### A. Standard Schedule
- **Deskripsi**: Pola kerja standar Senin-Jumat
- **Konfigurasi**: `standard_off_days = [0,6]` (Minggu=0, Sabtu=6)
- **Perhitungan**: Exclude weekend otomatis

#### B. Custom Schedule  
- **Deskripsi**: Pola kerja dengan hari libur tetap custom
- **Konfigurasi**: `standard_off_days = [1,3]` (Senin, Rabu libur)
- **Perhitungan**: Exclude hari yang ditentukan

#### C. Flexible Schedule
- **Deskripsi**: Jumlah hari kerja tetap per bulan, tapi tanggal fleksibel
- **Konfigurasi**: `work_days_per_month = 20`
- **Perhitungan**: Berdasarkan target hari kerja, bukan exclude pattern

### 3. Model dan Relationship Baru

#### A. Model `EmployeeWorkSchedule`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeWorkSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'schedule_type',
        'work_days_per_month',
        'standard_off_days',
        'effective_from',
        'effective_until',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'standard_off_days' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPeriod($query, $date)
    {
        $date = Carbon::parse($date);
        return $query->where('effective_from', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', $date);
                    });
    }
}
```

#### B. Model `EmployeeCustomOffDay`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCustomOffDay extends Model
{
    protected $fillable = [
        'employee_id',
        'off_date',
        'reason',
        'period_month',
        'period_year'
    ];

    protected $casts = [
        'off_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('period_year', $year)
                    ->where('period_month', $month);
    }
}
```

### 4. Update Model Employee

```php
// Tambahkan relationship di Employee model
public function workSchedules()
{
    return $this->hasMany(EmployeeWorkSchedule::class);
}

public function customOffDays()
{
    return $this->hasMany(EmployeeCustomOffDay::class);
}

public function currentWorkSchedule($date = null)
{
    $date = $date ?: now();
    return $this->workSchedules()
                ->active()
                ->forPeriod($date)
                ->orderBy('effective_from', 'desc')
                ->first();
}
```

### 5. Update SalaryPeriodService

#### A. Method Baru untuk Flexible Calculation
```php
/**
 * Calculate working days for employee with flexible schedule
 */
public function getWorkingDaysForEmployee($employeeId, $startDate, $endDate): int
{
    $employee = Employee::find($employeeId);
    $schedule = $employee->currentWorkSchedule($startDate);
    
    if (!$schedule) {
        // Fallback ke perhitungan standar
        return $this->getWorkingDaysInPeriod($startDate, $endDate);
    }
    
    switch ($schedule->schedule_type) {
        case 'standard':
            return $this->calculateStandardWorkingDays($startDate, $endDate, $schedule->standard_off_days);
            
        case 'custom':
            return $this->calculateCustomWorkingDays($startDate, $endDate, $schedule->standard_off_days);
            
        case 'flexible':
            return $this->calculateFlexibleWorkingDays($employeeId, $startDate, $endDate, $schedule);
            
        default:
            return $this->getWorkingDaysInPeriod($startDate, $endDate);
    }
}

/**
 * Calculate standard working days with custom off days
 */
private function calculateStandardWorkingDays($startDate, $endDate, $offDays = [0, 6]): int
{
    $workingDays = 0;
    $offDays = $offDays ?: [0, 6]; // Default weekend
    
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        if (!in_array($date->dayOfWeek, $offDays)) {
            $workingDays++;
        }
    }
    
    return $workingDays;
}

/**
 * Calculate custom working days (same as standard but different off days)
 */
private function calculateCustomWorkingDays($startDate, $endDate, $offDays): int
{
    return $this->calculateStandardWorkingDays($startDate, $endDate, $offDays);
}

/**
 * Calculate flexible working days based on target per month
 */
private function calculateFlexibleWorkingDays($employeeId, $startDate, $endDate, $schedule): int
{
    // Untuk flexible, kita gunakan target hari kerja per bulan
    $targetPerMonth = $schedule->work_days_per_month;
    
    // Hitung proporsi berdasarkan periode
    $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;
    $averageDaysPerMonth = 30; // Asumsi rata-rata hari per bulan
    
    $proportion = $totalDaysInPeriod / $averageDaysPerMonth;
    $expectedWorkingDays = round($targetPerMonth * $proportion);
    
    // Kurangi dengan custom off days yang sudah ditentukan
    $customOffDays = EmployeeCustomOffDay::where('employee_id', $employeeId)
        ->whereBetween('off_date', [$startDate, $endDate])
        ->count();
    
    return max(0, $expectedWorkingDays - $customOffDays);
}
```

#### B. Update Method getAllEmployeesSalaryStatus
```php
public function getAllEmployeesSalaryStatus($period = null): Collection
{
    $period = $period ?: $this->getCurrentPeriod();
    $employees = Employee::active()->get();
    $statuses = collect();
    
    // Get all salary data for the period at once
    $salaryData = DailySalary::whereBetween('work_date', [$period['start'], $period['end']])
        ->where('status', 'confirmed')
        ->selectRaw('employee_id, COUNT(*) as input_days, MAX(work_date) as last_input_date')
        ->groupBy('employee_id')
        ->get()
        ->keyBy('employee_id');
    
    foreach ($employees as $employee) {
        $salary = $salaryData->get($employee->id);
        $inputDays = $salary ? $salary->input_days : 0;
        
        // PERUBAHAN: Gunakan perhitungan per employee
        $workingDays = $this->getWorkingDaysForEmployee(
            $employee->id, 
            $period['start'], 
            $period['end']
        );
        
        $percentage = $workingDays > 0 ? ($inputDays / $workingDays) * 100 : 0;
        
        $statuses->push([
            'employee' => $employee,
            'employee_id' => $employee->id,
            'name' => $employee->name,
            'employee_code' => $employee->employee_code,
            'period' => $period,
            'working_days' => $workingDays, // Sekarang per employee
            'input_days' => $inputDays,
            'percentage' => round($percentage, 1),
            'status' => $this->determineSalaryStatus($percentage),
            'last_input_date' => $salary ? $salary->last_input_date : null
        ]);
    }
    
    return $statuses;
}
```

### 6. Interface Management

#### A. Form Pengaturan Work Schedule
```php
// Controller: EmployeeWorkScheduleController
public function create(Employee $employee)
{
    return view('employees.work-schedules.create', compact('employee'));
}

public function store(Request $request, Employee $employee)
{
    $validated = $request->validate([
        'schedule_type' => 'required|in:standard,custom,flexible',
        'work_days_per_month' => 'nullable|integer|min:1|max:31',
        'standard_off_days' => 'nullable|array',
        'standard_off_days.*' => 'integer|min:0|max:6',
        'effective_from' => 'required|date',
        'effective_until' => 'nullable|date|after:effective_from',
        'notes' => 'nullable|string'
    ]);
    
    // Deactivate previous schedules
    $employee->workSchedules()->update(['is_active' => false]);
    
    // Create new schedule
    $employee->workSchedules()->create($validated + ['is_active' => true]);
    
    return redirect()->route('employees.show', $employee)
        ->with('success', 'Jadwal kerja berhasil disimpan');
}
```

#### B. Form Custom Off Days
```php
// Controller: EmployeeCustomOffDayController
public function store(Request $request, Employee $employee)
{
    $validated = $request->validate([
        'off_dates' => 'required|array',
        'off_dates.*' => 'date',
        'reason' => 'nullable|string',
        'period_month' => 'required|integer|min:1|max:12',
        'period_year' => 'required|integer|min:2020|max:2030'
    ]);
    
    foreach ($validated['off_dates'] as $date) {
        EmployeeCustomOffDay::updateOrCreate([
            'employee_id' => $employee->id,
            'off_date' => $date
        ], [
            'reason' => $validated['reason'],
            'period_month' => $validated['period_month'],
            'period_year' => $validated['period_year']
        ]);
    }
    
    return redirect()->back()->with('success', 'Hari libur custom berhasil disimpan');
}
```

### 7. Contoh Implementasi

#### Skenario 1: Karyawan Standard (Senin-Jumat)
```php
EmployeeWorkSchedule::create([
    'employee_id' => 1,
    'schedule_type' => 'standard',
    'standard_off_days' => [0, 6], // Minggu, Sabtu
    'effective_from' => '2025-08-01',
    'is_active' => true
]);

// Hasil: 23 hari kerja (exclude weekend)
```

#### Skenario 2: Karyawan dengan 4 Hari Libur Fleksibel
```php
EmployeeWorkSchedule::create([
    'employee_id' => 2,
    'schedule_type' => 'flexible',
    'work_days_per_month' => 26, // Target 26 hari kerja per bulan
    'effective_from' => '2025-08-01',
    'is_active' => true
]);

// Tambah hari libur custom
EmployeeCustomOffDay::create([
    'employee_id' => 2,
    'off_date' => '2025-08-05',
    'reason' => 'Libur personal',
    'period_month' => 8,
    'period_year' => 2025
]);

// Hasil: 26 hari kerja target - 4 hari libur custom = 22 hari kerja
```

#### Skenario 3: Karyawan Custom (Libur Selasa-Rabu)
```php
EmployeeWorkSchedule::create([
    'employee_id' => 3,
    'schedule_type' => 'custom',
    'standard_off_days' => [2, 3], // Selasa, Rabu
    'effective_from' => '2025-08-01',
    'is_active' => true
]);

// Hasil: ~18 hari kerja (exclude Selasa-Rabu)
```

### 8. Migration Commands

```bash
# Buat migration
php artisan make:migration create_employee_work_schedules_table
php artisan make:migration create_employee_custom_off_days_table

# Buat models
php artisan make:model EmployeeWorkSchedule
php artisan make:model EmployeeCustomOffDay

# Buat controllers
php artisan make:controller EmployeeWorkScheduleController --resource
php artisan make:controller EmployeeCustomOffDayController --resource
```

### 9. Keuntungan Solusi Ini

1. **Fleksibilitas Tinggi**: Mendukung berbagai pola kerja
2. **Backward Compatible**: Karyawan existing tetap menggunakan pola standar
3. **Per-Employee Customization**: Setiap karyawan bisa punya jadwal berbeda
4. **Historical Tracking**: Bisa track perubahan jadwal dari waktu ke waktu
5. **Easy Management**: Interface yang user-friendly untuk setting

### 10. Implementasi Bertahap

#### Phase 1: Database & Models
- Buat migration dan models baru
- Update Employee model dengan relationships

#### Phase 2: Service Layer
- Update SalaryPeriodService dengan logic baru
- Maintain backward compatibility

#### Phase 3: UI/UX
- Buat interface untuk manage work schedules
- Update employee detail page

#### Phase 4: Testing & Rollout
- Test dengan data sample
- Gradual rollout ke production

## Kesimpulan

Solusi ini memberikan fleksibilitas penuh untuk mengatur hari kerja per karyawan sambil tetap mempertahankan kompatibilitas dengan sistem yang ada. Angka "23" akan menjadi dinamis per karyawan berdasarkan jadwal kerja masing-masing.

**Apakah Anda ingin saya lanjutkan dengan implementasi kode lengkapnya?**