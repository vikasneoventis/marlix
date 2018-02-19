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

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Ordermanagement\Model\Api\Factory;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Set additional payment details on an order during the push notification
 */
class AddOrderDetailsOnPush implements ObserverInterface
{

    /**
     * @var Ordermanagement
     */
    protected $om;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Factory
     */
    protected $omFactory;

    /**
     * AddOrderDetailsOnPush constructor.
     *
     * @param Factory      $omFactory
     * @param ConfigHelper $configHelper
     */
    public function __construct(Factory $omFactory, ConfigHelper $configHelper)
    {
        $this->omFactory = $omFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \RuntimeException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        /** @var \Klarna\Core\Model\Order $klarnaOrder */
        $klarnaOrder = $observer->getKlarnaOrder();

        $this->configureOm($order);

        /** @var DataObject $klarnaOrderDetails */
        $klarnaOrderDetails = $this->om->getPlacedKlarnaOrder($klarnaOrder->getKlarnaOrderId());

        // Add invoice to order details
        if ($klarnaReference = $klarnaOrderDetails->getKlarnaReference()) {
            $order->getPayment()->setAdditionalInformation('klarna_reference', $klarnaReference);
        }
    }

    /**
     * Configure OM for order
     *
     * @param OrderInterface $order
     */
    protected function configureOm($order)
    {
        $store = $order->getStore();
        $omClass = $this->configHelper->getOrderMangagementClass($store);
        $this->om = $this->omFactory->create($omClass);
        $this->om->resetForStore($store, 'klarna_kco'); // Push only used for KCO
    }
}
