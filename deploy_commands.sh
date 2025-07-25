#!/bin/bash

echo "=== MITRA PROJECT DEPLOYMENT COMMANDS ==="
echo "Jalankan command berikut di VPS untuk mengatasi masalah routing:"
echo ""

echo "1. Clear semua cache:"
echo "php artisan cache:clear"
echo "php artisan config:clear"
echo "php artisan route:clear"
echo "php artisan view:clear"
echo ""

echo "2. Optimize untuk production:"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo "php artisan view:cache"
echo ""

echo "3. Set permissions (jika diperlukan):"
echo "chmod -R 755 storage/"
echo "chmod -R 755 bootstrap/cache/"
echo ""

echo "4. Restart web server (pilih salah satu):"
echo "# Untuk Apache:"
echo "sudo systemctl restart apache2"
echo ""
echo "# Untuk Nginx:"
echo "sudo systemctl restart nginx"
echo ""

echo "5. Verify routes:"
echo "php artisan route:list --name=login"
echo ""

echo "6. Test aplikasi:"
echo "curl -I http://your-domain.com/login"
echo ""

echo "=== TROUBLESHOOTING ==="
echo "Jika masih error 404:"
echo ""
echo "A. Cek .htaccess di public folder:"
echo "cat public/.htaccess"
echo ""
echo "B. Cek virtual host configuration"
echo "C. Pastikan document root mengarah ke folder 'public'"
echo "D. Cek file .env sudah ada dan benar"
echo ""

echo "=== QUICK FIX COMMANDS ==="
echo "Jalankan semua sekaligus:"
echo "php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache"
