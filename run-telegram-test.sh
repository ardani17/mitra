#!/bin/bash

# Telegram Bot Test Runner Script
# Jalankan semua test secara berurutan

echo "======================================"
echo "   TELEGRAM BOT TEST RUNNER"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check if telegram-bot-api server is running
echo -e "${YELLOW}Step 1: Checking telegram-bot-api server status...${NC}"
echo ""
php check-telegram-server.php

echo ""
echo -e "${YELLOW}Press Enter to continue or Ctrl+C to exit...${NC}"
read

# Step 2: Run quick test
echo ""
echo -e "${YELLOW}Step 2: Running quick test...${NC}"
echo ""
echo -e "${GREEN}NOTE: Make sure you have edited the BOT_TOKEN in telegram-bot-quick-test.php${NC}"
echo ""
php telegram-bot-quick-test.php

echo ""
echo -e "${YELLOW}Press Enter to run full test or Ctrl+C to exit...${NC}"
read

# Step 3: Run full test (optional)
echo ""
echo -e "${YELLOW}Step 3: Running full interactive test...${NC}"
echo ""
php telegram-bot-test.php

echo ""
echo "======================================"
echo -e "${GREEN}   TEST COMPLETED${NC}"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. If tests passed, you can integrate with Laravel"
echo "2. Check TELEGRAM_BOT_TEST_README.md for more info"
echo "3. Use app/Services/TelegramService.php for Laravel integration"
echo ""