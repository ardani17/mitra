# Telegram Bot User Management - Production Deployment Guide

## Prerequisites
- Laravel application running with PostgreSQL database
- Telegram Bot token configured
- PHP 8.1+ and Composer installed
- Queue worker configured (optional but recommended)

## Step 1: Run Database Migrations

Execute all migrations to create the necessary tables:

```bash
php artisan migrate
```

This will create:
- `bot_users` table - Main user management
- `bot_roles` table - Role definitions with 5 default roles
- `bot_registration_requests` table - Registration tracking
- `bot_user_activity_logs` table - Activity audit logs

## Step 2: Verify Default Roles

Check that the 5 default roles were created:

```bash
php artisan tinker
>>> \App\Models\BotRole::all()->pluck('display_name', 'name');
```

Expected output:
- super_admin => Super Administrator
- admin => Administrator  
- moderator => Moderator
- user => User
- guest => Guest

## Step 3: Assign Initial Admin

### Option A: Using Artisan Command (Recommended)

```bash
# Assign super admin role to your Telegram ID
php artisan bot:assign-admin YOUR_TELEGRAM_ID --role=super_admin

# Or assign regular admin role
php artisan bot:assign-admin YOUR_TELEGRAM_ID --role=admin
```

### Option B: Using Tinker

```bash
php artisan tinker
>>> $user = \App\Models\BotUser::findByTelegramId('YOUR_TELEGRAM_ID');
>>> if (!$user) {
>>>     $user = \App\Models\BotUser::create([
>>>         'telegram_id' => 'YOUR_TELEGRAM_ID',
>>>         'username' => 'your_username',
>>>         'first_name' => 'Your Name',
>>>         'role_id' => 1, // Super Admin
>>>         'status' => 'active',
>>>         'approved_at' => now()
>>>     ]);
>>> } else {
>>>     $user->update(['role_id' => 1, 'status' => 'active']);
>>> }
```

## Step 4: Configure Environment Variables

Add these to your `.env` file:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=your_bot_username
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/telegram/webhook

# User Management Settings
BOT_REGISTRATION_ENABLED=true
BOT_AUTO_APPROVE_REGISTRATIONS=false
BOT_DEFAULT_USER_ROLE=user
BOT_ACTIVITY_LOG_RETENTION_DAYS=30

# Rate Limiting
BOT_REGISTRATION_RATE_LIMIT=5  # Max registrations per hour per IP
BOT_COMMAND_RATE_LIMIT=60      # Max commands per minute per user

# Notification Settings
BOT_NOTIFY_ADMINS_ON_REGISTRATION=true
BOT_NOTIFY_USER_ON_APPROVAL=true
```

## Step 5: Set Up Queue Worker (Recommended)

For better performance with notifications and activity logging:

```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/laravel-bot-worker.conf
```

Add this configuration:

```ini
[program:laravel-bot-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
```

Then reload supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-bot-worker:*
```

## Step 6: Set Up Cron Jobs

Add to crontab for cleaning old activity logs:

```bash
crontab -e
```

Add:

```cron
# Clean old bot activity logs daily at 2 AM
0 2 * * * cd /path/to/your/app && php artisan bot:clean-logs >> /dev/null 2>&1

# Process pending registrations reminder every hour
0 * * * * cd /path/to/your/app && php artisan bot:notify-pending-registrations >> /dev/null 2>&1
```

## Step 7: Configure Web Server

### Nginx Configuration

Ensure your webhook endpoint is accessible:

```nginx
location /telegram/webhook {
    proxy_pass http://127.0.0.1:8000/telegram/webhook;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
    proxy_read_timeout 300s;
    proxy_connect_timeout 75s;
}
```

### Set Webhook URL

```bash
php artisan telegram:webhook:set
```

## Step 8: Security Configuration

### Rate Limiting

Configure in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'telegram' => [
        'throttle:60,1', // 60 requests per minute
    ],
];
```

### IP Whitelisting (Optional)

For webhook endpoint, whitelist Telegram's IP ranges:

```php
// In middleware or route
$telegramIPs = [
    '149.154.160.0/20',
    '91.108.4.0/22',
    // Add more Telegram IP ranges
];
```

## Step 9: Test the System

### 1. Test Registration Flow

```bash
# In Telegram, send to your bot:
/register

