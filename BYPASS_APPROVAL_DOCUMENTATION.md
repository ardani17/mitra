# Dokumentasi Fitur Bypass Approval untuk Direktur

## Overview

Fitur Bypass Approval memungkinkan direktur untuk melewati workflow approval pengeluaran ketika mereka membuat expense. Fitur ini dirancang untuk memberikan fleksibilitas kepada direktur saat belum ada finance manager dan project manager, namun tetap mempertahankan sistem approval untuk masa depan.

## Fitur Utama

### 1. Setting Toggle
- **Lokasi**: Pengaturan Sistem (hanya dapat diakses direktur)
- **Fungsi**: Mengaktifkan/menonaktifkan fitur bypass approval
- **Default**: Nonaktif (false)

### 2. Automatic Bypass
- Ketika bypass aktif dan direktur membuat expense, status langsung menjadi "approved"
- Tidak ada approval workflow yang dibuat
- Semua actions ter-log untuk audit trail

### 3. User Interface Indicators
- Halaman create expense menampilkan status bypass
- Notifikasi berbeda berdasarkan kondisi:
  - Hijau: Bypass aktif, expense akan langsung disetujui
  - Kuning: User adalah direktur tapi bypass belum aktif
  - Biru: User biasa, workflow normal

## Komponen yang Diimplementasikan

### 1. Database
- **Tabel**: `settings`
- **Key**: `expense_director_bypass_enabled`
- **Migration**: `2025_08_14_124122_create_settings_table.php`
- **Seeder**: `SettingsSeeder.php`

### 2. Models
- **Setting.php**: Model untuk menyimpan konfigurasi sistem
  - `isDirectorBypassEnabled()`: Cek status bypass
  - `setDirectorBypass()`: Set status bypass
  - Caching untuk performa

### 3. Services
- **BypassApprovalService.php**: Service untuk handle logic bypass
  - `canBypass()`: Cek apakah user bisa bypass
  - `shouldBypassExpenseApproval()`: Cek apakah expense harus bypass
  - `logBypassAction()`: Log audit trail
  - `getBypassInfo()`: Get informasi status bypass

### 4. Controllers
- **SettingController.php**: Manage pengaturan bypass
  - `index()`: Halaman pengaturan
  - `updateDirectorBypass()`: Update setting bypass
  - Middleware: Hanya direktur yang bisa akses

- **ExpenseController.php**: Modified untuk handle bypass
  - `store()`: Cek bypass sebelum create expense
  - `create()`: Kirim bypass info ke view
  - `createApprovalWorkflow()`: Skip workflow jika bypass

### 5. Views
- **settings/index.blade.php**: Interface pengaturan bypass
- **expenses/create.blade.php**: Modified untuk show bypass status
- **layouts/navigation.blade.php**: Link ke pengaturan sistem

### 6. Routes
- `/settings`: Halaman pengaturan (direktur only)
- `/settings/director-bypass`: Update bypass setting
- API endpoint untuk get settings

## Cara Penggunaan

### Mengaktifkan Bypass
1. Login sebagai direktur
2. Buka menu "Manajemen" → "Pengaturan Sistem"
3. Aktifkan toggle "Bypass Approval Direktur"
4. Klik "Simpan"

### Membuat Expense dengan Bypass
1. Pastikan bypass sudah aktif
2. Buka "Pengeluaran" → "Ajukan Pengeluaran Baru"
3. Akan muncul notifikasi hijau bahwa expense akan langsung disetujui
4. Isi form dan submit
5. Expense langsung berstatus "approved"

### Menonaktifkan Bypass
1. Buka "Pengaturan Sistem"
2. Nonaktifkan toggle "Bypass Approval Direktur"
3. Klik "Simpan"
4. Expense selanjutnya akan mengikuti workflow normal

## Security & Audit

### Logging
Semua bypass actions ter-log dengan informasi:
- User ID dan nama
- Expense ID dan amount
- Timestamp
- Status bypass saat action

### Access Control
- Hanya direktur yang bisa akses pengaturan
- Middleware `role:direktur` melindungi routes
- Policy checks di controller

### Audit Trail
- Log tersimpan di Laravel log files
- Dapat dimonitor untuk compliance
- Tracking perubahan setting bypass

## Konfigurasi Tambahan

### Settings Lain yang Terkait
- `expense_high_amount_threshold`: Batas nilai tinggi (default: 10 juta)
- `expense_approval_notification_enabled`: Notifikasi email approval

### Environment
Tidak ada environment variables khusus yang diperlukan.

## Testing

### Manual Testing
1. **Test Bypass Aktif**:
   - Login sebagai direktur
   - Aktifkan bypass di settings
   - Buat expense baru
   - Verify status langsung "approved"

2. **Test Bypass Nonaktif**:
   - Nonaktifkan bypass
   - Buat expense baru
   - Verify workflow approval normal

3. **Test Non-Direktur**:
   - Login sebagai role lain
   - Verify tidak bisa akses settings
   - Verify workflow normal

### Log Verification
Check Laravel logs untuk memastikan bypass actions ter-record:
```bash
tail -f storage/logs/laravel.log | grep "bypass"
```

## Troubleshooting

### Bypass Tidak Bekerja
1. Cek apakah user benar-benar memiliki role 'direktur'
2. Cek setting `expense_director_bypass_enabled` di database
3. Clear cache: `php artisan cache:clear`

### Setting Tidak Tersimpan
1. Cek permission database
2. Cek Laravel logs untuk error
3. Verify middleware role direktur

### UI Tidak Update
1. Clear view cache: `php artisan view:clear`
2. Clear route cache: `php artisan route:clear`
3. Refresh browser cache

## Future Enhancements

### Possible Improvements
1. **Email Notifications**: Notifikasi ketika bypass digunakan
2. **Approval Limits**: Batas nilai untuk bypass
3. **Time-based Bypass**: Bypass hanya aktif di jam tertentu
4. **Multi-level Bypass**: Bypass berbeda untuk nilai berbeda
5. **Approval History**: Dashboard untuk track bypass usage

### Database Optimizations
1. Index pada settings table untuk performa
2. Separate audit table untuk bypass logs
3. Archive old bypass logs

## Maintenance

### Regular Tasks
1. Monitor bypass usage melalui logs
2. Review setting changes secara berkala
3. Backup database settings
4. Update dokumentasi jika ada perubahan

### Performance Considerations
- Settings di-cache untuk mengurangi database queries
- Service class untuk reusable logic
- Efficient database queries dengan proper indexing

---

**Dibuat**: 14 Agustus 2025  
**Versi**: 1.0  
**Author**: Kilo Code  
**Status**: Implemented & Ready for Production