# 📋 PANDUAN PEMULIHAN DATA CASHFLOW

## 🔴 Status Saat Ini
- **Data cashflow_categories**: TERHAPUS karena perintah `truncate()` di seeder
- **Backup**: Tidak tersedia
- **Pemulihan otomatis**: Tidak memungkinkan

## ✅ Langkah Pemulihan

### 1. Jalankan Seeder yang Sudah Diperbaiki
```bash
php artisan db:seed --class=CashflowCategorySeeder
```
Ini akan mengembalikan kategori-kategori cashflow (tapi bukan data transaksi).

### 2. Input Ulang Data Transaksi
Karena data transaksi hilang, Anda harus:

#### A. Cek Sumber Data Alternatif:
- ✉️ **Email**: Cari laporan cashflow yang pernah dikirim
- 📊 **Excel/CSV**: Cek file backup manual di komputer
- 📱 **WhatsApp/Telegram**: Cari screenshot laporan
- 📄 **Dokumen Fisik**: Nota, invoice, kwitansi

#### B. Minta Data dari Tim:
- Finance/Accounting
- Admin
- Project Manager

#### C. Input Manual:
1. Login ke sistem
2. Buka menu **Finance → Cashflow**
3. Klik **Tambah Transaksi**
4. Input data satu per satu

## 🛡️ PENCEGAHAN UNTUK MASA DEPAN

### 1. Setup Backup Otomatis

#### A. Backup Manual (Jalankan Setiap Hari)
```bash
# Backup seluruh database
php artisan backup:database

# Backup tabel tertentu saja
php artisan backup:database --tables="cashflow_entries cashflow_categories"
```

#### B. Setup Cron Job untuk Backup Otomatis
Edit crontab:
```bash
crontab -e
```

Tambahkan baris ini:
```bash
# Backup setiap hari jam 2 pagi
0 2 * * * cd /path/to/your/project && php artisan backup:database
```

### 2. Backup Sebelum Operasi Database
```bash
# SELALU backup sebelum:
# - Running migrations
# - Running seeders  
# - Update sistem

php artisan backup:database
```

### 3. Export Data Berkala
1. Login ke sistem
2. Menu **Finance → Cashflow**
3. Klik **Export** → Pilih Excel/CSV
4. Simpan file di tempat aman

### 4. Gunakan Migration, BUKAN Seeder untuk Production
```php
// ❌ JANGAN gunakan seeder dengan truncate() di production
DB::table('cashflow_categories')->truncate(); // BAHAYA!

// ✅ Gunakan migration atau seeder yang aman
if (!DB::table('cashflow_categories')->where('code', $code)->exists()) {
    DB::table('cashflow_categories')->insert($data);
}
```

## 📝 Checklist Pemulihan

- [ ] Jalankan seeder yang sudah diperbaiki
- [ ] Cek email untuk laporan lama
- [ ] Cek file Excel/CSV backup
- [ ] Cek WhatsApp/screenshot
- [ ] Kumpulkan dokumen fisik
- [ ] Koordinasi dengan tim
- [ ] Input ulang data transaksi
- [ ] Setup backup otomatis
- [ ] Test backup restore

## ⚠️ PERINGATAN PENTING

### JANGAN PERNAH di Production:
1. ❌ Gunakan `truncate()` atau `delete()` tanpa where
2. ❌ Jalankan seeder tanpa review code
3. ❌ Skip backup sebelum operasi database
4. ❌ Gunakan `--force` tanpa yakin 100%

### SELALU di Production:
1. ✅ Backup dulu sebelum operasi database
2. ✅ Test di development/staging dulu
3. ✅ Review code seeder/migration
4. ✅ Simpan backup di tempat terpisah

## 📞 Bantuan

Jika butuh bantuan lebih lanjut:
1. Hubungi developer/IT support
2. Cek dokumentasi Laravel
3. Gunakan command backup yang sudah dibuat

## 🔧 Command Backup yang Tersedia

```bash
# Lihat semua backup
ls -la storage/app/backups/

# Backup manual
php artisan backup:database

# Restore backup (manual)
mysql -u username -p database_name < storage/app/backups/backup-file.sql
```

---

**INGAT**: Data yang hilang tidak bisa dikembalikan tanpa backup. 
Mulai sekarang, SELALU backup sebelum operasi database!