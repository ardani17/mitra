# 📋 Telegram Bot Integration - Setup Instructions

## ✅ Implementation Complete!

Semua komponen untuk integrasi Telegram Bot dengan Laravel telah berhasil dibuat.

## 🚀 Setup Steps

### 1. Run Database Migrations

Jalankan migrations untuk membuat tabel-tabel bot:

```bash
php artisan migrate
```

Ini akan membuat tabel-tabel berikut:
- `bot_configurations` - Konfigurasi bot
- `bot_user_sessions` - Session user telegram
- `bot_activities` - Log aktivitas bot
- `bot_command_history` - History command yang dijalankan
- `bot_upload_queue` - Queue untuk upload file

### 2. Clear Cache & Config

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Access Bot Configuration

1. Login sebagai user dengan role `direktur`
2. Buka menu **Manajemen** → **Tools** → **Bot Configuration**
3. Atau langsung akses: `/telegram-bot/config`

### 4. Configure Your Bot

Di halaman Bot Configuration, isi:

#### Bot Settings:
- **Bot Name**: Nama bot Anda
- **Bot Username**: Username bot (tanpa @)
- **Bot Token**: Token dari @BotFather

#### Server Configuration:
- **Server Host**: `localhost` (karena server telegram-bot-api sudah running)
- **Server Port**: `8081` (sesuai dengan server yang sudah jalan)
- **Use Local Server**: ✅ (untuk support file hingga 2GB)
- **Max File Size**: `2000` MB

#### Path Configuration:
- **Bot API Base Path**: `/var/lib/telegram-bot-api` (atau sesuai konfigurasi server Anda)
- **Documents Path**: `documents`
- **Photos Path**: `photos`
- **Videos Path**: `videos`
- **Temp Path**: `temp`

#### Cleanup Settings:
- **Auto Cleanup**: ✅ (untuk bersihkan file dari Bot API path setelah copy ke Laravel)
- **Cleanup After**: `24` hours

### 5. Test Connection

Klik tombol **Test Connection** untuk memastikan bot terhubung dengan benar.

### 6. Activate Bot

Centang **Activate Bot** dan klik **Save Configuration**.

### 7. Add Allowed Users

Untuk menambahkan user yang diizinkan menggunakan bot:

1. Minta user mengirim pesan `/start` ke bot
2. Lihat Telegram User ID di Bot Activity
3. Tambahkan User ID tersebut ke Allowed Users

Atau gunakan script test untuk mendapatkan User ID:
```bash
php telegram-bot-test.php
```

## 📱 Bot Commands

Setelah setup selesai, bot mendukung commands berikut:

- `/start` - Memulai bot
- `/help` - Menampilkan bantuan
- `/cari [keyword]` - Mencari proyek
- `/pilih [kode_proyek]` - Memilih proyek aktif
- `/status` - Melihat status saat ini
- `/list` - Melihat daftar file dalam proyek
- `/folder [nama]` - Membuat folder baru
- `/clear` - Hapus proyek aktif

## 🔄 File Upload Flow

1. User pilih proyek dengan `/pilih [kode_proyek]`
2. User kirim file (document, photo, video) ke bot
3. File tersimpan di Bot API path (temporary)
4. Laravel copy file ke `storage/app/proyek/{project-code}/`
5. File dapat diakses melalui web dashboard
6. Bot API file di-cleanup setelah 24 jam (optional)

## 🛠️ Troubleshooting

### Webhook Not Working

Jika webhook tidak bekerja, pastikan:
1. URL webhook dapat diakses dari internet
2. SSL certificate valid (untuk production)
3. Firewall tidak memblokir port

Manual set webhook:
```bash
curl -X POST "http://localhost:8081/bot{YOUR_BOT_TOKEN}/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://yourdomain.com/api/telegram/webhook"}'
```

### File Not Found in Bot API Path

Jika file tidak ditemukan di Bot API path:
1. Pastikan path configuration benar
2. Check permission folder
3. Bot akan otomatis download dari Telegram jika file tidak ada

### Permission Denied

Pastikan Laravel memiliki permission untuk:
- Read dari Bot API path
- Write ke storage/app/proyek/

```bash
# Set permission untuk storage
chmod -R 775 storage
chown -R www-data:www-data storage

# Set permission untuk Bot API path (jika diperlukan)
chmod -R 755 /var/lib/telegram-bot-api
```

## 📊 Monitoring

### Bot Activity

Akses `/telegram-bot/activity` untuk melihat:
- Total aktivitas
- Upload statistics
- Command usage
- Error logs

### File Explorer

Akses `/telegram-bot/explorer` untuk melihat:
- File yang diupload via Telegram
- Filter by project
- Download files

## 🔒 Security Notes

1. **User Whitelist**: Hanya user yang terdaftar di allowed_users yang bisa menggunakan bot
2. **Rate Limiting**: Bot memiliki rate limiting untuk mencegah spam
3. **File Validation**: File divalidasi sebelum disimpan
4. **Project Access**: User hanya bisa upload ke proyek yang ada

## 📝 Additional Configuration

### Custom Bot API Server

Jika ingin menggunakan server Bot API yang berbeda:

```bash
# Start telegram-bot-api server dengan custom path
telegram-bot-api-binary \
  --api-id=YOUR_API_ID \
  --api-hash=YOUR_API_HASH \
  --local \
  --dir=/custom/path/for/files \
  --port=8081
```

### Environment Variables (Optional)

Tambahkan ke `.env`:

```env
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_API_HOST=localhost
TELEGRAM_BOT_API_PORT=8081
TELEGRAM_BOT_API_PATH=/var/lib/telegram-bot-api
```

## ✅ Checklist Implementasi

- [x] Database migrations created
- [x] Eloquent models created
- [x] Navigation menu added
- [x] TelegramService implemented
- [x] FileProcessingService implemented
- [x] CommandHandler implemented
- [x] Webhook controller created
- [x] Bot controller created
- [x] Configuration view created
- [x] Routes configured
- [x] Two-path system implemented
- [x] Security measures implemented
- [x] Mobile responsive UI

## 🎉 Implementation Complete!

Telegram Bot integration dengan Laravel sudah siap digunakan. Bot dapat:
- Menerima dan memproses commands
- Upload file hingga 2GB
- Organize file otomatis ke folder proyek
- Track semua aktivitas
- Support multiple users dengan whitelist

Untuk bantuan lebih lanjut, lihat:
- `TELEGRAM_BOT_TEST_README.md` - Panduan testing
- `TELEGRAM_TROUBLESHOOTING.md` - Troubleshooting guide
- `TELEGRAM_BOT_PATH_FLOW.md` - Dokumentasi two-path system