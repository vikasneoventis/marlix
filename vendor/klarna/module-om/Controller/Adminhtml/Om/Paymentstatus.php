<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Controller\Adminhtml\Om;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Klarna\Core\Api\OrderInterface;
use Klarna\Core\Model\OrderRepository as KlarnaOrderRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Paymentstatus extends Action
{
    protected $mageOrderRepository;

    /**
     * Klarna Order Repository
     *
     * @var KlarnaOrderRepository
     */
    protected $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Klarna\Ordermanagement\Model\Paymentstatus
     */
    private $paymentstatus;

    /**
     * Acknowledge constructor.
     *
     * @param Action\Context                              $context
     * @param KlarnaOrderRepository                       $orderRepository
     * @param MageOrderRepository                         $mageOrderRepository
     * @param StoreManagerInterface                       $storeManager
     * @param \Klarna\Ordermanagement\Model\Paymentstatus $paymentstatus
     */
    public function __construct(
        Action\Context $context,
        KlarnaOrderRepository $orderRepository,
        MageOrderRepository $mageOrderRepository,
        StoreManagerInterface $storeManager,
        \Klarna\Ordermanagement\Model\Paymentstatus $paymentstatus
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->storeManager = $storeManager;
        $this->paymentstatus = $paymentstatus;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $orderId = $this->_request->getParam('id');
        $url = $this->_url->getUrl('sales/order/view/order_id/' . $orderId);
        $storeId = $this->_request->getParam('store');
        try {
            $klarnaOrder = $this->getKlarnaOrder($orderId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Canceling order as Klarna order information is missing'));
            $mageOrder = $this->mageOrderRepository->get($orderId);
            $payment = $mageOrder->getPayment();
            $payment->setNotificationResult(true);
            $payment->deny(false);
            $this->mageOrderRepository->save($mageOrder);
            return $this->_redirect($url);
        }

        $orderDetails = $this->paymentstatus->getStatusUpdate($klarnaOrder);
        $stopStatuses = ['CANCELLED', 'EXPIRED'];
        if (in_array($orderDetails->getStatus(), $stopStatuses)) {
            $this->messageManager->addErrorMessage(__('Canceling order as Klarna shows it as %1', $orderDetails->getStatus()));
            $mageOrder = $this->mageOrderRepository->get($orderId);
            $payment = $mageOrder->getPayment();
            $payment->setNotificationResult(true);
            $payment->deny(false);
            $this->mageOrderRepository->save($mageOrder);
            return $this->_redirect($url);
        }
        if ($orderDetails->getFraudStatus() !== \Klarna\Ordermanagement\Model\Api\Ordermanagement::ORDER_FRAUD_STATUS_PENDING) {
            $this->doPush($storeId, $klarnaOrder);
            return $this->_redirect($url);
        }
        $this->messageManager->addErrorMessage('Order is still PENDING with Klarna');
        return $this->_redirect($url);
    }

    /**
     * Get Klarna order for given Magento order ID
     *
     * @param int $orderId
     * @return OrderInterface
     */
    private function getKlarnaOrder($orderId)
    {
        $mageOrder = $this->mageOrderRepository->get($orderId);
        return $this->orderRepository->getByOrder($mageOrder);
    }

    private function doPush($storeId, OrderInterface $klarnaOrder)
    {
        $client = new Client();
        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_DIRECT_LINK, true);
        $url = $baseUrl . 'klarna/api/push/id/' . $klarnaOrder->getKlarnaOrderId();
        try {
            $res = $client->post($url);
            if ($res->getStatusCode() === 200) {
                $this->messageManager->addSuccessMessage('Order updated');
            } else {
                $this->messageManager->addErrorMessage($res->getReasonPhrase());
            }
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->messageManager->addErrorMessage('Order not found or previously canceled with Klarna');
                $mageOrder = $this->mageOrderRepository->get($klarnaOrder->getOrderId());
                $payment = $mageOrder->getPayment();
                $payment->setNotificationResult(true);
                $payment->deny(false);
                $this->mageOrderRepository->save($mageOrder);
            } else {
                $this->messageManager->addErrorMessage($e->getCode() . ': ' . $res->getReasonPhrase());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getCode() . ': Unknown Error Occured - ');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Klarna_Ordermanagement::payment_status');
    }
}
