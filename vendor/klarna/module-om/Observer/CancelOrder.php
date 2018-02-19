<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Observer;

use Klarna\Core\Api\OrderRepositoryInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Ordermanagement\Api\ApiInterface;
use Klarna\Ordermanagement\Model\Api\Factory;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CancelOrder implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var Ordermanagement
     */
    protected $om;
    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var ConfigHelper
     */

    protected $helper;

    /**
     * @var Factory
     */
    protected $omFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    protected $quoteResourceModel;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $mageOrderRepository;


    /**
     * CancelOrder constructor.
     *
     * @param LoggerInterface          $log
     * @param Ordermanagement          $om
     * @param StoreManagerInterface    $store
     * @param ConfigHelper             $helper
     * @param Factory                  $omFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        LoggerInterface $log,
        Ordermanagement $om,
        StoreManagerInterface $store,
        ConfigHelper $helper,
        Factory $omFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResourceModel,
        \Magento\Sales\Api\OrderRepositoryInterface $mageOrderRepository
    ) {
        $this->log = $log;
        $this->om = $om;
        $this->store = $store->getStore();
        $this->helper = $helper;
        $this->omFactory = $omFactory;
        $this->orderRepository = $orderRepository;
        $this->quoteResourceModel = $quoteResourceModel;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $checkoutId = $observer->getKlarnaOrderId();

        if ($checkoutId === null) {
            return;
        }

        $kOrder = $this->orderRepository->getByKlarnaOrderId($checkoutId);
        if (!$kOrder->getId() && !$this->helper->getDelayedPushNotification($this->store)) {
            // If no order exists and API does not have a delay before the push notices,
            // don't cancel.  It's likely the push happened too quickly.  See
            // LogOrderPushNotification observer
            $this->log->debug('Delaying canceling order as delayed push is enabled');
            return;
        }

        try {
            $om = $this->getOmApi($this->store, $observer->getMethodCode());
            $order = $om->getPlacedKlarnaOrder($checkoutId);
            $klarnaId = $order->getReservation();
            if (!$klarnaId) {
                $klarnaId = $checkoutId;
            }
            if ($order->getStatus() != 'CANCELED') {
                $om->cancel($klarnaId);
                $this->log->debug('Canceled order with Klarna - ' . $observer->getReason());
            }
        } catch (\Exception $e) {
            $this->log->error($e);
        }

        try {
            $mageOrder = $observer->getOrder();
            if ($mageOrder) {
                $mageOrder->cancel();
                $this->log->debug('Canceled order in Magento');
            } else {
                $this->log->debug('Magento order object not available to cancel');
            }
            /** @var Quote $quote */
            $quote = $observer->getQuote();
            if ($quote) {
                $quote->setReservedOrderId(null);
                $quote->setIsActive(1);
                // STFU and just save the quote
                $this->quoteResourceModel->save($quote->collectTotals());
            }
        } catch (\Exception $e) {
            $this->log->error($e);
        }
    }

    /**
     * Get api class
     *
     * @param StoreInterface $store
     * @param                $methodCode
     * @return ApiInterface
     * @internal param $method_code
     */
    protected function getOmApi(StoreInterface $store, $methodCode)
    {
        $omClass = $this->helper->getOrderMangagementClass($store);
        /** @var ApiInterface $om */
        $this->om = $this->omFactory->create($omClass);
        $this->om->resetForStore($store, $methodCode);
        return $this->om;
    }
}
