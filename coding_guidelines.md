# Development Guidelines

## File Structure
```text
app/code/Vendor/AutoCacheFlush/
├── registration.php
├── etc/
│   ├── module.xml
│   └── crontab.xml
└── Cron/
    └── FlushCache.php
```

## Naming Conventions
*   **Namespaces**: Follow PSR-4, e.g., `Vendor\AutoCacheFlush\Cron`.
*   **Classes**: PascalCase, e.g., `FlushCache`.
*   **Methods**: camelCase, e.g., `execute`.
*   **XML Files**: lowercase, e.g., `crontab.xml`.
*   **Variables**: camelCase, e.g., `$cacheManager`.

## Coding Standards
*   Adherence to **MEP2 (Magento Extension Quality Program)** coding standards.
*   **PSR-12** coding style guide.
*   Strict type hinting where applicable (PHP 7.4+).
*   No direct use of `ObjectManager` in class files (use Constructor Injection).
*   All logic must be enclosed in `try-catch` blocks to prevent cron failures from crashing the scheduler.

## Testing Strategy
1.  **Unit Testing**: Write a PHPUnit test to mock `CacheManagerInterface` and verify the `flush` method is called.
2.  **Integration Testing**: Enable the module in a development environment and run `php bin/magento cron:run --group=default`.
3.  **Manual Verification**:
    *   Check `SELECT * FROM cron_schedule WHERE job_code LIKE '%cache%';`.
    *   Load a page to generate cache, wait/run cron, check if cache is cleared.
4.  **Log Verification**: Tail `var/log/system.log` to confirm execution entries.

## Error Handling
1.  **Try-Catch Blocks**: Wrap the main logic in the `execute` method.
2.  **Logging**: Use `Psr\Log\LoggerInterface` (injected via constructor) to write exceptions to `exception.log`.
3.  **Graceful Degradation**: If the cache flush fails, the cron should mark as 'error' or 'missed' in the schedule table but not bring down the system.

## Dependencies
*   `magento/framework` (Core Magento functionality)
*   `php` (>= 7.4)
*   Composer packages are inherited from the Magento core metapackage; no external `composer.json` dependencies are strictly required for this basic module other than Magento itself.

## Configuration
1.  `etc/module.xml`: Defines module name and version.
2.  `etc/crontab.xml`: Defines the cron job instance:
    *   `job_name`: `auto_cache_flush`
    *   `schedule`: `0 */2 * * *` (Run at minute 0 past every 2nd hour).
