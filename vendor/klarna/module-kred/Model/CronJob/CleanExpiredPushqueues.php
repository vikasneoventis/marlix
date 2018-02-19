<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model\CronJob;

use Klarna\Kred\Model\ResourceModel\Pushqueue\CollectionFactory;
use Magento\Store\Model\StoresConfig;
use Psr\Log\LoggerInterface;

class CleanExpiredPushqueues
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var CollectionFactory
     */
    protected $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param StoresConfig                                               $storesConfig
     * @param LoggerInterface                                   $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->logger = $logger;
        $this->factory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetime = $this->storesConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        if (!is_int($lifetime)) {
            return;
        }
        $lifetime *= 86400;
        /** @var $pushqueues \Klarna\Kred\Model\ResourceModel\Pushqueue\Collection */
        $pushqueues = $this->factory->create();
        $pushqueues->getSelect()->where(
            'TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `update_time`)) >= ' . $lifetime
        );

        try {
            $pushqueues->walk('delete');
        } catch (\Exception $e) {
            $this->logger->error('Error deleting expired pushqueues: ' . $e->getMessage());
        }
    }
}
