# Vendor AutoCacheFlush Module

**Automated project built via Dev Agents.**

A robust Magento 2 module that automatically flushes the system cache at a fixed interval of every two hours via the native cron scheduler, thereby reducing manual administrative overhead and ensuring optimal site performance.

## Features

- **Automated Scheduling**: Automatically runs every 2 hours (00:00, 02:00, 04:00, etc.).
- **Optimized Performance**: Reduces manual administrative overhead and ensures optimal site performance by preventing stale cache.
- **Error Handling & Logging**: Includes comprehensive logging to `var/log/system.log` for success and failure scenarios.
- **Unit Tested**: Includes unit tests to verify cron job execution and cache flushing logic.

## Installation

1. **Copy the files** to your Magento installation:
   ```bash
   cp -r app/code/Vendor/AutoCacheFlush /path/to/magento/app/code/Vendor/
   ```

2. **Enable the module**:
   ```bash
   php bin/magento module:enable Vendor_AutoCacheFlush
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   ```

3. **Flush the cache**:
   ```bash
   php bin/magento cache:flush
   ```

## Usage

The module works automatically via Magento's cron job system.
- **Cron Job Name**: `auto_cache_flush`
- **Schedule**: `0 */2 * * *` (Every 2 hours)

### Manual Verification

To verify the module is working without waiting for the next schedule:

1. **Run cron manually**:
   ```bash
   php bin/magento cron:run --group=default
   ```

2. **Check the System Log**:
   Monitor `var/log/system.log`. You should see one of the following messages:
   - `Cache flushed successfully via cron.`
   - `Cache flush attempt returned false, or nothing to flush.`
   - `Error flushing cache via cron: ...` (if an error occurred)

3. **Check Cron Schedule Table**:
   Run the following SQL query to see scheduled executions:
   ```sql
   SELECT * FROM cron_schedule WHERE job_code = 'auto_cache_flush';
   ```

## Testing

Unit tests are provided in `Test/Unit/Cron/FlushCacheTest.php`. You can run them using Magento's test framework or PHPUnit directly within the Magento environment.

```bash
php bin/magento dev:tests:run unit
```

## Documentation Context

For more details on the project architecture and implementation decisions, please refer to the Golden Context files:
- `project_brief.md`
- `technical_spec.md`
- `implementation_plan.md`
- `coding_guidelines.md`
