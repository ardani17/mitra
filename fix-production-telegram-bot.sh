#!/bin/bash

# Fix Production Telegram Bot User Management System
# Run this script on your production server to fix cache and permission issues

echo "================================================"
echo "🔧 FIXING PRODUCTION TELEGRAM BOT SYSTEM"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the project path
PROJECT_PATH="/www/wwwroot/mitra.cloudnexify.com/mitra"

# Check if we're in the right directory
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo -e "${RED}❌ Error: Laravel project not found at $PROJECT_PATH${NC}"
    echo "Please update the PROJECT_PATH variable in this script"
    exit 1
fi

cd $PROJECT_PATH

echo -e "${YELLOW}📁 Step 1: Creating required directories...${NC}"
# Create all required cache directories
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public

echo -e "${GREEN}✅ Directories created${NC}"
echo ""

echo -e "${YELLOW}🔐 Step 2: Fixing permissions...${NC}"
# Fix permissions for web server user (adjust user if needed)
WEB_USER="www-data"  # Change to 'nginx' or 'apache' if different

# Set proper ownership
chown -R $WEB_USER:$WEB_USER bootstrap/cache
chown -R $WEB_USER:$WEB_USER storage

# Set proper permissions
chmod -R 775 bootstrap/cache
chmod -R 775 storage

echo -e "${GREEN}✅ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}🧹 Step 3: Clearing all cache...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo -e "${GREEN}✅ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}💾 Step 4: Running database migrations...${NC}"
# Backup database first
echo "Creating database backup..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)

# Create backup directory
mkdir -p storage/backups
BACKUP_FILE="storage/backups/backup_$(date +%Y%m%d_%H%M%S).sql"

# Backup database (adjust for PostgreSQL)
pg_dump -h $DB_HOST -U $DB_USER -d $DB_NAME > $BACKUP_FILE 2>/dev/null || {
    echo -e "${YELLOW}⚠️  Could not create backup, but continuing...${NC}"
}

# Run migrations
php artisan migrate --force || {
    echo -e "${YELLOW}⚠️  Some migrations may have already been applied${NC}"
    
    # Try running individual migrations
    echo "Trying individual migrations..."
    php artisan migrate --path=database/migrations/2025_09_01_131732_create_bot_users_table.php --force 2>/dev/null
    php artisan migrate --path=database/migrations/2025_09_01_131802_create_bot_roles_table.php --force 2>/dev/null
    php artisan migrate --path=database/migrations/2025_09_01_131819_create_bot_registration_requests_table.php --force 2>/dev/null
    php artisan migrate --path=database/migrations/2025_09_01_131841_create_bot_user_activity_logs_table.php --force 2>/dev/null
    php artisan migrate --path=database/migrations/2025_09_01_150000_add_processed_at_to_bot_registration_requests.php --force 2>/dev/null
    php artisan migrate --path=database/migrations/2025_09_01_132319_migrate_existing_allowed_users_to_bot_users.php --force 2>/dev/null
}

echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

echo -e "${YELLOW}🔨 Step 5: Optimizing for production...${NC}"
# Install dependencies without dev packages
composer install --optimize-autoloader --no-dev

# Rebuild cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo -e "${GREEN}✅ Optimization completed${NC}"
echo ""

echo -e "${YELLOW}🔄 Step 6: Restarting services...${NC}"
# Try different service names
if systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl restart php8.2-fpm
    echo "Restarted PHP 8.2 FPM"
elif systemctl is-active --quiet php8.1-fpm; then
    sudo systemctl restart php8.1-fpm
    echo "Restarted PHP 8.1 FPM"
elif systemctl is-active --quiet php7.4-fpm; then
    sudo systemctl restart php7.4-fpm
    echo "Restarted PHP 7.4 FPM"
fi

# Restart web server
if systemctl is-active --quiet nginx; then
    sudo systemctl restart nginx
    echo "Restarted Nginx"
elif systemctl is-active --quiet apache2; then
    sudo systemctl restart apache2
    echo "Restarted Apache"
fi

echo -e "${GREEN}✅ Services restarted${NC}"
echo ""

echo "================================================"
echo -e "${GREEN}✅ PRODUCTION FIX COMPLETED!${NC}"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Assign yourself as admin:"
echo "   php artisan bot:assign-admin YOUR_TELEGRAM_ID --role=super_admin"
echo ""
echo "2. Test the pages:"
echo "   - https://mitra.cloudnexify.com/telegram-bot/users"
echo "   - https://mitra.cloudnexify.com/telegram-bot/registrations"
echo ""
echo "3. If still having issues, check logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""