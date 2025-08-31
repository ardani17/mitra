# Panduan Cleanup Seeder

## Seeder yang BISA DIHAPUS (Sudah ada di Migration)

### 1. ✅ RoleSeeder.php
- **Status**: BISA DIHAPUS
- **Alasan**: Sudah dipindahkan ke `2025_08_29_000001_insert_default_roles.php`
- **Data**: 4 roles (direktur, project_manager, finance_manager, staf)

### 2. ✅ SettingsSeeder.php  
- **Status**: BISA DIHAPUS
- **Alasan**: Sudah dipindahkan ke `2025_08_29_000002_insert_default_settings.php`
- **Data**: 9 settings default

### 3. ✅ CashflowCategorySeeder.php
- **Status**: BISA DIHAPUS/MODIFIKASI
- **Alasan**: Sudah dipindahkan ke `2025_08_29_000003_insert_default_cashflow_categories.php`
- **Data**: 41 kategori cashflow
- **Catatan**: Bisa diubah jadi seeder untuk data testing saja

### 4. ✅ UserSeeder.php
- **Status**: BISA DIHAPUS/MODIFIKASI  
- **Alasan**: User default sudah dipindahkan ke `2025_08_29_000004_insert_default_users.php`
- **Data**: 4 user default
- **Catatan**: Bisa diubah untuk user testing saja

## Seeder yang HARUS DIPERTAHANKAN (Untuk Testing/Development)

### 1. ❌ ProjectBillingSeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data dummy untuk testing
- **Gunakan**: Hanya di development/staging

### 2. ❌ ProjectExpenseSeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data dummy untuk testing
- **Gunakan**: Hanya di development/staging

### 3. ❌ ProjectRevenueSeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data dummy untuk testing
- **Gunakan**: Hanya di development/staging

### 4. ❌ ProjectTimelineSeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data dummy untuk testing
- **Gunakan**: Hanya di development/staging

### 5. ❌ TestEmployeeSeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data testing untuk modul employee
- **Gunakan**: Hanya di development/staging

### 6. ❌ DailySalarySeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data testing untuk modul salary
- **Gunakan**: Hanya di development/staging

### 7. ❌ CurrentPeriodSalarySeeder.php
- **Status**: PERTAHANKAN
- **Alasan**: Data testing untuk modul salary
- **Gunakan**: Hanya di development/staging

### 8. ⚠️ DatabaseSeeder.php
- **Status**: MODIFIKASI
- **Alasan**: Orchestrator untuk seeder lain
- **Action**: Update untuk hanya memanggil seeder testing

## Cara Menghapus/Rename Seeder

### Option 1: Rename (Recommended - untuk backup)
```bash
# Rename file yang sudah tidak diperlukan
mv database/seeders/RoleSeeder.php database/seeders/RoleSeeder.php.bak
mv database/seeders/SettingsSeeder.php database/seeders/SettingsSeeder.php.bak
mv database/seeders/CashflowCategorySeeder.php database/seeders/CashflowCategorySeeder.php.bak
mv database/seeders/UserSeeder.php database/seeders/UserSeeder.php.bak
```

### Option 2: Delete (Permanent)
```bash
# Hapus file seeder
rm database/seeders/RoleSeeder.php
rm database/seeders/SettingsSeeder.php
rm database/seeders/CashflowCategorySeeder.php
rm database/seeders/UserSeeder.php
```

### Option 3: Modifikasi DatabaseSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // PRODUCTION: Tidak perlu seeder, data master sudah di migration
        
        // DEVELOPMENT/TESTING ONLY - Uncomment jika perlu data dummy
        if (app()->environment(['local', 'staging'])) {
            // $this->call(ProjectBillingSeeder::class);
            // $this->call(ProjectTimelineSeeder::class);
            // $this->call(ProjectExpenseSeeder::class);
            // $this->call(ProjectRevenueSeeder::class);
            // $this->call(TestEmployeeSeeder::class);
            // $this->call(DailySalarySeeder::class);
        }
    }
}
```

## Testing Setelah Cleanup

### 1. Test Migration (Production Scenario)
```bash
# Drop dan recreate database
dropdb -U postgres mitra_test
createdb -U postgres mitra_test

# Jalankan HANYA migration (tanpa seeder)
php artisan migrate --database=pgsql_test

# Verifikasi data master ada
php artisan tinker
>>> DB::connection('pgsql_test')->table('roles')->count(); // Should be 4
>>> DB::connection('pgsql_test')->table('settings')->count(); // Should be 9
>>> DB::connection('pgsql_test')->table('cashflow_categories')->count(); // Should be 41
>>> DB::connection('pgsql_test')->table('users')->count(); // Should be 4
```

### 2. Test Login dengan User Default
```
Email: direktur@mitra.com
Password: password123
```

### 3. Verifikasi Aplikasi Berjalan Normal
- Login ke aplikasi
- Check dashboard
- Check cashflow categories
- Check settings

## Deployment Checklist

### Development/Local:
```bash
git pull
composer install
php artisan migrate
php artisan db:seed  # Optional untuk data testing
```

### Production:
```bash
git pull
composer install --no-dev
php artisan migrate  # Data master otomatis terinstal
# JANGAN jalankan db:seed di production!
php artisan config:cache
php artisan route:cache
```

## Summary

✅ **Seeder yang bisa dihapus**: RoleSeeder, SettingsSeeder, CashflowCategorySeeder, UserSeeder
❌ **Seeder yang dipertahankan**: Seeder untuk data testing/dummy
📝 **Best Practice**: Rename dulu untuk backup, jangan langsung delete
🚀 **Production**: Cukup `php artisan migrate` tanpa seeder