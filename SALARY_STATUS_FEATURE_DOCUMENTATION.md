# 📊 Dokumentasi Fitur Status Gaji Karyawan

## 🎯 Overview

Fitur Status Gaji Karyawan adalah sistem yang memberikan indikator visual dan popup informatif untuk membantu admin HR mengetahui status input gaji karyawan berdasarkan periode cut-off yang dapat dikonfigurasi.

## ✨ Fitur Utama

### 1. **Cut-off Period Kustomisasi**
- Sistem mendukung periode gaji custom (default: tanggal 11 bulan lalu - 10 bulan ini)
- Dapat dikonfigurasi melalui settings
- Otomatis menghitung hari kerja (exclude weekend)

### 2. **Indikator Visual di Daftar Karyawan**
- Format: `18/22 - 82%` (hari input/total hari kerja - persentase)
- Status badge: Lengkap (hijau), Kurang (kuning), Belum (merah)
- Tanggal input terakhir

### 3. **Popup Status Gaji**
- Ringkasan statistik periode berjalan
- Daftar karyawan yang perlu perhatian
- Quick action buttons untuk input gaji
- Auto-refresh setiap 5 menit

## 🏗️ Arsitektur Sistem

### Backend Components

#### 1. **SalaryPeriodService** (`app/Services/SalaryPeriodService.php`)
```php
// Core methods:
- getCurrentPeriod()           // Get current salary period
- getPeriodForMonth()          // Get specific month period
- getAllEmployeesSalaryStatus() // Get all employees status
- getSalaryStatusSummary()     // Get summary statistics
- getCalendarData()            // Get calendar view data
```

#### 2. **EmployeeController** (Enhanced)
```php
// New API endpoints:
- getSalaryStatusSummary()     // API: /finance/api/employees/salary-status-summary
- getSalaryStatusDetail()      // API: /finance/api/employees/salary-status-detail
- getSalaryCalendar()          // API: /finance/api/employees/salary-calendar
```

#### 3. **Settings Configuration**
```php
// New settings keys:
- salary_cutoff_start_day      // Default: 11
- salary_cutoff_end_day        // Default: 10
- salary_status_complete_threshold  // Default: 90%
- salary_status_partial_threshold   // Default: 50%
- salary_status_auto_refresh   // Default: enabled
- salary_status_email_notification  // Default: enabled
```

### Frontend Components

#### 1. **Salary Status Indicator** (`resources/views/components/salary-status-indicator.blade.php`)
```blade
<x-salary-status-indicator :status="$salaryStatus" />
```

#### 2. **Enhanced Employee Index** (`resources/views/employees/index.blade.php`)
- New "Status Gaji" column
- "Status Gaji" button for popup
- JavaScript SalaryStatusManager class

#### 3. **JavaScript SalaryStatusManager**
```javascript
// Core functionality:
- showModal()                  // Display status popup
- renderModal()                // Render popup content
- refreshStatusIndicators()    // Auto-refresh indicators
- renderEmployeeList()         // Show priority employees
```

## 🗄️ Database Schema

### Settings Table (Enhanced)
```sql
-- New settings for salary cut-off
INSERT INTO settings (key, value, description, type) VALUES 
('salary_cutoff_start_day', '11', 'Tanggal mulai periode gaji (1-31)', 'integer'),
('salary_cutoff_end_day', '10', 'Tanggal akhir periode gaji (1-31)', 'integer'),
('salary_status_complete_threshold', '90', 'Persentase minimum untuk status lengkap (%)', 'integer'),
('salary_status_partial_threshold', '50', 'Persentase minimum untuk status kurang (%)', 'integer');
```

### Existing Tables Used
- `employees` - Employee data
- `daily_salaries` - Daily salary records
- `settings` - Configuration settings

## 🔧 Konfigurasi Cut-off Period

### Contoh Periode Gaji:
```
Periode Januari 2025: 11 Desember 2024 - 10 Januari 2025
Periode Februari 2025: 11 Januari 2025 - 10 Februari 2025
Periode Maret 2025: 11 Februari 2025 - 10 Maret 2025
```

### Logika Perhitungan:
```php
// Jika tanggal sekarang >= start day, periode dimulai bulan ini
if ($date->day >= $startDay) {
    $periodStart = $date->copy()->day($startDay);
    $periodEnd = $date->copy()->addMonth()->day($endDay);
} else {
    // Jika tanggal sekarang < start day, periode dimulai bulan lalu
    $periodStart = $date->copy()->subMonth()->day($startDay);
    $periodEnd = $date->copy()->day($endDay);
}
```

## 📊 Status Classification

### Status Levels:
- **Lengkap (Complete)**: ≥90% dari hari kerja sudah diinput
- **Kurang (Partial)**: 50-89% dari hari kerja sudah diinput  
- **Belum (Empty)**: <50% dari hari kerja sudah diinput

### Visual Indicators:
- 🟢 **Hijau**: Status Lengkap
- 🟡 **Kuning**: Status Kurang
- 🔴 **Merah**: Status Belum

