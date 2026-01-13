<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

declare(strict_types=1);

namespace Vendor\AutoCacheFlush\Cron;

use Magento\Framework\App\Cache\Manager;
use Psr\Log\LoggerInterface;

/**
 * Class FlushCache
 *
 * Cron job to automatically flush Magento cache
 */
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
     * Constructor
     *
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
     * Execute cache flush
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $result = $this->cacheManager->flush([]);

            if ($result) {
                $this->logger->info('Cache flushed successfully via AutoCacheFlush cron job.');
            } else {
                $this->logger->warning('Cache flush returned false in AutoCacheFlush cron job.');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error flushing cache: ' . $e->getMessage());
        }
    }
}
