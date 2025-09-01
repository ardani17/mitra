#!/bin/bash

echo "========================================="
echo "Fix Telegram Bot Menu on VPS"
echo "========================================="

# 1. Clear all caches
echo "1. Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 2. Rebuild caches
echo "2. Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Check if user has direktur role
echo "3. Checking user roles..."
php artisan tinker --execute="
\$user = \App\Models\User::find(1);
echo 'User: ' . \$user->name . PHP_EOL;
echo 'Email: ' . \$user->email . PHP_EOL;
echo 'Roles: ';
foreach(\$user->roles as \$role) {
    echo \$role->name . ' ';
}
echo PHP_EOL;
echo 'Has direktur role: ' . (\$user->hasRole('direktur') ? 'Yes' : 'No') . PHP_EOL;
"

# 4. Check if routes exist
echo ""
echo "4. Checking Telegram Bot routes..."
php artisan route:list | grep telegram-bot

# 5. Check permissions
echo ""
echo "5. Checking file permissions..."
ls -la resources/views/layouts/navigation.blade.php
ls -la storage/framework/views/

# 6. Rebuild assets
echo ""
echo "6. Rebuilding assets..."
npm run build

echo ""
echo "========================================="
echo "Fix completed!"
echo "========================================="
echo ""
echo "Please check:"
echo "1. Make sure you are logged in as a user with 'direktur' role"
echo "2. The menu only appears for users with 'direktur' role"
echo "3. Try logging out and logging in again"
echo ""