# Check pending registrations
php artisan tinker
>>> \App\Models\BotRegistrationRequest::where('status', 'pending')->get();
```

### 2. Test Admin Commands

```bash
# As admin in Telegram:
/pending     # View pending registrations
/users       # List all users
/approve ID  # Approve a user
```

### 3. Test Web Interface

Navigate to:
- `/telegram-bot/users` - User management
- `/telegram-bot/registrations` - Pending registrations

## Step 10: Monitoring

### Set Up Logging

In `config/logging.php`:

```php
'channels' => [
    'telegram-bot' => [
        'driver' => 'daily',
        'path' => storage_path('logs/telegram-bot.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

### Monitor Key Metrics

```sql
-- Check registration trends
SELECT DATE(created_at) as date, COUNT(*) as registrations
FROM bot_registration_requests
WHERE created_at > NOW() - INTERVAL '7 days'
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Check active users
SELECT status, COUNT(*) as count
FROM bot_users
GROUP BY status;

-- Check command usage
SELECT action_type, COUNT(*) as count
FROM bot_user_activity_logs
WHERE created_at > NOW() - INTERVAL '24 hours'
GROUP BY action_type
ORDER BY count DESC;
```

## Step 11: Backup Strategy

### Database Backup

```bash
# Daily backup script
#!/bin/bash
pg_dump -U postgres -d your_database -t bot_users -t bot_roles -t bot_registration_requests -t bot_user_activity_logs > /backups/bot_users_$(date +%Y%m%d).sql
```

### Activity Log Archival

```bash
# Monthly archival
php artisan bot:archive-logs --older-than=30
```

## Troubleshooting

### Common Issues

1. **Registration not working**
   - Check webhook is set: `php artisan telegram:webhook:info`
   - Verify bot token is correct
   - Check logs: `tail -f storage/logs/telegram-bot.log`

2. **Users can't access commands**
   - Verify user status: `\App\Models\BotUser::findByTelegramId('ID')->status`
   - Check role permissions
   - Ensure CommandHandler is updated

3. **Notifications not sending**
   - Check queue worker is running: `sudo supervisorctl status`
   - Verify admin users have valid telegram_id
   - Check Telegram API rate limits

### Debug Commands

```bash
# Check system status
php artisan bot:status

# List all admins
php artisan tinker
>>> \App\Models\BotUser::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))->get();

# Check pending registrations
>>> \App\Models\BotRegistrationRequest::pending()->count();

# View recent activity
>>> \App\Models\BotUserActivityLog::latest()->limit(10)->get();
```

## Performance Optimization

### Database Indexes

Already included in migrations:
- `telegram_id` index on bot_users
- `status` index on bot_users and bot_registration_requests
- `created_at` index on activity logs

### Cache Configuration

```php
// Cache user permissions for 1 hour
Cache::remember("bot_user_permissions_{$userId}", 3600, function() use ($userId) {
    return BotUser::find($userId)->role->permissions;
});
```

### Queue Optimization

For high-volume bots:

```bash
# Increase workers
numprocs=4  # in supervisor config

# Use Redis for queue
QUEUE_CONNECTION=redis
```

## Maintenance Mode

To temporarily disable registrations:

```bash
php artisan down --message="Bot maintenance in progress" --retry=3600
```

## Security Best Practices

1. **Regular Updates**
   ```bash
   composer update
   php artisan optimize
   ```

2. **Monitor Failed Logins**
   ```sql
   SELECT * FROM bot_user_activity_logs 
   WHERE action_type = 'unauthorized_access' 
   AND created_at > NOW() - INTERVAL '1 day';
   ```

3. **Review Permissions**
   ```bash
   php artisan bot:audit-permissions
   ```

4. **Rotate Webhook Secret**
   ```bash
   php artisan telegram:webhook:rotate-secret
   ```

## Support and Documentation

- Main documentation: `/docs/telegram-bot-user-management-implementation.md`
- API reference: `/docs/telegram-bot-user-management-plan.md`
- Summary: `/docs/telegram-bot-user-management-summary.md`

For issues or questions, check the activity logs first:
```bash
tail -f storage/logs/telegram-bot.log
tail -f storage/logs/laravel.log