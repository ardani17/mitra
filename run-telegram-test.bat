@echo off
REM Telegram Bot Test Runner Script for Windows
REM Jalankan semua test secara berurutan

echo ======================================
echo    TELEGRAM BOT TEST RUNNER
echo ======================================
echo.

REM Step 1: Check if telegram-bot-api server is running
echo Step 1: Checking telegram-bot-api server status...
echo.
php check-telegram-server.php

echo.
echo Press any key to continue or Ctrl+C to exit...
pause > nul

REM Step 2: Run quick test
echo.
echo Step 2: Running quick test...
echo.
echo NOTE: Make sure you have edited the BOT_TOKEN in telegram-bot-quick-test.php
echo.
php telegram-bot-quick-test.php

echo.
echo Press any key to run full test or Ctrl+C to exit...
pause > nul

REM Step 3: Run full test (optional)
echo.
echo Step 3: Running full interactive test...
echo.
php telegram-bot-test.php

echo.
echo ======================================
echo    TEST COMPLETED
echo ======================================
echo.
echo Next steps:
echo 1. If tests passed, you can integrate with Laravel
echo 2. Check TELEGRAM_BOT_TEST_README.md for more info
echo 3. Use app/Services/TelegramService.php for Laravel integration
echo.
pause