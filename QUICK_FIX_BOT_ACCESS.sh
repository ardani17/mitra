#!/bin/bash

# QUICK FIX - Bot Masih Akses Ditolak
# Walaupun user sudah ada di database

echo "🔧 QUICK FIX - Bot Authorization"
echo "================================"
echo ""
echo "Pilih metode fix:"
echo "1. Tambah ke allowed_users.json (PALING CEPAT)"
echo "2. Update CommandHandler.php"
echo "3. Bypass semua authorization (DEV ONLY)"
echo ""
read -p "Pilih [1-3]: " choice

case $choice in
    1)
        echo "📝 Menambahkan user ke allowed_users.json..."
        
        # Method 1: Direct file write
        cat > storage/app/allowed_users.json << 'EOF'
{
    "allowed_users": ["731289973"]
}
EOF
        
        echo "✅ File allowed_users.json sudah diupdate"
        echo ""
        echo "Alternative dengan PHP:"
        echo "php artisan tinker"
        echo ">>> \$data = ['allowed_users' => ['731289973']];"
        echo ">>> file_put_contents('storage/app/allowed_users.json', json_encode(\$data));"
        echo ">>> exit;"
        ;;
        
    2)
        echo "📝 Update CommandHandler.php..."
        echo ""
        echo "Edit file: app/Services/Telegram/CommandHandler.php"
        echo ""
        echo "CARI (line ~31-34):"
        echo "----------------------------------------"
        cat << 'EOF'
// Check if user is allowed
if (!$this->telegramService->isUserAllowed($user['id'])) {
    return $this->sendUnauthorizedMessage($chatId);
}
EOF
        echo ""
        echo "GANTI DENGAN:"
        echo "----------------------------------------"
        cat << 'EOF'
// Check authorization - New system with fallback
$botUser = \App\Models\BotUser::findByTelegramId($user['id']);

// Public commands available for everyone
$publicCommands = ['start', 'help', 'register', 'status'];
$commandParts = explode(' ', $text);
$command = str_replace('/', '', $commandParts[0]);

if (!in_array($command, $publicCommands)) {
    // Check new database system first
    if ($botUser && $botUser->isActive()) {
        // User authorized via new system
    } else {
        // Fallback to old JSON system
        if (!$this->telegramService->isUserAllowed($user['id'])) {
            return $this->sendUnauthorizedMessage($chatId);
        }
    }
}
EOF
        ;;
        
    3)
        echo "⚠️  BYPASS MODE (Development Only!)"
        echo ""
        echo "Edit file: app/Services/Telegram/CommandHandler.php"
        echo ""
        echo "Comment out authorization check:"
        echo "----------------------------------------"
        cat << 'EOF'
// TEMPORARY BYPASS - REMOVE IN PRODUCTION!
/*
if (!$this->telegramService->isUserAllowed($user['id'])) {
    return $this->sendUnauthorizedMessage($chatId);
}
*/
EOF
        ;;
esac

echo ""
echo "📌 Setelah fix, jalankan:"
echo "----------------------------------------"
echo "php artisan cache:clear"
echo "php artisan config:clear"
echo "php artisan optimize"
echo ""
echo "Jika masih tidak work:"
echo "sudo systemctl restart php8.1-fpm"
echo "sudo systemctl restart nginx"
echo ""
echo "✅ Test dengan kirim /start ke bot"