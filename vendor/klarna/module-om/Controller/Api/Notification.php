<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Controller\Api;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\OrderRepository;
use Klarna\Core\Traits\CommonController;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Order update from pending status
 *
 * @package Klarna\Ordermanagement\Controller\Api
 */
class Notification extends Action
{
    use CommonController;

    /**
     * Magento Order Repository
     *
     * @var MageOrderRepository
     */
    protected $mageOrderRepository;

    /**
     * Klarna Order Repository
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Notification constructor.
     *
     * @param Context             $context
     * @param LoggerInterface     $logger
     * @param JsonHelper          $jsonHelper
     * @param JsonFactory         $resultJsonFactory
     * @param OrderRepository     $orderRepository
     * @param MageOrderRepository $mageOrderRepository
     * @param ConfigHelper        $configHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        JsonHelper $jsonHelper,
        JsonFactory $resultJsonFactory,
        OrderRepository $orderRepository,
        MageOrderRepository $mageOrderRepository,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->checkIsPost();

        $checkoutId = $this->getRequest()->getParam('id');

        try {
            $body = $this->getRequest()->getContent();

            try {
                $notification = $this->jsonHelper->jsonDecode($body);
                $notification = new DataObject($notification);
            } catch (\Exception $e) {
                return $this->sendBadRequestResponse($e->getMessage(), 500);
            }

            if (null === $checkoutId) {
                $checkoutId = $notification->getOrderId();
            }

            /** @var \Klarna\Core\Model\Order $klarnaOrder */
            $klarnaOrder = $this->orderRepository->getByKlarnaOrderId($checkoutId);
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->mageOrderRepository->get($klarnaOrder->getOrderId());

            if (!$order->getId()) {
                throw new KlarnaException('Order not found');
            }

            /** @var Payment $payment */
            $payment = $order->getPayment();

            if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
                switch ($notification->getEventType()) {
                    case Ordermanagement::ORDER_NOTIFICATION_FRAUD_REJECTED:
                        $payment->setNotificationResult(true);
                        $payment->deny(false);
                        break;
                    case Ordermanagement::ORDER_NOTIFICATION_FRAUD_ACCEPTED:
                        $payment->setNotificationResult(true);
                        $payment->accept(false);
                        break;
                }

                $statusObject = new DataObject(
                    [
                        'status' => $this->configHelper->getProcessedOrderStatus($order->getStore(), $payment->getMethod())
                    ]
                );

                $this->_eventManager->dispatch(
                    'klarna_push_notification_before_set_state',
                    [
                        'order'         => $order,
                        'klarna_order'  => $klarnaOrder,
                        'status_object' => $statusObject
                    ]
                );

                if (Order::STATE_PROCESSING === $order->getState()) {
                    $order->addStatusHistoryComment(
                        __('Order processed by Klarna.'),
                        $statusObject->getStatus()
                    );
                }
                $this->mageOrderRepository->save($order);
            } elseif (Ordermanagement::ORDER_NOTIFICATION_FRAUD_REJECTED === $notification->getEventType()
                && $order->getState() !== Order::STATE_PAYMENT_REVIEW
            ) {
                $payment->setNotificationResult(false);
                $payment->setIsFraudDetected(true);
                $payment->deny(false);
                $this->mageOrderRepository->save($order);
            }
        } catch (KlarnaException $e) {
            $this->log($e, LogLevel::ERROR);
            $resultPage = $this->sendBadRequestResponse($e->getMessage(), 500);
            $resultPage->setJsonData(
                $this->jsonHelper->jsonEncode([
                    'error'   => 400,
                    'message' => $e->getMessage(),
                ])
            );
            return $resultPage;
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse($e->getMessage(), 500);
        }
        $resultPage = $this->resultJsonFactory->create();
        $resultPage->setHttpResponseCode(200);
        $resultPage->setData([]);
        return $resultPage;
    }
}
