# 📋 PANDUAN PEMULIHAN DATA CASHFLOW - POSTGRESQL

## 🔴 Status Saat Ini
- **Database**: PostgreSQL
- **Data cashflow_categories**: TERHAPUS karena perintah `truncate()` di seeder
- **Backup**: Tidak tersedia
- **Pemulihan otomatis**: Tidak memungkinkan

## ✅ Langkah Pemulihan untuk PostgreSQL

### 1. Jalankan Seeder yang Sudah Diperbaiki
```bash
php artisan db:seed --class=CashflowCategorySeeder
```
Ini akan mengembalikan kategori-kategori cashflow (tapi bukan data transaksi).

### 2. Cek Apakah Ada Data di PostgreSQL Log
```bash
# Cek log PostgreSQL (lokasi tergantung instalasi)
sudo tail -n 1000 /var/log/postgresql/postgresql-*.log | grep -i "cashflow"

# Atau di Windows
type "C:\Program Files\PostgreSQL\14\data\log\postgresql-*.log" | findstr /i "cashflow"
```

### 3. Cek WAL (Write-Ahead Logging) PostgreSQL
Jika WAL archiving aktif, mungkin bisa recovery:
```sql
-- Cek konfigurasi WAL
SHOW wal_level;
SHOW archive_mode;
SHOW archive_command;
```

## 🛡️ SETUP BACKUP POSTGRESQL

### 1. Backup Manual dengan Command Artisan
```bash
# Backup seluruh database
php artisan backup:database

# Backup tabel tertentu
php artisan backup:database --tables="cashflow_entries,cashflow_categories"
```

### 2. Backup Manual dengan pg_dump
```bash
# Backup seluruh database
pg_dump -h localhost -U username -d database_name > backup.sql

# Backup tabel tertentu
pg_dump -h localhost -U username -d database_name -t cashflow_entries -t cashflow_categories > backup_cashflow.sql

# Backup dengan compression
pg_dump -h localhost -U username -d database_name -Fc > backup.dump
```

### 3. Restore Backup PostgreSQL
```bash
# Restore dari SQL file
psql -h localhost -U username -d database_name < backup.sql

# Restore dari dump file
pg_restore -h localhost -U username -d database_name backup.dump

# Restore tabel tertentu
psql -h localhost -U username -d database_name < backup_cashflow.sql
```

### 4. Setup Backup Otomatis dengan Cron
```bash
# Edit crontab
crontab -e

# Tambahkan untuk backup otomatis setiap hari jam 2 pagi
0 2 * * * cd /path/to/project && php artisan backup:database

# Atau langsung dengan pg_dump
0 2 * * * PGPASSWORD='password' pg_dump -h localhost -U username -d database_name > /backup/db_$(date +\%Y\%m\%d).sql
```

### 5. Setup Backup Otomatis di Windows (Task Scheduler)
```batch
@echo off
set PGPASSWORD=your_password
"C:\Program Files\PostgreSQL\14\bin\pg_dump.exe" -h localhost -U username -d database_name > "C:\backups\backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%.sql"
```

## 📝 Script Backup PostgreSQL Lengkap

Buat file `backup_postgres.sh`:
```bash
#!/bin/bash

# Konfigurasi
DB_HOST="localhost"
DB_PORT="5432"
DB_NAME="database_name"
DB_USER="username"
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Set password (atau gunakan .pgpass file)
export PGPASSWORD='your_password'

# Buat folder backup jika belum ada
mkdir -p $BACKUP_DIR

# Backup database
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME -Fc > "$BACKUP_DIR/backup_$DATE.dump"

# Hapus backup lama (lebih dari 30 hari)
find $BACKUP_DIR -name "backup_*.dump" -mtime +30 -delete

echo "Backup selesai: backup_$DATE.dump"
```

## 🔧 Command PostgreSQL Penting

```sql
-- Cek ukuran database
SELECT pg_database_size('database_name');

-- Cek ukuran tabel
SELECT pg_size_pretty(pg_total_relation_size('cashflow_entries'));

-- Cek aktivitas database
SELECT * FROM pg_stat_activity;

-- Cek lock
SELECT * FROM pg_locks;

-- Vacuum dan analyze (maintenance)
VACUUM ANALYZE cashflow_entries;
VACUUM ANALYZE cashflow_categories;
```

## ⚠️ Tips Khusus PostgreSQL

1. **Gunakan `.pgpass` file untuk password otomatis**
   ```bash
   # Buat file ~/.pgpass
   echo "localhost:5432:database:username:password" > ~/.pgpass
   chmod 600 ~/.pgpass
   ```

2. **Enable Point-in-Time Recovery (PITR)**
   ```sql
   -- Di postgresql.conf
   wal_level = replica
   archive_mode = on
   archive_command = 'cp %p /archive/%f'
   ```

3. **Gunakan pg_basebackup untuk full backup**
   ```bash
   pg_basebackup -h localhost -U username -D /backup/base -Fp -Xs -P
   ```

## 📋 Checklist Pemulihan PostgreSQL

- [ ] Jalankan seeder yang sudah diperbaiki
- [ ] Cek PostgreSQL log
- [ ] Cek WAL archives (jika ada)
- [ ] Setup backup dengan pg_dump
- [ ] Test restore backup
- [ ] Setup cron/scheduler untuk backup otomatis
- [ ] Konfigurasi .pgpass untuk automation
- [ ] Enable WAL archiving untuk PITR
- [ ] Input ulang data yang hilang

## 🚨 PERINGATAN KHUSUS POSTGRESQL

**JANGAN:**
- ❌ `TRUNCATE CASCADE` - akan hapus data terkait
- ❌ `DROP SCHEMA CASCADE` - akan hapus semua
- ❌ Skip `VACUUM` terlalu lama
- ❌ Matikan WAL tanpa backup

**SELALU:**
- ✅ Gunakan transaction untuk operasi berbahaya
- ✅ Test di database development dulu
- ✅ Monitor disk space untuk WAL
- ✅ Regular VACUUM dan ANALYZE

---

**INGAT**: PostgreSQL punya fitur recovery yang lebih baik dari MySQL, tapi tetap butuh setup yang benar. Mulai sekarang aktifkan WAL archiving dan backup rutin!