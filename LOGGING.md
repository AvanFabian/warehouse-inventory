# Logging Configuration

This application uses Laravel's logging system with multiple channels for different types of logs.

## Log Channels

### 1. **Daily Log** (Default)
- **Location**: `storage/logs/laravel.log`
- **Retention**: 14 days (configurable via `LOG_DAILY_DAYS`)
- **Level**: debug
- **Purpose**: General application logs

### 2. **Security Log**
- **Location**: `storage/logs/security.log`
- **Retention**: 30 days
- **Level**: warning
- **Purpose**: Authentication and authorization events
- **Logged Events**:
  - Failed login attempts
  - Unauthorized access attempts (403 errors)
  - Authentication exceptions
  - Authorization exceptions

### 3. **Activity Log**
- **Location**: `storage/logs/activity.log`
- **Retention**: 30 days
- **Level**: info
- **Purpose**: User activity tracking
- **Logged Events**:
  - Create operations (POST)
  - Update operations (PUT/PATCH)
  - Delete operations (DELETE)
  - User information (ID, email, role)
  - Request details (URL, IP, user agent)

### 4. **Database Log**
- **Location**: `storage/logs/database.log`
- **Retention**: 7 days
- **Level**: debug
- **Purpose**: Database errors and query issues
- **Logged Events**:
  - Connection errors
  - Query exceptions
  - PDO exceptions

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
LOG_CHANNEL=stack
LOG_STACK=daily,security
LOG_LEVEL=debug
LOG_DAILY_DAYS=14
```

### Production Settings

For production environments, update your `.env`:

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
LOG_STACK=daily,security
LOG_LEVEL=error
LOG_DAILY_DAYS=30
```

## Log Context

All exceptions are logged with the following context:

- **user_id**: ID of the authenticated user (or 'guest')
- **user_email**: Email of the authenticated user (or 'guest')
- **url**: Full URL of the request
- **method**: HTTP method (GET, POST, PUT, DELETE)
- **ip**: IP address of the requester
- **user_agent**: Browser/client user agent
- **exception_class**: Class name of the exception
- **file**: File where the exception occurred
- **line**: Line number where the exception occurred

## Activity Logging

The `LogUserActivity` middleware automatically logs:

- All POST, PUT, PATCH, DELETE requests
- Only authenticated users
- Human-readable action names (e.g., "Created Product", "Updated User")

### Excluded Routes

The following routes are NOT logged:
- Logout
- Password reset/change
- Debug routes (_ignition)

## Custom Error Pages

Custom error pages are available for:

- **404** - Page Not Found
- **403** - Access Denied
- **419** - Session Expired
- **500** - Internal Server Error
- **503** - Service Unavailable

All error pages maintain the application layout and branding.

## Viewing Logs

### Via Command Line

```bash
# View latest log entries
tail -f storage/logs/laravel.log

# View security logs
tail -f storage/logs/security.log

# View activity logs
tail -f storage/logs/activity.log

# View database logs
tail -f storage/logs/database.log

# Search for errors
grep "ERROR" storage/logs/laravel.log

# View today's logs
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log
```

### Log Rotation

Logs are automatically rotated based on the retention period:
- **Daily logs**: 14 days
- **Security logs**: 30 days
- **Activity logs**: 30 days
- **Database logs**: 7 days

Old logs are automatically deleted by Laravel.

## Best Practices

1. **Never log sensitive data** (passwords, credit cards, personal information)
2. **Use appropriate log levels**:
   - `debug`: Detailed debug information
   - `info`: Interesting events (e.g., user actions)
   - `warning`: Exceptional occurrences that are not errors
   - `error`: Runtime errors that don't require immediate action
   - `critical`: Critical conditions (e.g., database unavailable)

3. **Monitor logs regularly** for:
   - Repeated failed login attempts
   - 500 errors
   - Unauthorized access attempts
   - Database connection issues

4. **Set up log monitoring** (optional):
   - [Sentry](https://sentry.io) for error tracking
   - [Papertrail](https://papertrailapp.com) for centralized logging
   - [Bugsnag](https://bugsnag.com) for error monitoring

## Manual Logging

You can manually log events in your code:

```php
use Illuminate\Support\Facades\Log;

// Log to default channel
Log::info('Product created', ['product_id' => $product->id]);

// Log to specific channel
Log::channel('security')->warning('Unauthorized access attempt', [
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
]);

// Log to activity channel
Log::channel('activity')->info('User performed action', [
    'user_id' => auth()->id(),
    'action' => 'exported_report',
]);

// Log database issues
Log::channel('database')->error('Query failed', [
    'query' => $query,
    'error' => $exception->getMessage(),
]);
```

## Troubleshooting

### Logs not being created

1. Check storage permissions:
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. Verify `.env` configuration is correct

### Disk space issues

If logs are taking too much space:

1. Reduce `LOG_DAILY_DAYS` in `.env`
2. Manually clean old logs:
   ```bash
   find storage/logs -name "*.log" -mtime +30 -delete
   ```
3. Set up log rotation with logrotate (Linux)

## Security Considerations

1. **Protect log files** - Ensure logs are not publicly accessible
2. **Review logs regularly** for security incidents
3. **Set up alerts** for critical errors
4. **Backup logs** before deleting for compliance
5. **Sanitize logs** - Never log passwords or sensitive data

## Compliance

For compliance requirements (GDPR, HIPAA, etc.):

- Logs contain user information (ID, email, IP)
- Consider anonymizing logs after a certain period
- Implement log retention policies
- Provide mechanisms for users to request log deletion
- Encrypt logs at rest if required
