<?php
/**
 * Copyright © Vendor. All rights reserved.
 */
declare(strict_types=1);

namespace Vendor\AutoCacheFlush\Test\Unit\Cron;

use Magento\Framework\App\Cache\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Vendor\AutoCacheFlush\Cron\FlushCache;

/**
 * Unit test for FlushCache cron job.
 */
class FlushCacheTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    private $cacheManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FlushCache
     */
    private $flushCache;

    /**
     * Set up test dependencies.
     */
    protected function setUp(): void
    {
        $this->cacheManagerMock = $this->createMock(Manager::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->flushCache = new FlushCache(
            $this->cacheManagerMock,
            $this->loggerMock
        );
    }

    /**
     * Test successful cache flush execution.
     *
     * @return void
     */
    public function testExecuteSuccess(): void
    {
        // Expect flush to be called once with empty array
        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->with([])
            ->willReturn(true);

        // Expect info log to be called once with success message
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Cache flushed successfully via cron.');

        // Expect warning and error not to be called
        $this->loggerMock->expects($this->never())
            ->method('warning');
        $this->loggerMock->expects($this->never())
            ->method('error');

        // Execute the cron job
        $this->flushCache->execute();
    }

    /**
     * Test cache flush execution when flush returns false.
     *
     * @return void
     */
    public function testExecuteReturnsFalse(): void
    {
        // Expect flush to be called once with empty array
        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->with([])
            ->willReturn(false);

        // Expect warning log to be called once
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Cache flush attempt returned false, or nothing to flush.');

        // Expect info and error not to be called
        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->never())
            ->method('error');

        // Execute the cron job
        $this->flushCache->execute();
    }

    /**
     * Test cache flush execution when an exception occurs.
     *
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $exceptionMessage = 'Cache flush failed due to system error';
        $exception = new \Exception($exceptionMessage);

        // Expect flush to be called once and throw an exception
        $this->cacheManagerMock->expects($this->once())
            ->method('flush')
            ->with([])
            ->willThrowException($exception);

        // Expect error log to be called once with exception message
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Error flushing cache via cron: ' . $exceptionMessage);

        // Expect info and warning not to be called
        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->never())
            ->method('warning');

        // Execute the cron job - should not throw exception
        $this->flushCache->execute();
    }
}
