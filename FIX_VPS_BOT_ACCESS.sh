#!/bin/bash

# FIX BOT ACCESS DI VPS - Direct Commands
# Run these commands directly on your VPS

echo "======================================"
echo "🔧 FIX BOT AUTHORIZATION DI VPS"
echo "======================================"
echo ""
echo "SSH ke VPS Anda, lalu jalankan command berikut:"
echo ""
echo "1️⃣ QUICK FIX - Tambah ke allowed_users.json:"
echo "================================================"
cat << 'EOF'
cd /www/wwwroot/mitra.cloudnexify.com/mitra
echo '{"allowed_users":["731289973"]}' > storage/app/allowed_users.json
php artisan cache:clear
php artisan config:clear
php artisan optimize
EOF

echo ""
echo "2️⃣ ATAU Via PHP Tinker:"
echo "========================"
cat << 'EOF'
cd /www/wwwroot/mitra.cloudnexify.com/mitra
php artisan tinker
>>> $data = ['allowed_users' => ['731289973']];
>>> file_put_contents('storage/app/allowed_users.json', json_encode($data));
>>> echo file_get_contents('storage/app/allowed_users.json');
>>> exit;
EOF

echo ""
echo "3️⃣ ATAU Update CommandHandler.php:"
echo "===================================="
cat << 'EOF'
cd /www/wwwroot/mitra.cloudnexify.com/mitra
nano app/Services/Telegram/CommandHandler.php

# Cari line 31-34:
# if (!$this->telegramService->isUserAllowed($user['id'])) {

# Ganti dengan:
$botUser = \App\Models\BotUser::findByTelegramId($user['id']);
if ($botUser && $botUser->isActive()) {
    // User authorized via new system
} elseif (!$this->telegramService->isUserAllowed($user['id'])) {
    return $this->sendUnauthorizedMessage($chatId);
}

# Save: Ctrl+O, Enter, Ctrl+X
EOF

echo ""
echo "4️⃣ ONE-LINE COMMAND (Copy-Paste ke VPS):"
echo "=========================================="
echo 'ssh root@your-vps-ip "cd /www/wwwroot/mitra.cloudnexify.com/mitra && echo '"'"'{"allowed_users":["731289973"]}'"'"' > storage/app/allowed_users.json && php artisan cache:clear && php artisan config:clear"'

echo ""
echo "======================================"
echo "📋 FULL SCRIPT UNTUK VPS"
echo "======================================"
echo ""
echo "Copy script ini dan jalankan di VPS:"
echo ""
cat << 'SCRIPT'
#!/bin/bash
cd /www/wwwroot/mitra.cloudnexify.com/mitra

# Backup existing file
cp storage/app/allowed_users.json storage/app/allowed_users.json.bak 2>/dev/null

# Add user to allowed list
echo '{"allowed_users":["731289973"]}' > storage/app/allowed_users.json

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# Verify
echo "✅ Fixed! Current allowed_users.json:"
cat storage/app/allowed_users.json

# Check database user
php artisan tinker --execute="
\$user = \App\Models\BotUser::where('telegram_id', '731289973')->first();
if (\$user) {
    echo 'Database User: ID=' . \$user->telegram_id . ', Status=' . \$user->status . ', Role=' . \$user->role_id . PHP_EOL;
} else {
    echo 'User not found in database' . PHP_EOL;
}
"

echo ""
echo "✅ Bot should work now! Test with /start"
SCRIPT