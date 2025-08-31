# 🔧 Telegram Bot API - Troubleshooting Guide

## ⚠️ Common Issues & Solutions

### 1. Error: "Module 'zip' is already loaded"
**Symptom:**
```
PHP Warning: Module "zip" is already loaded in Unknown on line 0
```

**Solution:**
- Ini hanya warning, bisa diabaikan
- Untuk menghilangkan, edit `php.ini` dan comment salah satu baris `extension=zip`
- Atau tambahkan `@` sebelum perintah PHP: `@php telegram-bot-quick-test.php`

### 2. Error: "shell_exec() has been disabled"
**Symptom:**
```
PHP Fatal error: Uncaught Error: Call to undefined function shell_exec()
```

**Solution:**
- Function `shell_exec()` dinonaktifkan untuk keamanan
- Tidak mempengaruhi fungsi utama bot
- Script akan tetap berjalan dengan fitur terbatas

### 3. Bot Connection Timeout
**Symptom:**
- Script berhenti saat "Testing Bot Connection..."
- Tidak ada response dari server

**Possible Causes & Solutions:**

#### A. Server tidak berjalan
```bash
# Check if server is running
ps aux | grep telegram-bot-api

# If not running, start it:
cd /home/teleweb/backend/data-bot-api
./telegram-bot-api --api-id=YOUR_ID --api-hash=YOUR_HASH --local --port=8081
```

#### B. Firewall blocking port 8081
```bash
# Check firewall (Ubuntu/Debian)
sudo ufw status

# Allow port 8081
sudo ufw allow 8081

# For CentOS/RHEL
sudo firewall-cmd --add-port=8081/tcp --permanent
sudo firewall-cmd --reload
```

#### C. Wrong IP or Port
- Verify server IP: `103.195.190.235`
- Verify port: `8081`
- Test direct access: `http://103.195.190.235:8081`

### 4. Invalid Bot Token
**Symptom:**
```
Error: Not Found
```

**Solution:**
1. Get new token from @BotFather
2. Update token in script
3. Make sure no extra spaces in token

### 5. PHP Configuration Issues

#### Check PHP Extensions:
```bash
php -m | grep -E "curl|json|openssl"
```

#### Required PHP Settings:
```ini
; php.ini
allow_url_fopen = On
max_execution_time = 60
memory_limit = 128M
```

## 🧪 Debug Steps

### Step 1: Run Debug Script
```bash
php telegram-debug.php
```

This will check:
- ✅ Port connectivity
- ✅ HTTP response
- ✅ Bot API access
- ✅ PHP requirements

### Step 2: Test Direct API Access
Open in browser or curl:
```bash
# Test server
curl http://103.195.190.235:8081

# Test bot
curl http://103.195.190.235:8081/bot8281280313:AAG0B4mu6tEzs3N0_BSO3VGatHov7t0klls/getMe
```

### Step 3: Check Server Logs
```bash
# Check telegram-bot-api logs
tail -f /home/teleweb/backend/data-bot-api/telegram-bot.log

# Check system logs
tail -f /var/log/syslog | grep telegram
```

## 📊 Expected Output

### ✅ Successful Connection:
```
========================================
TELEGRAM BOT QUICK TEST
========================================
Server: LOCAL (103.195.190.235:8081)
Token: 8281280313...

1. Testing Bot Connection...
✅ Bot Connected: @proyek_ardani_bot
   Name: Proyek Bot
   ID: 8281280313
```

### ❌ Failed Connection:
```
1. Testing Bot Connection...
❌ Failed to connect to API
   URL: http://103.195.190.235:8081/bot.../getMe
   Error: Connection timeout
```

## 🚀 Quick Fix Script

Create `fix-telegram.sh`:
```bash
#!/bin/bash

echo "Fixing Telegram Bot Issues..."

# 1. Kill existing process
pkill -f telegram-bot-api

# 2. Start server
cd /home/teleweb/backend/data-bot-api
nohup ./telegram-bot-api \
  --api-id=YOUR_API_ID \
  --api-hash=YOUR_API_HASH \
  --local \
  --port=8081 > telegram-bot.log 2>&1 &

# 3. Wait for startup
sleep 5

# 4. Test connection
php telegram-debug.php
```

## 🔍 Network Diagnostics

### Test from local machine:
```bash
# Ping server
ping 103.195.190.235

# Test port
telnet 103.195.190.235 8081

# Test with netcat
nc -zv 103.195.190.235 8081
```

### Test from server itself:
```bash
# SSH to server
ssh user@103.195.190.235

# Test locally
curl http://localhost:8081
php telegram-bot-quick-test.php
```

## 📝 Configuration Checklist

- [ ] telegram-bot-api server installed
- [ ] Server running on port 8081
- [ ] Firewall allows port 8081
- [ ] Bot token is valid
- [ ] PHP has curl extension
- [ ] allow_url_fopen enabled
- [ ] Network connectivity OK

## 💡 Pro Tips

1. **Use systemd service** for auto-start:
```ini
[Unit]
Description=Telegram Bot API Server
After=network.target

[Service]
Type=simple
User=teleweb
WorkingDirectory=/home/teleweb/backend/data-bot-api
ExecStart=/home/teleweb/backend/data-bot-api/telegram-bot-api --api-id=XXX --api-hash=YYY --local --port=8081
Restart=always

[Install]
WantedBy=multi-user.target
```

2. **Monitor with cron**:
```bash
*/5 * * * * pgrep telegram-bot-api || /home/teleweb/start-telegram-bot.sh
```

3. **Use nginx proxy** for HTTPS:
```nginx
server {
    listen 443 ssl;
    server_name bot.yourdomain.com;
    
    location / {
        proxy_pass http://localhost:8081;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
    }
}
```

## 🆘 Still Having Issues?

1. Check the [telegram-debug.php](telegram-debug.php) output
2. Review server logs
3. Verify network connectivity
4. Ensure all dependencies installed
5. Try official API first to isolate issue

---

**Remember:** The local server gives you 2GB file limit vs 20MB on official API!