# Magento 2 Auto Cache Flush Module

A robust Magento 2 module that automatically flushes the system cache at a fixed interval of every two hours via the native cron scheduler, reducing manual administrative overhead and ensuring optimal site performance.

## Features

- **Automated Scheduling**: Background task runs every 2 hours (at 00:00, 02:00, 04:00, etc.)
- **Cache Flushing**: Full invalidation of Magento cache types using the framework's Cache Manager
- **Detailed Logging**: Logs execution status to `var/log/system.log` for debugging
- **Error Handling**: Graceful degradation with exception logging to prevent scheduler crashes

## Requirements

- PHP 7.4+
- Magento 2.4.x (Open Source / Commerce CE/EE)
- Composer (for dependency management)
- Cron jobs configured on the server

## Installation

### Method 1: Manual Installation

1. **Copy the module files** to your Magento installation:

   ```bash
   cp -r app/code/Vendor/AutoCacheFlush /path/to/magento/app/code/
   ```

2. **Enable the module** using Magento CLI:

   ```bash
   cd /path/to/magento
   php bin/magento module:enable Vendor_AutoCacheFlush
   ```

3. **Run setup upgrade** to register the module:

   ```bash
   php bin/magento setup:upgrade
   ```

4. **Clean and flush the cache**:

   ```bash
   php bin/magento cache:clean
   php bin/magento cache:flush
   ```

5. **Deploy static content** (if in production mode):

   ```bash
   php bin/magento setup:static-content:deploy
   ```

### Method 2: Composer Installation (Recommended)

If the module is available via Packagist or a private repository:

```bash
composer require vendor/auto-cache-flush
php bin/magento module:enable Vendor_AutoCacheFlush
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Usage

Once installed and enabled, the module operates automatically without any manual intervention:

- The cron job `auto_cache_flush` is scheduled to run every 2 hours
- At each scheduled time, the module executes the cache flush operation
- All cache types managed by Magento are invalidated
- Execution status is logged to the system log

### What Gets Flushed

The module flushes all cache types configured in your Magento installation, including:
- Configuration
- Layouts
- Blocks HTML output
- Collections
- Reflection data
- EAV types and attributes
- Customer Notification
- Integrations
- Integrations API Configuration
- Page Cache
- Translate
- Web Services Configuration

## Verification

To verify that the module is working correctly, follow these steps:

### 1. Check Module Status

```bash
php bin/magento module:status
```

**Expected Output:**
```
List of enabled modules:
...
Vendor_AutoCacheFlush
...
```

### 2. Verify Cron Schedule

**Check the cron schedule table:**

```bash
mysql -u username -p database_name -e "SELECT * FROM cron_schedule WHERE job_code = 'auto_cache_flush' ORDER BY scheduled_at DESC LIMIT 5;"
```

**Expected Output:** You should see entries with:
- `job_code`: `auto_cache_flush`
- `schedule_id`: Auto-increment ID
- `status`: `pending`, `running`, `success`, or `missed`
- `scheduled_at`: Times at every 2-hour interval (e.g., 2024-01-01 00:00:00, 02:00:00, 04:00:00)

### 3. Test Manual Execution

Run the cron job manually to test cache flush logic:

```bash
php bin/magento cron:run --group=default
```

### 4. Check System Logs

Monitor the system log for execution confirmation:

```bash
tail -f var/log/system.log
```

**Expected Output (Success):**
```
[2024-01-01 00:00:01] main.INFO: Cache flushed successfully via cron. [] []
```

**Expected Output (Warning - nothing to flush):**
```
[2024-01-01 00:00:01] main.WARNING: Cache flush attempt returned false, or nothing to flush. [] []
```

**Expected Output (Error):**
```
[2024-01-01 00:00:01] main.ERROR: Error flushing cache via cron: {error message} [] []
```

### 5. Monitor Exception Log

If errors occur, check the exception log:

```bash
tail -f var/log/exception.log
```

### 6. Verify Cache Cleared

1. Load a page in your browser to generate cache
2. Run the cron manually or wait for the scheduled execution
3. Reload the page - it should be regenerated (not served from stale cache)

## Troubleshooting

### Module Not Appearing in Status

- Verify the directory structure: `app/code/Vendor/AutoCacheFlush/`
- Check that `registration.php` and `etc/module.xml` exist
- Ensure file permissions are correct

### Cron Job Not Running

- Verify Magento cron is configured on your server:
  ```bash
  crontab -l
  ```
- Ensure the Magento cron entries are present:
  ```bash
  * * * * * php /path/to/magento/bin/magento cron:run 2>&1 | grep -v "Ran jobs by schedule" >> /path/to/magento/var/log/magento.cron.log
  ```
- Check that the `crontab.xml` file exists and is properly formatted

### Cache Not Flushing

- Check `var/log/system.log` for error messages
- Verify the `FlushCache.php` class exists at `app/code/Vendor/AutoCacheFlush/Cron/FlushCache.php`
- Ensure the `Magento\Framework\App\Cache\Manager` service is available

## Testing

### Unit Tests

Run the unit tests for the FlushCache class:

```bash
php bin/magento dev:tests:run unit --filter FlushCacheTest
```

The tests verify:
- The `flush` method is called on the Cache Manager
- Logging occurs for successful execution
- Logging occurs for failure scenarios
- Error handling works correctly

### Integration Testing

Enable the module in a development environment and run:

```bash
php bin/magento cron:run --group=default
```

## Architecture

### Component Structure

```
app/code/Vendor/AutoCacheFlush/
├── registration.php          # Component registrar
├── etc/
│   ├── module.xml           # Module declaration
│   └── crontab.xml           # Cron schedule definition
└── Cron/
    └── FlushCache.php        # Cache flush worker class
```

### Key Classes

- **Vendor\AutoCacheFlush\Cron\FlushCache**: The cron job worker class that performs the cache flush operation

### Dependencies

- `Magento\Framework\App\Cache\Manager`: Core Magento cache management API
- `Psr\Log\LoggerInterface`: Logging interface for system and exception logging

## Configuration

### Cron Schedule

The cron job is configured in `etc/crontab.xml`:

```xml
<schedule>0 */2 * * *</schedule>
```

This runs the job at minute 0 of every 2nd hour (00:00, 02:00, 04:00, etc.).

To modify the schedule:

1. Edit `app/code/Vendor/AutoCacheFlush/etc/crontab.xml`
2. Update the `<schedule>` tag with your desired cron expression
3. Clear the cache: `php bin/magento cache:flush`

## License

Copyright © Vendor. All rights reserved.

## Support

For issues, questions, or contributions, please refer to the project documentation:
- `project_brief.md` - Project overview and goals
- `technical_spec.md` - Technical architecture and design
- `implementation_plan.md` - Implementation details and acceptance criteria
- `coding_guidelines.md` - Development guidelines and standards
