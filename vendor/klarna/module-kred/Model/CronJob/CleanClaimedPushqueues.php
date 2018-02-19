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

use Magento\Store\Model\StoresConfig;

class CleanClaimedPushqueues
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param StoresConfig                                               $storesConfig
     * @param \Psr\Log\LoggerInterface                                   $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Psr\Log\LoggerInterface $logger,
        \Klarna\Kred\Model\ResourceModel\Pushqueue\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->logger = $logger;
        $this->factory = $collectionFactory;
    }

    /**
     * Clean claimed pushqueues (cron process)
     *
     * @return void
     */
    public function execute()
    {
        /** @var $pushqueues \Klarna\Kred\Model\ResourceModel\Pushqueue\Collection */
        $pushqueues = $this->factory->create();
        $pushqueues->addFieldToFilter('is_acknowledged', 1);
        $pushqueues->join(
            ['kco_order' => $pushqueues->getTable('klarna_core_order')],
            'main_table.klarna_checkout_id = kco_order.klarna_order_id'
        );

        try {
            $pushqueues->walk('delete');
        } catch (\Exception $e) {
            $this->logger->error('Error deleting claimed pushqueues: ' . $e->getMessage());
        }
    }
}
