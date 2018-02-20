<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Observer;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\OrderRepository as KlarnaOrderRepository;
use Klarna\Kco\Helper\ApiHelper;
use Klarna\Kco\Model\Payment\Kco;
use Klarna\Kred\Model\PushqueueRepository;
use Klarna\Ordermanagement\Api\ApiInterface;
use Klarna\Ordermanagement\Model\Api\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AcknowledgeKredOrderOnConfirmation implements ObserverInterface
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var PushqueueRepository
     */
    protected $pushqueueRepository;

    /**
     * @var MageOrderRepository
     */
    protected $mageOrderRepository;

    /**
     * @var KlarnaOrderRepository
     */
    protected $klarnaOrderRepository;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ApiInterface
     */
    protected $om;

    /**
     * AcknowledgeKredOrderOnConfirmation constructor.
     *
     * @param LoggerInterface       $log
     * @param ConfigHelper          $configHelper
     * @param ManagerInterface      $eventManager
     * @param PushqueueRepository   $pushqueueRepository
     * @param MageOrderRepository   $mageOrderRepository
     * @param KlarnaOrderRepository $klarnaOrderRepository
     * @param ApiHelper             $apiHelper
     * @param Factory               $omFactory
     * @param StoreManagerInterface $store
     */
    public function __construct(
        LoggerInterface $log,
        ConfigHelper $configHelper,
        ManagerInterface $eventManager,
        PushqueueRepository $pushqueueRepository,
        MageOrderRepository $mageOrderRepository,
        KlarnaOrderRepository $klarnaOrderRepository,
        ApiHelper $apiHelper,
        Factory $omFactory,
        StoreManagerInterface $store
    ) {
        $this->log = $log;
        $this->configHelper = $configHelper;
        $this->eventManager = $eventManager;
        $this->pushqueueRepository = $pushqueueRepository;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->klarnaOrderRepository = $klarnaOrderRepository;
        $this->apiHelper = $apiHelper;
        /** @var StoreInterface $store */
        $store = $store->getStore();
        $omClass = $this->configHelper->getOrderMangagementClass($store);
        $this->om = $omFactory->create($omClass);
        $this->om->resetForStore($store, Kco::METHOD_CODE);
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        /** @var \Klarna\Core\Model\Order $klarnaOrder */
        $klarnaOrder = $observer->getKlarnaOrder();

        $checkoutId = $klarnaOrder->getKlarnaOrderId();
        $checkoutType = $this->configHelper->getCheckoutType($order->getStore());
        $pushQueue = $this->pushqueueRepository->getByCheckoutId($checkoutId);

        try {
            if ('kred' == $checkoutType
                && !$klarnaOrder->getIsAcknowledged()
                && $pushQueue->getId()
            ) {
                /** @var Payment $payment */
                $payment = $order->getPayment();
                $payment->update(true);
                $status = false;

                if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
                    $statusObject = new DataObject([
                        'status' => $this->configHelper->getProcessedOrderStatus($order->getStore(), KCO::METHOD_CODE)
                    ]);

                    $this->eventManager->dispatch('kco_push_notification_before_set_state', [
                        'order'         => $order,
                        'klarna_order'  => $klarnaOrder,
                        'status_object' => $statusObject
                    ]);

                    if (Order::STATE_PROCESSING === $order->getState()) {
                        $status = $statusObject->getStatus();
                    }
                }
                $order->addStatusHistoryComment(__('Order processed by Klarna.'), $status);

                $this->om->updateMerchantReferences($checkoutId, $order->getIncrementId());
                $response = $this->om->acknowledgeOrder($checkoutId);
                if (!$response->getIsSuccessful()) {
                    $this->log->error($response->getError());
                    $this->log->error($response->getPayload());
                    // TODO: Consider: Should we cancel order in Magento here?
                    return;
                }
                $order->addStatusHistoryComment('Acknowledged request sent to Klarna');
                $this->mageOrderRepository->save($order);
                $klarnaOrder->setIsAcknowledged(1);
                $this->klarnaOrderRepository->save($klarnaOrder);
            }
        } catch (\Exception $e) {
            $this->log->error($e);
        }
    }
}
