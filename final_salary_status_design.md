# 🎯 Desain Final: Status Gaji dengan Cut-off Period

## 📋 Spesifikasi Final

### ✅ **Yang Dipilih**: Opsi 2 Modified
- **Indikator**: Persentase tanpa progress bar (contoh: "12/22 - 55%")
- **Popup**: Kalender visual dengan status per tanggal
- **Cut-off**: Periode gaji custom (tgl 11 bulan lalu - 10 bulan ini)

---

## 🗓️ Sistem Cut-off Gaji

### Contoh Periode Gaji:
```
Periode Januari 2025: 11 Desember 2024 - 10 Januari 2025
Periode Februari 2025: 11 Januari 2025 - 10 Februari 2025
Periode Maret 2025: 11 Februari 2025 - 10 Maret 2025
```

### Konfigurasi Cut-off:
- **Start Day**: 11 (tanggal mulai periode)
- **End Day**: 10 (tanggal akhir periode)
- **Configurable**: Bisa diubah per perusahaan/sistem

---

## 🎨 Mockup Desain Final

### A. Indikator di Daftar Karyawan
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Manajemen Karyawan                                    [📊 Status Gaji] [+ Tambah] │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ Karyawan          │ Kontak        │ Posisi      │ Status Gaji │ Aksi        │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Ahmad Rizki    │ 081234567890  │ Developer   │ 18/22 - 82% │ 👁️ ✏️ 🗑️    │ │
│ │    EMP001         │ ahmad@...     │ IT          │ ✅ Lengkap  │             │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Siti Nurhaliza │ 081234567891  │ Designer    │ 12/22 - 55% │ 👁️ ✏️ 🗑️    │ │
│ │    EMP002         │ siti@...      │ IT          │ ⚠️ Kurang   │             │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Budi Santoso   │ 081234567892  │ Manager     │ 0/22 - 0%   │ 👁️ ✏️ 🗑️    │ │
│ │    EMP003         │ budi@...      │ Operations  │ ❌ Belum    │             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### B. Popup Kalender dengan Cut-off Period
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ 📊 Status Gaji - Periode Januari 2025 (11 Des 2024 - 10 Jan 2025)      [✖️]   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ 📈 Ringkasan Periode Ini:                                                      │
│ • Periode: 11 Desember 2024 - 10 Januari 2025 (22 hari kerja)                │
│ • Total Karyawan: 15                                                           │
│ • Sudah Lengkap (≥90%): 5 karyawan (33%)                                      │
│ • Masih Kurang (50-89%): 7 karyawan (47%)                                     │
│ • Belum Input (<50%): 3 karyawan (20%)                                        │
│                                                                                 │
│ 📅 Kalender Input Gaji:                                                        │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │                    DESEMBER 2024                                            │ │
│ │  S  M  T  W  T  F  S                                                        │ │
│ │  1  2  3  4  5  6  7                                                        │ │
│ │  8  9 10 [11][12][13][14]   ✅ = Semua karyawan sudah input                │ │
│ │[15][16]🔒🔒[19][20][21]     ⚠️ = Ada yang belum input                       │ │
│ │[22][23][24]🔒🔒[27][28]     ❌ = Belum ada input sama sekali                │ │
│ │[29][30][31]                 🔒 = Hari libur/weekend                        │ │
│ │                                                                             │ │
│ │                     JANUARI 2025                                            │ │
│ │  S  M  T  W  T  F  S                                                        │ │
│ │           [1] [2] [3] [4]                                                   │ │
│ │ 🔒🔒 [7] [8] [9][10]                                                        │ │
│ │                                                                             │ │
│ │ Status: ✅✅⚠️⚠️🔒🔒✅⚠️✅✅❌🔒🔒❌⚠️✅✅⚠️🔒🔒✅⚠️                              │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ⚠️ Perlu Perhatian:                                                            │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ ❌ Budi Santoso (EMP003) - 0/22 hari (0%)     [📝 Input Sekarang]         │ │
│ │ ❌ Rina Wati (EMP007) - 2/22 hari (9%)        [📝 Input Sekarang]         │ │
│ │ ⚠️ Siti Nurhaliza (EMP002) - 12/22 hari (55%) [📝 Lengkapi]               │ │
│ │ ⚠️ Andi Pratama (EMP005) - 15/22 hari (68%)   [📝 Lengkapi]               │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ 🎯 Progress Keseluruhan: 68% (15/22 hari rata-rata)                           │
│ 📅 Periode Berikutnya: 11 Januari - 10 Februari 2025                         │
│                                                                                 │
│                                    [📝 Input Gaji] [⚙️ Pengaturan] [Tutup]    │
│                                                                                 │
│ 💡 Tip: Klik tanggal di kalender untuk input gaji hari tersebut               │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Konfigurasi Cut-off Gaji

