# Development Guidelines

## File Structure
```text
app/code/Vendor/AutoCacheFlush/
├── registration.php
├── etc/
│   ├── module.xml
│   └── crontab.xml
├── Cron/
│   └── FlushCache.php
└── Test/
    └── Unit/
        └── Cron/
            └── FlushCacheTest.php
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
2.  **Logging**: Use `Psr\Log\LoggerInterface` (injected via constructor) to write exceptions to `exception.log` or `system.log`.
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

### [RELEVANT CODE]
#### registration.php
```php
<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Vendor_AutoCacheFlush',
    __DIR__
);
```

#### etc/module.xml
```xml
<?xml version="1.0"?>
<!--
/**
 * Copyright © Vendor. All rights reserved.
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Vendor_AutoCacheFlush" setup_version="1.0.0" />
</config>
```

#### etc/crontab.xml
```xml
<?xml version="1.0"?>
<!--
/**
 * Copyright © Vendor. All rights reserved.
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Cron/etc/crontab.xsd">
    <group id="default">
        <job name="auto_cache_flush" instance="Vendor\AutoCacheFlush\Cron\FlushCache" method="execute">
            <schedule>0 */2 * * *</schedule>
        </job>
    </group>
</config>
```

#### Cron/FlushCache.php
```php
<?php
/**
 * Copyright © Vendor. All rights reserved.
 */
declare(strict_types=1);

namespace Vendor\AutoCacheFlush\Cron;

use Magento\Framework\App\Cache\Manager;
use Psr\Log\LoggerInterface;

class FlushCache
{
    /**
     * @var Manager
     */
    private Manager $cacheManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Manager $cacheManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Manager $cacheManager,
        LoggerInterface $logger
    ) {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * Execute the cron job to flush cache.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $flushed = $this->cacheManager->flush([]);
            if ($flushed) {
                $this->logger->info('Cache flushed successfully via cron.');
            } else {
                $this->logger->warning('Cache flush attempt returned false, or nothing to flush.');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error flushing cache via cron: ' . $e->getMessage());
        }
    }
}
```

#### Test/Unit/Cron/FlushCacheTest.php
```php
<?php
/**
 * Copyright © Vendor. All rights reserved.
 */
declare(strict_types=1);

namespace Vendor\AutoCacheFlush\Test\Unit\Cron;

use PHPUnit\Framework\TestCase;
use Vendor\AutoCacheFlush\Cron\FlushCache;
use Magento\Framework\App\Cache\Manager;
use Psr\Log\LoggerInterface;

class FlushCacheTest extends TestCase
{
    /**
     * @var FlushCache
     */
    private FlushCache $flushCache;

    /**
     * @var Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheManagerMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->cacheManagerMock = $this->createMock(Manager::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->flushCache = new FlushCache(
            $this->cacheManagerMock,
            $this->loggerMock
        );
    }

    public function testExecuteFlushesCacheSuccessfully()
    {
        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->with([])
            ->willReturn(true);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Cache flushed successfully via cron.');

        $this->flushCache->execute();
    }

    public function testExecuteLogsWarningIfFlushFails()
    {
        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->willReturn(false);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Cache flush attempt returned false, or nothing to flush.');

        $this->flushCache->execute();
    }

    public function testExecuteLogsErrorOnException()
    {
        $exception = new \Exception('Test error');

        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Error flushing cache via cron: Test error');

        $this->flushCache->execute();
    }
}
```
