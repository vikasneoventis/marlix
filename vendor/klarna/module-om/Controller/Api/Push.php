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
use Klarna\Ordermanagement\Model\Api\Factory;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * API call to notify Magento that the order is now ready to receive order management calls
 *
 * @package Klarna\Ordermanagement\Controller\Api
 */
class Push extends Action
{
    use CommonController;

    /**
     * @var MageOrderRepository
     */
    protected $mageOrderRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var Ordermanagement
     */
    protected $om;

    /**
     * @var Factory
     */
    protected $omFactory;

    /**
     * Push constructor.
     *
     * @param Context               $context
     * @param LoggerInterface       $logger
     * @param PageFactory           $resultPageFactory
     * @param JsonFactory           $resultJsonFactory
     * @param JsonHelper            $jsonHelper
     * @param QuoteRepository       $quoteRepository
     * @param MageQuoteRepository   $mageQuoteRepository
     * @param ConfigHelper          $configHelper
     * @param OrderRepository       $orderRepository
     * @param MageOrderRepository   $mageOrderRepository
     * @param Factory               $omFactory
     * @param StoreManagerInterface $store
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        JsonHelper $jsonHelper,
        ConfigHelper $configHelper,
        OrderRepository $orderRepository,
        MageOrderRepository $mageOrderRepository,
        Factory $omFactory,
        StoreManagerInterface $store
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->omFactory = $omFactory;
    }

    public function execute()
    {
        $this->checkIsPost();

        $checkoutId = $this->getRequest()->getParam('id');
        $responseCodeObject = new DataObject([
            'response_code' => 200
        ]);

        $order = null;

        try {
            $klarnaOrder = $this->orderRepository->getByKlarnaOrderId($checkoutId);
            if (!$klarnaOrder->getId()) {
                throw new KlarnaException(__('Klarna Order not found'));
            }

            try {
                $order = $this->mageOrderRepository->get($klarnaOrder->getOrderId());
            } catch (NoSuchEntityException $nse) {
                throw new KlarnaException(__('Magento Order not found'));
            }

            $store = $order->getStore();

            $this->_eventManager->dispatch(
                'klarna_push_notification_before',
                [
                    'order'                => $order,
                    'klarna_order_id'      => $checkoutId,
                    'response_code_object' => $responseCodeObject,
                ]
            );

            $this->getOmApi($order);

            // Add comment to order and update status if still in payment review
            if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
                /** @var Payment $payment */
                $payment = $order->getPayment();
                $payment->update(true);

                $statusObject = new DataObject(
                    [
                        'status' => $this->configHelper->getProcessedOrderStatus($store->getCode())
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
                    $order->addStatusHistoryComment(__('Order processed by Klarna.'), $statusObject->getStatus());
                }
            }

            $checkoutType = $this->configHelper->getCheckoutType($store->getCode());
            $this->_eventManager->dispatch(
                "klarna_push_notification_after_type_{$checkoutType}",
                [
                    'order'                => $order,
                    'klarna_order'         => $klarnaOrder,
                    'response_code_object' => $responseCodeObject,
                ]
            );

            $this->_eventManager->dispatch(
                'klarna_push_notification_after',
                [
                    'order'                => $order,
                    'klarna_order'         => $klarnaOrder,
                    'response_code_object' => $responseCodeObject,
                ]
            );

            $merchantReferences = new DataObject([
                'merchant_reference_1' => $order->getIncrementId(),
                'merchant_reference_2' => ''
            ]);

            $this->_eventManager->dispatch(
                'klarna_rest_merchant_reference_update',
                [
                    'order_id'            => $checkoutId,
                    'merchant_references' => $merchantReferences
                ]
            );

            // Update order references
            $this->om->updateMerchantReferences(
                $checkoutId,
                $merchantReferences->getMerchantReference1(),
                $merchantReferences->getMerchantReference2()
            );

            // Acknowledge order
            if ($order->getState() !== Order::STATE_PAYMENT_REVIEW && !$klarnaOrder->getIsAcknowledged()) {
                $response = $this->om->acknowledgeOrder($checkoutId);
                if (!$response->getIsSuccessful()) {
                    $this->log($response->getError(), LogLevel::ERROR, $response->getPayload());
                    // TODO: Consider: Should we cancel order in Magento here?
                    throw new \Exception((string)__('Acknowledge call failed. Check log for details'));
                }
                $order->addStatusHistoryComment('Acknowledged request sent to Klarna');
                $klarnaOrder->setIsAcknowledged(1);
                $this->orderRepository->save($klarnaOrder);
            }

            // Cancel order in Klarna if cancelled on store
            if ($order->isCanceled()) {
                $this->_eventManager->dispatch(
                    'klarna_cancel_order',
                    [
                        'klarna_order_id'   => $checkoutId,
                        'method_code'       => 'klarna_kco', // Push is only used for KCO
                        'controller_action' => $this,
                        'reason'            => 'Order Canceled in Magento'
                    ]
                );
            }

            $this->mageOrderRepository->save($order);
        } catch (KlarnaException $e) {
            $message = $e;
            if ($e->getMessage() === 'Klarna Order not found') {
                $message = 'Could not find order in Magento - ' . $checkoutId;
            }
            $this->log($message, LogLevel::ERROR);
            $responseCodeObject->setResponseCode(500);
            $cancelObject = new DataObject([
                'cancel_order' => true
            ]);
            $this->_eventManager->dispatch(
                'klarna_push_notification_order_not_found',
                [
                    'klarna_order_id'      => $checkoutId,
                    'cancel_object'        => $cancelObject,
                    'response_code_object' => $responseCodeObject,
                    'method_code'          => 'klarna_kco', // Push is only used for KCO
                    'controller_action'    => $this,
                    'reason'               => $message
                ]
            );
            return $this->sendBadRequestResponse($e->getMessage(), $responseCodeObject->getResponseCode());
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            $responseCodeObject->setResponseCode(500);
            $this->_eventManager->dispatch(
                'klarna_push_notification_failed',
                [
                    'order'                => $order,
                    'klarna_order_id'      => $checkoutId,
                    'response_code_object' => $responseCodeObject,
                    'method_code'          => 'klarna_kco', // Push is only used for KCO
                    'controller_action'    => $this,
                    'reason'               => $e->getMessage()
                ]
            );
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse($e->getMessage(), $responseCodeObject->getResponseCode());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(200);
    }

    /**
     * Get api class
     *
     * @param OrderInterface $order
     * @return Ordermanagement
     * @internal param Store $store
     */
    protected function getOmApi(OrderInterface $order)
    {
        $store = $order->getStore();
        if ($this->om === null) {
            $omClass = $this->configHelper->getOrderMangagementClass($store);
            $this->om = $this->omFactory->create($omClass);
            $this->om->resetForStore($store, 'klarna_kco'); // Push is only used for KCO
        }

        return $this->om;
    }
}