### Settings Page Enhancement
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚙️ Pengaturan Sistem Gaji                                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🗓️ Periode Cut-off Gaji:                                       │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Tanggal Mulai Periode: [11] ▼                              │ │
│ │ Tanggal Akhir Periode:  [10] ▼                             │ │
│ │                                                             │ │
│ │ Preview Periode:                                            │ │
│ │ • Januari 2025: 11 Des 2024 - 10 Jan 2025 (22 hari kerja) │ │
│ │ • Februari 2025: 11 Jan 2025 - 10 Feb 2025 (23 hari kerja)│ │
│ │ • Maret 2025: 11 Feb 2025 - 10 Mar 2025 (20 hari kerja)   │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📊 Pengaturan Status:                                          │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Status "Lengkap": ≥ [90]% dari hari kerja                  │ │
│ │ Status "Kurang":  [50]% - [89]% dari hari kerja            │ │
│ │ Status "Belum":   < [50]% dari hari kerja                  │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔄 Pengaturan Update:                                          │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ ☑️ Auto-refresh status setiap 5 menit                      │ │
│ │ ☑️ Notifikasi email untuk karyawan <50%                    │ │
│ │ ☑️ Highlight periode mendekati cut-off                     │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│                                          [💾 Simpan Pengaturan] │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema Update

### 1. Settings Table Enhancement
```sql
-- Tambah ke settings table yang sudah ada
INSERT INTO settings (key, value, description) VALUES 
('salary_cutoff_start_day', '11', 'Tanggal mulai periode gaji (1-31)'),
('salary_cutoff_end_day', '10', 'Tanggal akhir periode gaji (1-31)'),
('salary_status_complete_threshold', '90', 'Persentase minimum untuk status lengkap'),
('salary_status_partial_threshold', '50', 'Persentase minimum untuk status kurang'),
('salary_status_auto_refresh', '1', 'Auto refresh status (0=off, 1=on)'),
('salary_status_email_notification', '1', 'Email notification untuk status rendah');
```

### 2. Optional: Salary Period Cache Table
```sql
CREATE TABLE salary_period_cache (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_work_days INT DEFAULT 0,
    input_days INT DEFAULT 0,
    completion_percentage DECIMAL(5,2) DEFAULT 0,
    status ENUM('complete', 'partial', 'empty') DEFAULT 'empty',
    last_input_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_employee_period (employee_id, period_start, period_end),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_period (period_start, period_end),
    INDEX idx_status (status)
);
```

---

## 🔧 Backend Logic: Cut-off Period Calculation

### SalaryPeriodService
```php
<?php

class SalaryPeriodService
{
    public function getCurrentPeriod($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $startDay = (int) setting('salary_cutoff_start_day', 11);
        $endDay = (int) setting('salary_cutoff_end_day', 10);
        
        // Jika tanggal sekarang >= start day, periode dimulai bulan ini
        if ($date->day >= $startDay) {
            $periodStart = $date->copy()->day($startDay);
            $periodEnd = $date->copy()->addMonth()->day($endDay);
        } else {
            // Jika tanggal sekarang < start day, periode dimulai bulan lalu
            $periodStart = $date->copy()->subMonth()->day($startDay);
            $periodEnd = $date->copy()->day($endDay);
        }
        
        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'name' => $this->getPeriodName($periodStart, $periodEnd)
        ];
    }
    
    public function getPeriodName($startDate, $endDate)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Nama periode berdasarkan bulan akhir
        $endMonth = $months[$endDate->month];
        $endYear = $endDate->year;
        
        return "{$endMonth} {$endYear}";
    }
    
    public function getWorkingDaysInPeriod($startDate, $endDate)
    {
        $workingDays = 0;
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }
        return $workingDays;
    }
    
    public function getCalendarData($startDate, $endDate, $employeeId = null)
    {
        $calendar = [];
        $query = DailySalary::whereBetween('work_date', [$startDate, $endDate]);
        
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        
        $salaries = $query->get()->groupBy('work_date');
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayData = [
                'date' => $date->copy(),
                'is_weekend' => $date->isWeekend(),
                'has_input' => isset($salaries[$dateStr]),
                'input_count' => isset($salaries[$dateStr]) ? $salaries[$dateStr]->count() : 0,
                'total_employees' => Employee::active()->count()
            ];
            
            // Determine status
            if ($dayData['is_weekend']) {
                $dayData['status'] = 'weekend';
            } elseif ($dayData['input_count'] == 0) {
                $dayData['status'] = 'empty';
            } elseif ($dayData['input_count'] == $dayData['total_employees']) {
                $dayData['status'] = 'complete';
            } else {
                $dayData['status'] = 'partial';
            }
            
            $calendar[] = $dayData;
        }
        
        return $calendar;
    }
}
```

---

## 🎯 Implementasi Priority

### Phase 1: Core Functionality
1. ✅ Settings untuk cut-off configuration
2. ✅ SalaryPeriodService untuk period calculation
3. ✅ Basic indicator di employee list (persentase)

### Phase 2: Enhanced UI
1. ✅ Popup kalender dengan visual status
2. ✅ Real-time updates
3. ✅ Quick action buttons

### Phase 3: Advanced Features
1. ✅ Email notifications
2. ✅ Performance optimization dengan caching
3. ✅ Mobile responsive design

---

## 📱 Mobile Responsive Considerations

### Mobile Layout:
```
┌─────────────────────────┐
│ 👤 Ahmad Rizki          │
│    EMP001 - Developer   │
│    📊 18/22 - 82% ✅    │
├─────────────────────────┤
│ 👤 Siti Nurhaliza       │
│    EMP002 - Designer    │
│    📊 12/22 - 55% ⚠️    │
└─────────────────────────┘
```

### Mobile Popup:
- Collapsible sections
- Swipe navigation between months
- Touch-friendly calendar
- Bottom sheet design