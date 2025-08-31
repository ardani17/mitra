#!/bin/bash

# Setup script for System Metrics Helper
# This script configures the metrics helper to run with proper permissions

echo "Setting up System Metrics Helper..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
   echo "Please run this script as root (use sudo)"
   exit 1
fi

# Get the web server user (usually www-data, nginx, or apache)
WEB_USER=""
if id -u www-data >/dev/null 2>&1; then
    WEB_USER="www-data"
elif id -u nginx >/dev/null 2>&1; then
    WEB_USER="nginx"
elif id -u apache >/dev/null 2>&1; then
    WEB_USER="apache"
else
    echo "Could not detect web server user. Please enter it manually:"
    read WEB_USER
fi

echo "Using web server user: $WEB_USER"

# Get the Laravel project path
LARAVEL_PATH=$(dirname $(dirname $(realpath $0)))
echo "Laravel project path: $LARAVEL_PATH"

# Create sudoers entry for the metrics helper
SUDOERS_FILE="/etc/sudoers.d/laravel-metrics-helper"
echo "Creating sudoers configuration..."

cat > $SUDOERS_FILE << EOF
# Allow web server user to run system metrics helper without password
$WEB_USER ALL=(root) NOPASSWD: $LARAVEL_PATH/scripts/system-metrics-helper.php
$WEB_USER ALL=(root) NOPASSWD: /usr/bin/php $LARAVEL_PATH/scripts/system-metrics-helper.php *
EOF

# Set proper permissions
chmod 0440 $SUDOERS_FILE
echo "Sudoers configuration created at $SUDOERS_FILE"

# Make the helper script executable
chmod +x $LARAVEL_PATH/scripts/system-metrics-helper.php
echo "Made helper script executable"

# Test the configuration
echo ""
echo "Testing configuration..."
sudo -u $WEB_USER sudo php $LARAVEL_PATH/scripts/system-metrics-helper.php cpu 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Setup completed successfully!"
    echo ""
    echo "The web server user ($WEB_USER) can now run the metrics helper with sudo."
    echo "Laravel will automatically use this helper when needed."
else
    echo ""
    echo "✗ Setup test failed. Please check the configuration."
    echo "You may need to restart your web server or PHP-FPM service."
fi

echo ""
echo "To manually test the helper, run:"
echo "  sudo -u $WEB_USER sudo php $LARAVEL_PATH/scripts/system-metrics-helper.php all"