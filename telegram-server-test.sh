#!/bin/bash

# Script untuk menjalankan test dari server VPS
# Jalankan script ini langsung di server VPS via SSH

echo "========================================="
echo "TELEGRAM BOT TEST - SERVER SIDE"
echo "========================================="
echo ""
echo "Script ini harus dijalankan di server VPS"
echo "dimana telegram-bot-api server berjalan"
echo ""

# Check if running on server
if [ ! -f "/usr/local/bin/telegram-bot-api" ]; then
    echo "⚠️  Warning: telegram-bot-api not found in /usr/local/bin/"
    echo "    Make sure you're running this on the VPS server"
    echo ""
fi

# Check if telegram-bot-api is running
echo "1. Checking telegram-bot-api process..."
if pgrep -f telegram-bot-api > /dev/null; then
    echo "✅ telegram-bot-api is running"
    echo ""
    ps aux | grep telegram-bot-api | grep -v grep
else
    echo "❌ telegram-bot-api is NOT running"
    echo "   Please start the server first"
    exit 1
fi

echo ""
echo "2. Testing localhost connection..."
curl -s http://localhost:8081 > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Port 8081 is accessible on localhost"
else
    echo "❌ Cannot connect to localhost:8081"
fi

echo ""
echo "3. Running PHP test scripts..."
echo ""

# Run check server
if [ -f "check-telegram-server.php" ]; then
    echo "Running check-telegram-server.php..."
    php check-telegram-server.php
    echo ""
fi

# Run quick test
if [ -f "telegram-bot-quick-test.php" ]; then
    echo "Running telegram-bot-quick-test.php..."
    php telegram-bot-quick-test.php
    echo ""
fi

# Run debug if needed
read -p "Run debug diagnostic? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if [ -f "telegram-debug.php" ]; then
        php telegram-debug.php
    fi
fi

echo ""
echo "========================================="
echo "TEST COMPLETED"
echo "========================================="
echo ""
echo "If tests failed, check:"
echo "1. Bot token is correct"
echo "2. Server is running with correct parameters"
echo "3. No firewall blocking localhost:8081"
echo ""