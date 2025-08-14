# Dokumentasi Sistem Periode Gaji

## Overview
Sistem periode gaji menggunakan konsep cut-off untuk menentukan periode penggajian karyawan. Status gaji ditampilkan dalam format "X/Y - Z%" dimana:
- **X** = Jumlah hari yang sudah diinput gaji
- **Y** = Total hari kerja dalam periode (contoh: 23)
- **Z** = Persentase kelengkapan input gaji

## Perhitungan Hari Kerja (Working Days)

### Logika Dasar
Angka "23" (atau angka lainnya) dihitung berdasarkan:
1. **Periode Cut-off**: Rentang tanggal berdasarkan pengaturan sistem
2. **Exclusion Weekend**: Mengecualikan hari Sabtu dan Minggu
3. **Dynamic Calculation**: Berubah setiap periode tergantung jumlah hari kerja

### Konfigurasi Cut-off
```php
// Default settings
'salary_cutoff_start_day' => 11,  // Tanggal mulai periode (bulan sebelumnya)
'salary_cutoff_end_day' => 10,    // Tanggal akhir periode (bulan ini)
```

**Contoh Periode:**
- Periode Agustus 2025: 11 Juli 2025 - 10 Agustus 2025
- Total hari: 31 hari
- Weekend: ~8-9 hari
- **Hari kerja: 23 hari**

## Implementasi Kode

### 1. Service Class
File: [`app/Services/SalaryPeriodService.php`](app/Services/SalaryPeriodService.php)

#### Method Utama:
```php
public function getWorkingDaysInPeriod($startDate, $endDate): int
{
    $workingDays = 0;
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        if (!$date->isWeekend()) {  // Exclude Saturday & Sunday
            $workingDays++;
        }
    }
    return $workingDays;
}
```

#### Mendapatkan Periode Saat Ini:
```php
public function getCurrentPeriod($date = null): array
{
    $date = $date ? Carbon::parse($date) : now();
    $startDay = (int) $this->getSetting('salary_cutoff_start_day', 11);
    $endDay = (int) $this->getSetting('salary_cutoff_end_day', 10);
    
    // Logic untuk menentukan periode berdasarkan tanggal saat ini
    if ($date->day >= $startDay) {
        $periodStart = $date->copy()->day($startDay);
        $periodEnd = $date->copy()->addMonth()->day($endDay);
    } else {
        $periodStart = $date->copy()->subMonth()->day($startDay);
        $periodEnd = $date->copy()->day($endDay);
    }
    
    return [
        'start' => $periodStart,
        'end' => $periodEnd,
        'name' => $this->getPeriodName($periodStart, $periodEnd),
        'working_days' => $this->getWorkingDaysInPeriod($periodStart, $periodEnd)
    ];
}
```

### 2. Controller Integration
File: [`app/Http/Controllers/EmployeeController.php`](app/Http/Controllers/EmployeeController.php)

```php
// Mendapatkan status gaji untuk semua karyawan
$salaryStatuses = $this->salaryPeriodService->getAllEmployeesSalaryStatus();
$salaryStatusesKeyed = $salaryStatuses->keyBy('employee_id');
```

### 3. View Component
File: [`resources/views/components/salary-status-indicator.blade.php`](resources/views/components/salary-status-indicator.blade.php)

```php
<span class="text-sm font-medium text-gray-900">
    {{ $status['input_days'] ?? 0 }}/{{ $status['working_days'] ?? 0 }} - {{ $status['percentage'] ?? 0 }}%
</span>
```

## Status Gaji

### Kategori Status:
1. **Complete** (Lengkap): ≥90% hari kerja sudah diinput
2. **Partial** (Kurang): 50-89% hari kerja sudah diinput  
3. **Empty** (Belum): <50% hari kerja sudah diinput

### Threshold Configuration:
```php
'salary_status_complete_threshold' => 90,  // Persentase untuk status "Lengkap"
'salary_status_partial_threshold' => 50,   // Persentase untuk status "Kurang"
```

## Database Schema

### Settings Table
Menyimpan konfigurasi sistem:
```sql
key: 'salary_cutoff_start_day', value: '11'
key: 'salary_cutoff_end_day', value: '10'
key: 'salary_status_complete_threshold', value: '90'
key: 'salary_status_partial_threshold', value: '50'
```

### Daily Salaries Table
Menyimpan data gaji harian karyawan:
- `employee_id`: ID karyawan
- `work_date`: Tanggal kerja
- `status`: Status konfirmasi ('confirmed', 'pending', etc.)

## Contoh Perhitungan

### Skenario 1: Periode Agustus 2025
- **Periode**: 11 Juli 2025 - 10 Agustus 2025
- **Total hari**: 31 hari
- **Weekend**: 8 hari (4 Sabtu + 4 Minggu)
- **Hari kerja**: 23 hari

### Skenario 2: Karyawan dengan 4 hari input
- **Input days**: 4 hari
- **Working days**: 23 hari
- **Persentase**: (4/23) × 100% = 17.4%
- **Status**: Empty (karena <50%)
- **Display**: "4/23 - 17.4%"

## Cara Mengubah Konfigurasi

### Melalui Database:
```sql
UPDATE settings SET value = '15' WHERE key = 'salary_cutoff_start_day';
UPDATE settings SET value = '14' WHERE key = 'salary_cutoff_end_day';
```

### Melalui Code:
```php
Setting::set('salary_cutoff_start_day', 15, 'Tanggal mulai periode gaji', 'integer');
Setting::set('salary_cutoff_end_day', 14, 'Tanggal akhir periode gaji', 'integer');
```

## FAQ

**Q: Mengapa angka "23" berubah-ubah?**
A: Karena jumlah hari kerja berbeda setiap periode tergantung jumlah weekend dalam rentang tanggal tersebut.

**Q: Apakah hari libur nasional dihitung?**
A: Saat ini sistem hanya mengecualikan weekend (Sabtu-Minggu). Hari libur nasional belum diimplementasikan.

**Q: Bagaimana jika ingin mengubah periode cut-off?**
A: Ubah nilai `salary_cutoff_start_day` dan `salary_cutoff_end_day` di tabel settings.

**Q: Apakah bisa custom hari kerja per karyawan?**
A: Saat ini belum ada fitur custom hari kerja per karyawan. Semua menggunakan standar Senin-Jumat.

## Maintenance

### Cache Clearing
Settings di-cache selama 1 jam. Untuk clear cache:
```php
Cache::forget("setting_salary_cutoff_start_day");
Cache::forget("setting_salary_cutoff_end_day");
```

### Performance Optimization
- Method `getAllEmployeesSalaryStatus()` menggunakan single query dengan groupBy untuk efisiensi
- Data salary di-cache per employee untuk menghindari N+1 query problem