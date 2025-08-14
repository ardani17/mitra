# Rencana Simplifikasi Sistem Jadwal Kerja

## Konsep Baru

User meminta untuk menghapus fitur jadwal kerja yang kompleks dan hanya menggunakan sistem hari libur. Konsep yang diinginkan:

1. **Default**: Semua hari adalah hari kerja
2. **Hari Libur**: User hanya perlu set hari libur untuk karyawan
3. **Otomatis**: Hari yang tidak libur = hari kerja secara otomatis
4. **Sederhana**: Tidak perlu kompleksitas jadwal kerja standard/custom/flexible

## Perubahan yang Diperlukan

### 1. Hapus Work Schedule System
- Hapus routes work schedule
- Hapus controller methods work schedule
- Hapus views work schedule
- Hapus referensi work schedule dari Employee model

### 2. Simplifikasi Custom Off Days
- Rename menjadi "Hari Libur" saja (bukan "Custom Off Days")
- Fokus pada fungsi utama: menentukan hari libur karyawan
- Semua hari selain hari libur = hari kerja

### 3. Update Logic Perhitungan
- SalaryPeriodService: hitung hari kerja = total hari - hari libur - weekend
- Employee model: method untuk get hari libur per periode
- Tidak perlu lagi cek work schedule, langsung cek hari libur

### 4. Update UI/UX
- Hapus menu "Jadwal Kerja" 
- Ubah "Hari Libur Custom" menjadi "Hari Libur"
- Simplifikasi interface untuk fokus pada hari libur

## Implementasi Plan

### Phase 1: Backend Changes
1. Update SalaryPeriodService
2. Update Employee model
3. Hapus work schedule routes
4. Update custom off days controller

### Phase 2: Frontend Changes  
1. Update employee show view
2. Simplifikasi custom off days views
3. Hapus work schedule views
4. Update navigation/menu

### Phase 3: Testing
1. Test perhitungan hari kerja
2. Test CRUD hari libur
3. Test integrasi dengan salary system
4. Verify semua fitur berfungsi

## Expected Benefits

1. **Simplicity**: Sistem lebih sederhana dan mudah dipahami
2. **User-Friendly**: User hanya perlu fokus pada hari libur
3. **Maintenance**: Lebih mudah maintain karena less complexity
4. **Performance**: Lebih cepat karena tidak perlu cek multiple conditions

## Files to Modify

### Backend
- `app/Services/SalaryPeriodService.php`
- `app/Models/Employee.php`
- `routes/web.php`
- `app/Http/Controllers/EmployeeCustomOffDayController.php`

### Frontend
- `resources/views/employees/show.blade.php`
- `resources/views/employees/custom-off-days/*.blade.php`
- Remove: `resources/views/employees/work-schedules/*.blade.php`

### Database
- Keep: `employee_custom_off_days` table
- Optional: Drop `employee_work_schedules` table (or keep for data history)

## Migration Strategy

1. **Backward Compatibility**: Keep existing data
2. **Gradual Migration**: Phase out work schedule features
3. **Data Preservation**: Don't lose existing off days data
4. **Testing**: Thorough testing before deployment

## New User Flow

1. User goes to Employee detail page
2. User clicks "Hari Libur" button
3. User can add/edit/delete hari libur for employee
4. System automatically calculates working days = all days - off days - weekends
5. Salary calculation uses this working days count

## Technical Implementation

### Working Days Calculation Logic
```php
// Old (complex)
$workingDays = $employee->getWorkingDaysForPeriod($startDate, $endDate);

// New (simple)
$totalDays = $startDate->diffInDays($endDate) + 1;
$weekends = $this->countWeekends($startDate, $endDate);
$offDays = $employee->getOffDaysForPeriod($startDate, $endDate);
$workingDays = $totalDays - $weekends - $offDays;
```

### Database Schema
```sql
-- Keep this table
employee_custom_off_days:
- id
- employee_id  
- off_date
- reason
- type
- is_paid
- notes
- created_at
- updated_at

-- Optional: Drop this table
employee_work_schedules (can be dropped or kept for history)
```

## Success Criteria

1. ✅ User can manage hari libur easily
2. ✅ Working days calculated correctly
3. ✅ Salary calculation works properly
4. ✅ All CRUD operations for hari libur work
5. ✅ UI is clean and simple
6. ✅ No broken functionality
7. ✅ Performance is good or better