## 🚀 API Endpoints

### 1. Salary Status Summary
```
GET /finance/api/employees/salary-status-summary
Parameters: month, year (optional)
Response: {
  period: {...},
  total: 15,
  complete: 5,
  partial: 7,
  empty: 3,
  employees: [...]
}
```

### 2. Salary Status Detail
```
GET /finance/api/employees/salary-status-detail
Parameters: month, year (optional)
Response: [
  {
    employee_id: 1,
    name: "Ahmad Rizki",
    working_days: 22,
    input_days: 18,
    percentage: 81.8,
    status: "partial"
  }
]
```

### 3. Salary Calendar
```
GET /finance/api/employees/salary-calendar
Parameters: month, year, employee_id (optional)
Response: {
  period: {...},
  calendar: [
    {
      date: "2025-01-15",
      status: "complete",
      icon: "✅"
    }
  ]
}
```

## 🎨 UI/UX Features

### Employee List Enhancements:
1. **New Column**: "Status Gaji" dengan indikator visual
2. **Status Button**: Purple button "📊 Status Gaji"
3. **Real-time Updates**: Auto-refresh setiap 5 menit

### Popup Modal Features:
1. **Summary Statistics**: Total, lengkap, kurang, belum
2. **Priority List**: Karyawan yang perlu perhatian
3. **Quick Actions**: Tombol langsung ke input gaji
4. **Period Info**: Menampilkan periode dan hari kerja

### Responsive Design:
- **Desktop**: Full layout dengan semua kolom
- **Tablet**: Compact view dengan icon status
- **Mobile**: Stack layout dengan status di bawah nama

## ⚡ Performance Optimizations

### 1. Efficient Queries
```php
// Single query untuk semua employee status
$salaryData = DailySalary::whereBetween('work_date', [$period['start'], $period['end']])
    ->where('status', 'confirmed')
    ->selectRaw('employee_id, COUNT(*) as input_days, MAX(work_date) as last_input_date')
    ->groupBy('employee_id')
    ->get()
    ->keyBy('employee_id');
```

### 2. Caching Strategy (Future Enhancement)
- Cache salary status per employee per month
- Invalidate cache when new salary input is added
- Use Redis for better performance

### 3. Frontend Optimizations
- Lazy load modal content
- Debouncing for real-time updates
- Efficient DOM updates

## 🔄 Workflow Integration

### 1. Employee Management Flow:
```
Employee List → Status Indicator → Quick Action → Input Gaji
```

### 2. Status Monitoring Flow:
```
Status Button → Popup Modal → Priority List → Direct Action
```

### 3. Notification Flow (Future):
```
Low Status Detection → Email Notification → Manager Alert
```

## 🧪 Testing Scenarios

### 1. Cut-off Period Testing:
- Test dengan tanggal 11-10 (default)
- Test dengan periode custom
- Test transisi antar bulan

### 2. Status Calculation Testing:
- Employee dengan 0% input (Belum)
- Employee dengan 60% input (Kurang)  
- Employee dengan 95% input (Lengkap)

### 3. UI/UX Testing:
- Popup modal functionality
- Real-time updates
- Mobile responsiveness

## 📝 Usage Examples

### 1. Mengecek Status Gaji Bulan Ini:
1. Buka halaman "Manajemen Karyawan"
2. Lihat kolom "Status Gaji" untuk setiap karyawan
3. Klik tombol "📊 Status Gaji" untuk detail lengkap

### 2. Input Gaji untuk Karyawan Prioritas:
1. Klik "📊 Status Gaji"
2. Lihat section "🔴 Prioritas Tinggi"
3. Klik "📝 Input Sekarang" untuk karyawan yang dipilih

### 3. Monitoring Progress Bulanan:
1. Popup modal menampilkan progress keseluruhan
2. Statistik real-time: Total, Lengkap, Kurang, Belum
3. Auto-refresh setiap 5 menit

## 🔮 Future Enhancements

### 1. Advanced Calendar View:
- Full calendar dengan status per tanggal
- Click-to-input functionality
- Drag & drop salary input

### 2. Email Notifications:
- Daily reminder untuk status rendah
- Weekly summary untuk manager
- Custom notification rules

### 3. Analytics Dashboard:
- Trend analysis per bulan
- Department-wise statistics
- Performance metrics

### 4. Mobile App Integration:
- Push notifications
- Quick input via mobile
- Offline capability

## 🛠️ Maintenance

### 1. Regular Tasks:
- Monitor performance metrics
- Update cut-off settings if needed
- Review notification effectiveness

### 2. Database Maintenance:
- Archive old salary status cache
- Optimize queries for large datasets
- Monitor index performance

### 3. Feature Updates:
- Collect user feedback
- Implement requested enhancements
- Update documentation

---

## 📞 Support

Untuk pertanyaan atau masalah terkait fitur ini, silakan hubungi tim development atau buat issue di repository project.

**Fitur ini telah diimplementasi dan siap digunakan!** 🎉