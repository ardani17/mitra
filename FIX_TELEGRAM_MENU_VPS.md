# Panduan Fix Menu Telegram Bot di VPS

## Masalah
Menu Telegram Bot tidak muncul di navbar setelah deploy ke VPS, padahal di local sudah muncul.

## Penyebab
Menu Telegram Bot hanya ditampilkan untuk user dengan role **"direktur"** (lihat `navigation.blade.php` baris 151):
```php
@if(auth()->user()->hasRole('direktur'))
```

## Solusi

### 1. Jalankan Script Fix di VPS
```bash
chmod +x fix-telegram-menu-vps.sh
./fix-telegram-menu-vps.sh
```

### 2. Periksa Role User Anda
Jalankan di terminal VPS:
```bash
php artisan tinker
```

Kemudian ketik:
```php
$user = App\Models\User::where('email', 'email_anda@example.com')->first();
$user->roles->pluck('name');
$user->hasRole('direktur');
exit
```

### 3. Jika User Tidak Punya Role Direktur

#### Option A: Tambahkan Role Direktur ke User
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'email_anda@example.com')->first();
$role = Spatie\Permission\Models\Role::where('name', 'direktur')->first();
if (!$role) {
    $role = Spatie\Permission\Models\Role::create(['name' => 'direktur']);
}
$user->assignRole('direktur');
exit
```

#### Option B: Ubah Permission di Navigation (Tidak Disarankan untuk Production)
Edit `resources/views/layouts/navigation.blade.php` baris 151:

Dari:
```php
@if(auth()->user()->hasRole('direktur'))
```

Menjadi (untuk semua user):
```php
@if(auth()->check())
```

Atau untuk multiple roles:
```php
@if(auth()->user()->hasAnyRole(['direktur', 'admin', 'finance_manager']))
```

### 4. Clear Cache Setelah Perubahan
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 5. Rebuild Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. Restart Services (jika menggunakan)
```bash
# Jika menggunakan PHP-FPM
sudo systemctl restart php8.2-fpm

# Jika menggunakan Nginx
sudo systemctl restart nginx

# Jika menggunakan Apache
sudo systemctl restart apache2
```

## Verifikasi

1. **Logout dan Login kembali**
2. **Periksa browser console** untuk error JavaScript
3. **Periksa Laravel log** di `storage/logs/laravel.log`

## Struktur Menu Telegram Bot

Menu Telegram Bot terletak di dalam dropdown "Manajemen" yang hanya muncul untuk role "direktur":

```
Manajemen (Dropdown)
├── Manajemen User
├── Pengaturan Sistem  
├── Statistik Sistem
└── Tools
    ├── Bot Configuration
    ├── File Explorer     <- Yang Anda cari
    └── Bot Activity
```

## Troubleshooting Tambahan

### Jika masih tidak muncul:

1. **Periksa file navigation.blade.php di server**:
```bash
cat resources/views/layouts/navigation.blade.php | grep -A 20 "telegram-bot"
```

2. **Periksa compiled view**:
```bash
ls -la storage/framework/views/
# Hapus semua compiled views
rm -rf storage/framework/views/*
```

3. **Periksa route tersedia**:
```bash
php artisan route:list | grep telegram
```

4. **Debug di blade template**:
Tambahkan debug di `navigation.blade.php` sebelum baris 151:
```php
@php
    dump(auth()->user()->roles->pluck('name'));
    dump(auth()->user()->hasRole('direktur'));
@endphp
```

## Kesimpulan

Masalah utama adalah **permission/role**. Menu Telegram Bot hanya muncul untuk user dengan role "direktur". Pastikan:
1. User Anda memiliki role "direktur"
2. Cache sudah di-clear dan di-rebuild
3. File navigation.blade.php sudah ter-update di server

Jika Anda ingin menu muncul untuk semua user atau role tertentu, sesuaikan kondisi `@if` di navigation.blade.php.