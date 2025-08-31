# Linux System Statistics Setup Guide

## Problem
On Linux production servers, the System Statistics dashboard may show 0% CPU usage, 0% RAM usage, and missing disk information due to permission restrictions. This happens because:
1. The web server user (www-data, nginx, apache) doesn't have permission to read certain `/proc` files
2. The `shell_exec()` function is often disabled on production servers for security reasons

## Solution Overview
We've implemented a multi-layered solution:
1. **Primary Method**: Helper script with sudo permissions for accurate metrics
2. **Fallback Method**: Direct reading from `/proc` filesystem when possible
3. **Final Fallback**: Default values to prevent errors

## Setup Instructions

### Step 1: Upload the Helper Scripts
Ensure these files are uploaded to your Laravel project:
- `scripts/system-metrics-helper.php` - The metrics collection script
- `scripts/setup-metrics-helper.sh` - The setup script

### Step 2: Run the Setup Script
SSH into your Linux server and run:

```bash
cd /path/to/your/laravel/project
sudo bash scripts/setup-metrics-helper.sh
```

The script will:
1. Detect your web server user (www-data, nginx, or apache)
2. Create a sudoers configuration to allow the web server to run the helper
3. Make the helper script executable
4. Test the configuration

### Step 3: Verify the Setup
After running the setup script, test it manually:

```bash
# Replace www-data with your actual web server user
sudo -u www-data sudo php scripts/system-metrics-helper.php all
```

You should see JSON output with CPU, memory, and disk metrics.

### Step 4: Clear Laravel Cache
Clear the application cache to ensure the new configuration is used:

```bash
php artisan cache:clear
php artisan config:clear
```

### Step 5: Test the Dashboard
Visit `/system-statistics` in your browser. You should now see:
- Accurate CPU usage percentage
- Correct RAM usage
- All disk partitions with usage statistics

## Manual Setup (Alternative Method)

If the automatic setup script doesn't work, you can configure it manually:

### 1. Create Sudoers Configuration
Create a file `/etc/sudoers.d/laravel-metrics-helper`:

```bash
sudo nano /etc/sudoers.d/laravel-metrics-helper
```

Add this content (replace paths and user as needed):

```
# Allow web server user to run system metrics helper without password
www-data ALL=(root) NOPASSWD: /var/www/your-project/scripts/system-metrics-helper.php
www-data ALL=(root) NOPASSWD: /usr/bin/php /var/www/your-project/scripts/system-metrics-helper.php *
```

### 2. Set Proper Permissions
```bash
sudo chmod 0440 /etc/sudoers.d/laravel-metrics-helper
sudo chmod +x /var/www/your-project/scripts/system-metrics-helper.php
```

### 3. Test the Configuration
```bash
sudo -u www-data sudo php /var/www/your-project/scripts/system-metrics-helper.php all
```

## Troubleshooting

### Issue: Still showing 0% after setup
1. **Check web server user**: Make sure you're using the correct user
   ```bash
   ps aux | grep -E 'nginx|apache|php-fpm'
   ```

2. **Check sudoers syntax**: Validate the sudoers file
   ```bash
   sudo visudo -c -f /etc/sudoers.d/laravel-metrics-helper
   ```

3. **Check SELinux**: If using SELinux, you may need to adjust contexts
   ```bash
   sudo setenforce 0  # Temporarily disable to test
   ```

4. **Check PHP configuration**: Ensure exec/shell_exec are not disabled
   ```bash
   php -r "echo ini_get('disable_functions');"
   ```

### Issue: Permission denied errors
1. **Check file ownership**:
   ```bash
   ls -la scripts/system-metrics-helper.php
   ```

2. **Check /proc permissions**:
   ```bash
   ls -la /proc/cpuinfo /proc/meminfo /proc/loadavg
   ```

### Issue: Debug mode
Access the debug endpoint to see what's working:
```
https://your-domain.com/system-statistics/debug
```

This will show:
- Current user and permissions
- Which /proc files are readable
- Available methods for getting metrics

## Security Considerations

1. **Sudoers Configuration**: The helper script can only run specific commands, not arbitrary code
2. **Input Validation**: The helper script validates all input commands
3. **No Shell Injection**: Uses escapeshellarg() for all shell commands
4. **Limited Scope**: Only reads system metrics, cannot modify system

## How It Works

### With Helper Script (Recommended)
1. Laravel detects it's running on Linux
2. Attempts to run the helper script with sudo
3. Helper script reads metrics with root permissions
4. Returns JSON data to Laravel
5. Laravel parses and displays the metrics

### Fallback Method (When Helper Unavailable)
1. Attempts to read directly from `/proc/cpuinfo`, `/proc/meminfo`, etc.
2. Uses PHP's built-in functions like `sys_getloadavg()` and `disk_free_space()`
3. Calculates metrics from available data
4. Returns best-effort metrics or defaults

## Supported Metrics

### CPU Metrics
- CPU usage percentage
- Number of cores
- Number of threads
- Load average (1, 5, 15 minutes)

### Memory Metrics
- Total RAM
- Used RAM
- Available RAM
- Memory usage percentage

### Disk Metrics
- All mounted filesystems
- Total space per partition
- Used space per partition
- Free space per partition
- Usage percentage per partition

### System Information
- Operating system and version
- Kernel version
- Hostname
- System uptime
- PHP version
- Laravel version

## Testing Commands

Test individual metrics:
```bash
# Test CPU metrics
sudo php scripts/system-metrics-helper.php cpu

# Test memory metrics
sudo php scripts/system-metrics-helper.php memory

# Test disk metrics
sudo php scripts/system-metrics-helper.php disk

# Test all metrics
sudo php scripts/system-metrics-helper.php all
```

## Compatibility

Tested on:
- Ubuntu 20.04, 22.04
- Debian 10, 11
- CentOS 7, 8
- Rocky Linux 8, 9
- AlmaLinux 8, 9
- Amazon Linux 2

Web servers:
- Apache with mod_php
- Apache with PHP-FPM
- Nginx with PHP-FPM
- OpenLiteSpeed

## Support

If you encounter issues:
1. Check this documentation first
2. Run the debug endpoint
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Check system logs: `sudo tail -f /var/log/syslog` or `/var/log/messages`