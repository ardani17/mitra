# 🤖 Telegram Bot Test dengan Local Server

Script PHP untuk testing Telegram Bot dengan local `telegram-bot-api` server yang sudah Anda install.

## 📋 Prerequisites

1. **telegram-bot-api server** sudah terinstall dan berjalan di port 8081
2. **PHP** dengan ekstensi curl enabled
3. **Bot Token** dari @BotFather di Telegram

## 🚀 Quick Start

### 1. Setup Bot Token

Edit file `telegram-bot-quick-test.php` dan ganti token:

```php
$BOT_TOKEN = 'YOUR_BOT_TOKEN_HERE';  // <-- Ganti dengan token bot Anda!
```

Cara mendapatkan token:
1. Buka Telegram, cari @BotFather
2. Kirim `/newbot` atau gunakan bot yang sudah ada
3. Copy token yang diberikan

### 2. Pilih Server

```php
$USE_LOCAL = true;  // true = local server, false = official API
```

- `true` = Gunakan local server di localhost:8081 (limit 2GB)
- `false` = Gunakan official Telegram API (limit 20MB download, 50MB upload)

### 3. Jalankan Test

```bash
# Quick test (sederhana)
php telegram-bot-quick-test.php

# Full test (lengkap dengan interactive mode)
php telegram-bot-test.php
```

### 4. Kirim Pesan ke Bot

Setelah menjalankan script:
1. Buka Telegram
2. Cari bot Anda (username akan ditampilkan di console)
3. Kirim pesan apa saja ke bot
4. Jalankan script lagi untuk melihat hasilnya

## 📊 Perbandingan Server

| Feature | Official API | Local Server |
|---------|-------------|--------------|
| Download Limit | 20 MB | 2 GB (2000 MB) |
| Upload Limit | 50 MB | 2 GB (2000 MB) |
| File Storage | Telegram Server | Local VPS |
| Rate Limits | Ada | Tidak ada |
| Privacy | File di Telegram | File di server sendiri |
| Setup | Tidak perlu | Install telegram-bot-api |

## 🧪 Test yang Dilakukan

### Quick Test (`telegram-bot-quick-test.php`)
1. ✅ Koneksi ke bot
2. ✅ Get bot info
3. ✅ Baca pesan masuk
4. ✅ Kirim balasan
5. ✅ Upload file 25MB (jika local server)

### Full Test (`telegram-bot-test.php`)
1. ✅ Get bot info
2. ✅ Get updates/messages
3. ✅ Send message
4. ✅ Download file
5. ✅ Upload large file
6. ✅ Interactive mode

## 📁 File yang Dihasilkan

- `test-25mb.txt` - File test 25MB untuk upload
- `test-large-file.txt` - File test untuk full test
- `downloads/` - Folder untuk file yang didownload

## 🔧 Troubleshooting

### Error: Connection failed!
- Pastikan telegram-bot-api server berjalan:
  ```bash
  ps aux | grep telegram-bot-api
  ```
- Check port 8081:
  ```bash
  netstat -an | grep 8081
  ```

### Error: Bot token invalid
- Pastikan token sudah benar
- Token format: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`

### Error: No messages found
- Kirim pesan ke bot terlebih dahulu
- Tunggu beberapa detik, lalu jalankan script lagi

### Error: File too big (Official API)
- Switch ke local server dengan `$USE_LOCAL = true`
- Official API limit: 20MB download, 50MB upload

## 📝 Contoh Output Sukses

```
========================================
TELEGRAM BOT QUICK TEST
========================================
Server: LOCAL (localhost:8081)
Token: 1234567890...

1. Testing Bot Connection...
✅ Bot Connected: @your_bot_name
   Name: Your Bot
   ID: 1234567890

2. Getting Recent Messages...
✅ Found 3 message(s)
   Last message from: username
   Chat ID: 987654321
   Text: Hello bot!

3. Sending Test Reply...
✅ Reply sent successfully!

4. Testing Large File Support...
   File size: 25.00 MB
   Uploading to Telegram...
✅ Large file uploaded successfully!
   LOCAL SERVER supports files up to 2GB!

========================================
TEST COMPLETED
========================================
```

## 🔄 Next Steps

Setelah test berhasil, Anda bisa:

1. **Integrasi dengan Laravel** - Gunakan `TelegramService.php` yang sudah dibuat
2. **Setup Webhook** - Untuk receive real-time updates
3. **Implement File Upload** - Terima file dari user via Telegram
4. **Auto-organize Files** - Simpan file ke folder project yang sesuai

## 💡 Tips

1. **Untuk Production:**
   - Gunakan webhook instead of polling
   - Setup HTTPS untuk webhook
   - Implement rate limiting
   - Add user authentication

2. **Security:**
   - Validasi user ID yang boleh upload
   - Scan file untuk virus
   - Limit file types yang diterima
   - Log semua aktivitas

3. **Performance:**
   - Gunakan queue untuk file besar
   - Compress file sebelum storage
   - Implement caching

## 📚 Resources

- [telegram-bot-api GitHub](https://github.com/tdlib/telegram-bot-api)
- [Telegram Bot API Docs](https://core.telegram.org/bots/api)
- [Local Bot API Server Guide](https://github.com/tdlib/telegram-bot-api#installation)

## ✅ Checklist Implementasi

- [ ] Install telegram-bot-api server
- [ ] Create bot via @BotFather
- [ ] Test dengan script ini
- [ ] Setup webhook URL
- [ ] Implement di Laravel
- [ ] Add security measures
- [ ] Deploy to production

---

**Note:** Script ini hanya untuk testing. Untuk production, gunakan proper framework dan security measures.