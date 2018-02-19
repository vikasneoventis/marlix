<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model\Api;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Model\Api\BuilderFactory;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Core\Model\OrderRepository;
use Klarna\Kred\Lib\Connector;
use Klarna\Kred\Lib\MagentoKlarna;
use Klarna\Kred\Model\Api\Builder\Kred as KredBuilder;
use Klarna\Kred\Model\Checkout\Orderline\Shipping;
use Klarna\Kred\Model\PushqueueRepository;
use Klarna\Kred\Traits\Logging;
use Klarna\Ordermanagement\Api\ApiInterface;
use Klarna\XMLRPC\Flags;
use Klarna\XMLRPC\Klarna;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class Ordermanagement implements ApiInterface
{
    use Logging;

    /**
     * Refund full invoice
     *
     * @var string
     */
    const REFUND_TYPE_INVOICE = 'invoice';

    /**
     * Partial refund by article
     *
     * @var string
     */
    const REFUND_TYPE_ARTICLE = 'article';

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PushqueueRepository
     */
    protected $pushqueueRepository;

    /**
     * Items to skip in the capture line item collection
     *
     * @var array
     */
    protected $_captureLineItemSkip = ['shipping', 'discount'];

    /**
     * Items to skip in the refund line item collection
     *
     * @var array
     */
    protected $_refundLineItemSkip = ['shipping', 'discount'];

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var DataObject;
     */
    protected $klarnaOrder;

    /**
     * @var Klarna
     */
    protected $_klarnaOrderManagement;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var \Klarna_Checkout_Order
     */
    protected $_order;

    /**
     * @var \Klarna_Checkout_Connector
     */
    protected $_connector;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * API type code
     *
     * @var string
     */
    protected $builderType = '';

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * Kred constructor.
     *
     * @param BuilderFactory        $builderFactory
     * @param ConfigHelper          $configHelper
     * @param LoggerInterface       $log
     * @param ManagerInterface      $eventManager
     * @param OrderRepository       $orderRepository
     * @param PushqueueRepository   $pushqueueRepository
     * @param DirectoryHelper       $directoryHelper
     * @param MessageManager        $messageManager
     * @param StoreManagerInterface $storeManager
     * @param VersionInfo           $versionInfo
     * @param MagentoKlarna         $klarna
     */
    public function __construct(
        BuilderFactory $builderFactory,
        ConfigHelper $configHelper,
        LoggerInterface $log,
        ManagerInterface $eventManager,
        OrderRepository $orderRepository,
        PushqueueRepository $pushqueueRepository,
        DirectoryHelper $directoryHelper,
        MessageManager $messageManager,
        StoreManagerInterface $storeManager,
        VersionInfo $versionInfo,
        MagentoKlarna $klarna
    ) {
        $this->builderFactory = $builderFactory;
        $this->configHelper = $configHelper;
        $this->log = $log;
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
        $this->pushqueueRepository = $pushqueueRepository;
        $this->directoryHelper = $directoryHelper;
        $this->builderType = KredBuilder::class;
        $this->messageManager = $messageManager;
        $this->versionInfo = $versionInfo;
        $this->_klarnaOrderManagement = $klarna;
    }

    /**
     * Capture an amount on an order
     *
     * @param string  $orderId
     * @param float   $amount
     * @param Invoice $invoice
     * @return DataObject
     * @throws \Exception
     */
    public function capture($orderId, $amount, $invoice = null)
    {
        if (!$invoice instanceof Invoice) {
            $e = new KlarnaException(__('Capture invoice must be an instance %1', Invoice::class));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        $response = new DataObject;

        try {
            $orderItems = $this->_getGenerator()
                               ->setObject($invoice)
                               ->collectOrderLines($invoice->getStore())
                               ->getOrderLines();

            $this->eventManager->dispatch(
                'kco_kred_capture_items_before',
                [
                    'object'      => $invoice,
                    'api'         => $this->_getKlarnaOrderManagement(),
                    'invoice'     => $invoice,
                    'order_items' => $orderItems
                ]
            );

            $resultArray = $this->_captureDiscounts($invoice, $orderItems)
                                ->_captureShipping($invoice, $orderItems)
                                ->_captureItems($invoice, $orderItems)
                                ->_getKlarnaOrderManagement()
                                ->activate($orderId, null, Flags::RSRV_SEND_BY_EMAIL);

            $this->eventManager->dispatch(
                'kco_kred_capture_items_after',
                [
                    'object'      => $invoice,
                    'invoice'     => $invoice,
                    'api'         => $this->_getKlarnaOrderManagement(),
                    'order_items' => $orderItems
                ]
            );
        } catch (\Exception $e) {
            $this->_debug($this->_getKlarnaOrderManagement());
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        $this->_debug($this->_getKlarnaOrderManagement());

        list($result, $reservation) = $resultArray;

        $captureResult = 'ok' === $result;

        $response->setIsSuccessful($captureResult);
        $response->setTransactionId($reservation);

        /**
         * If a capture fails, attempt to extend the auth and attempt capture again.
         * This work in certain cases that cannot be detected via api calls
         */
        if (!$response->getIsSuccessful() && !$this->_isRecursiveCall) {
            try {
                $this->_getKlarnaOrderManagement()->extendExpiryDate($orderId);

                $this->_isRecursiveCall = true;
                $response = $this->capture($orderId, $amount);
                $this->_isRecursiveCall = false;

                $this->_debug($this->_getKlarnaOrderManagement());

                return $response;
            } catch (KlarnaException $e) {
                // Ignored
            }
        }

        return $response;
    }

    /**
     * Get request generator
     *
     * @return \Klarna\Kco\Model\Api\Builder\AbstractModel
     * @throws KlarnaException
     */
    protected function _getGenerator()
    {
        return $this->builderFactory->create($this->builderType);
    }

    /**
     * Get Klarna order management
     *
     * @return Klarna
     * @throws \Klarna\XMLRPC\Exception\KlarnaException
     * @throws KlarnaException
     */
    protected function _getKlarnaOrderManagement()
    {
        return $this->_klarnaOrderManagement;
    }

    protected function initKlarnaOrderManagement()
    {
        $locale = $this->configHelper->getLocaleCode();
        $this->_klarnaOrderManagement->config(
            $this->configHelper->getApiConfig('merchant_id', $this->getStore()),
            $this->configHelper->getApiConfig('shared_secret', $this->getStore()),
            $this->directoryHelper->getDefaultCountry($this->getStore()),
            strtok($locale, '_'),
            $this->configHelper->getBaseCurrencyCode($this->getStore()),
            $this->configHelper->getApiConfigFlag('test_mode', $this->getStore()) ? Klarna::BETA : Klarna::LIVE,
            'json',
            'pclasses.json',
            true,
            $this->configHelper->getApiConfigFlag('debug', $this->getStore())
        );
    }

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(StoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Handle capture of items
     *
     * @param Invoice $invoice
     * @param array   $orderItems
     *
     * @return $this
     */
    protected function _captureItems($invoice, $orderItems = [])
    {
        $api = $this->_getKlarnaOrderManagement();

        foreach ($orderItems as $orderItem) {
            $skipObject = new DataObject([
                'skip_item' => in_array($orderItem['reference'], $this->_captureLineItemSkip)
            ]);

            $this->eventManager->dispatch(
                'kco_kred_capture_item_add_art_no',
                [
                    'object'      => $invoice,
                    'invoice'     => $invoice,
                    'order_item'  => $orderItem,
                    'skip_object' => $skipObject
                ]
            );

            if ($skipObject->getSkipItem()) {
                continue;
            }

            $api->addArtNo((int)$orderItem['quantity'], $orderItem['reference']);
        }

        return $this;
    }

    /**
     * Handle capture of shipping
     *
     * @param Invoice $invoice
     * @param array   $orderItems
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _captureShipping($invoice, $orderItems = [])
    {
        if (0 >= $invoice->getBaseShippingAmount()) {
            return $this;
        }

        $api = $this->_getKlarnaOrderManagement();
        if ($invoice->getBaseShippingAmount() == $invoice->getOrder()->getBaseShippingAmount()) {
            $api->addArtNo(1, $invoice->getOrder()->getShippingMethod());
        } else {
            $e = new KlarnaApiException(__('Cannot capture partial shipping amount for order.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * Handle capture of discounts
     *
     * @param Invoice $invoice
     * @param array   $orderItems
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _captureDiscounts($invoice, $orderItems = [])
    {
        if (0 <= $invoice->getBaseDiscountAmount()) {
            return $this;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();
        $invoiceDiscount = $this->configHelper->toApiFloat($invoice->getBaseDiscountAmount());
        $orderDiscount = $this->configHelper->toApiFloat($order->getBaseDiscountAmount());
        if ($invoiceDiscount == $orderDiscount) {
            $this->_getKlarnaOrderManagement()->addArtNo(1, 'discount');
        } else {
            $e = new KlarnaApiException(__('Cannot capture partial discount amount for invoice.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * Refund for an order
     *
     * @param string     $orderId
     * @param float      $amount
     * @param Creditmemo $creditMemo
     * @return DataObject
     * @throws \Exception
     */
    public function refund($orderId, $amount, $creditMemo = null)
    {
        if (!$creditMemo instanceof Creditmemo) {
            $e = new KlarnaException(__('Refund credit memo must be an instance Creditmemo'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        $response = new DataObject;
        $invoiceId = $creditMemo->getInvoice()->getTransactionId();

        try {
            switch ($this->_getRefundType($creditMemo)) {
                case self::REFUND_TYPE_INVOICE:
                    $refundResponse = $this->_getKlarnaOrderManagement()->creditInvoice($invoiceId);
                    break;
                default:
                    $orderItems = $this->_getGenerator()
                                       ->setObject($creditMemo)
                                       ->collectOrderLines($creditMemo->getStore())
                                       ->getOrderLines();

                    $this->eventManager->dispatch(
                        'kco_kred_refund_items_before',
                        [
                            'object'      => $creditMemo,
                            'invoice'     => $creditMemo,
                            'api'         => $this->_getKlarnaOrderManagement(),
                            'order_items' => $orderItems
                        ]
                    );

                    $refundResponse = $this->_refundShipping($creditMemo, $orderItems)
                                           ->_refundAdjustmentNegative($creditMemo)
                                           ->_refundAdjustmentPositive($creditMemo)
                                           ->_refundItems($creditMemo, $orderItems)
                                           ->_refundDiscount($creditMemo, $orderItems)
                                           ->_getKlarnaOrderManagement()->creditPart($invoiceId);

                    $this->eventManager->dispatch(
                        'kco_kred_refund_items_after',
                        [
                            'object'      => $creditMemo,
                            'api'         => $this->_getKlarnaOrderManagement(),
                            'invoice'     => $creditMemo,
                            'order_items' => $orderItems
                        ]
                    );
            }
        } catch (\Exception $e) {
            $this->_debug($this->_getKlarnaOrderManagement());
            throw $e;
        }

        $this->_debug($this->_getKlarnaOrderManagement());

        $refundResult = $invoiceId == $refundResponse;

        $response->setIsSuccessful((bool)$refundResult);

        return $response;
    }

    /**
     * Get the optimal refund type to perform
     *
     * @param Creditmemo $creditMemo
     *
     * @return string
     */
    protected function _getRefundType($creditMemo)
    {
        if (0 >= ($creditMemo->getInvoice()->getBaseGrandTotal() - $creditMemo->getBaseGrandTotal())) {
            return self::REFUND_TYPE_INVOICE;
        }

        return self::REFUND_TYPE_ARTICLE;
    }

    /**
     * Handle refunds of discounts
     *
     * @param Creditmemo $creditmemo
     * @param array      $orderItems
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _refundDiscount(Creditmemo $creditmemo, $orderItems = [])
    {
        if (0 <= $creditmemo->getBaseDiscountAmount()) {
            return $this;
        }

        if ($creditmemo->getBaseDiscountAmount() != $creditmemo->getOrder()->getBaseDiscountAmount()) {
            $e = new KlarnaApiException(__('Cannot refund partial discount amount for order.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        $this->_getKlarnaOrderManagement()->addArtNo(1, 'discount');

        return $this;
    }

    /**
     * Handel refund of items
     *
     * @param Creditmemo $creditMemo
     * @param array      $orderItems
     *
     * @return $this
     * @throws KlarnaException
     */
    protected function _refundItems(Creditmemo $creditMemo, $orderItems = [])
    {
        $api = $this->_getKlarnaOrderManagement();

        foreach ($orderItems as $orderItem) {
            $skipObject = new DataObject([
                'skip_item' => in_array($orderItem['reference'], $this->_refundLineItemSkip)
            ]);

            $this->eventManager->dispatch(
                'kco_kred_refund_item_add_art_no',
                [
                    'object'      => $creditMemo,
                    'credit_memo' => $creditMemo,
                    'order_item'  => $orderItem,
                    'skip_object' => $skipObject
                ]
            );

            if ($skipObject->getSkipItem()) {
                continue;
            }

            $api->addArtNo($orderItem['quantity'], $orderItem['reference']);
        }

        return $this;
    }

    /**
     * Add new article for positive refund adjustment
     *
     * @param Creditmemo $creditMemo
     *
     * @return $this
     */
    protected function _refundAdjustmentPositive(Creditmemo $creditMemo)
    {
        if (0 >= $creditMemo->getAdjustmentPositive()
            || $creditMemo->getAdjustmentPositive() == $creditMemo->getAdjustmentNegative()
        ) {
            return $this;
        }

        $this->_getKlarnaOrderManagement()->addArticle(
            1,
            'adj-pos-' . $creditMemo->getInvoice()->getIncrementId() . '-' . $this->_getRandomString(),
            __('Refund Adjustment Positive')->render(),
            -$creditMemo->getAdjustmentPositive(),
            0
        );

        return $this;
    }

    /**
     * Generate a random 4 letter string
     *
     * @return string
     */
    protected function _getRandomString()
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, 4);
    }

    /**
     * Add new article for negative refund adjustment
     *
     * @param Creditmemo $creditMemo
     *
     * @return $this
     */
    protected function _refundAdjustmentNegative(Creditmemo $creditMemo)
    {
        if (0 >= $creditMemo->getAdjustmentNegative()
            || $creditMemo->getAdjustmentPositive() == $creditMemo->getAdjustmentNegative()
        ) {
            return $this;
        }

        $this->_getKlarnaOrderManagement()->addArticle(
            1,
            'adj-neg-' . $creditMemo->getInvoice()->getIncrementId() . '-' . $this->_getRandomString(),
            __('Refund Adjustment Negative')->render(),
            $creditMemo->getAdjustmentNegative(),
            0
        );

        return $this;
    }

    /**
     * Refund an amount on shipping
     *
     * @param Creditmemo $creditMemo
     * @param array      $orderItems
     *
     * @return $this
     * @throws \Klarna\Core\Exception
     * @throws \Klarna\XMLRPC\Exception\KlarnaException
     */
    protected function _refundShipping(Creditmemo $creditMemo, $orderItems = [])
    {
        if (0 >= $creditMemo->getBaseShippingAmount()) {
            return $this;
        }

        $taxRate = 0;

        foreach ($orderItems as $orderItem) {
            if (isset($orderItem['type'])
                && $orderItem['type'] === Shipping::ITEM_TYPE_SHIPPING
            ) {
                $taxRate = $orderItem['tax_rate'] / 100;
                break;
            }
        }

        if ($creditMemo->getBaseShippingAmount() === $creditMemo->getOrder()->getBaseShippingAmount()) {
            $this->_getKlarnaOrderManagement()
                 ->addArtNo(1, 'shipping');
        } else {
            $this->_getKlarnaOrderManagement()->addArticle(
                1,
                'shipping-refund-' . $this->_getRandomString(),
                __('Refund Shipping')->render(),
                -$creditMemo->getBaseShippingAmount(),
                $taxRate,
                0,
                8
            );
        }

        return $this;
    }

    /**
     * Release the authorization for an order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function release($orderId)
    {
        return $this->cancel($orderId);
    }

    /**
     * Cancel an order
     *
     * @param string $orderId
     * @return DataObject
     * @throws \Exception
     */
    public function cancel($orderId)
    {
        $response = new DataObject;

        try {
            $apiResponse = $this->_getKlarnaOrderManagement()->cancelReservation($orderId);
            $response->setIsSuccessful($apiResponse);
        } catch (\Exception $e) {
            $this->_debug($this->_getKlarnaOrderManagement());
            throw $e;
        }

        $this->_debug($this->_getKlarnaOrderManagement());

        return $response;
    }

    /**
     * Acknowledge an order in order management
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function acknowledgeOrder($orderId)
    {
        $response = new DataObject;
        try {
            $updateRequest = $this->_getCheckoutOrder($orderId);
            $updateRequest->update([
                'status' => 'created'
            ]);

            $this->_debug($updateRequest);
            $response->setIsSuccessful(true);
        } catch (\Klarna_Checkout_ApiErrorException $e) {
            $response->setIsSuccessful(false);
            $response->setError($e->getMessage());
            $response->setExtra($e->getPayload());
        }

        return $response;
    }

    /**
     * Get Klarna checkout order
     *
     * @param string         $checkoutId
     * @param StoreInterface $store
     *
     * @return \Klarna_Checkout_Order
     */
    protected function _getCheckoutOrder($checkoutId = null, $store = null)
    {
        if (null === $store) {
            $store = $this->store;
        }
        if (null === $this->_order) {
            $this->_order = new \Klarna_Checkout_Order($this->_getCheckoutConnector($store), $checkoutId);
        }

        return $this->_order;
    }

    /**
     * Get the checkout connection
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return \Klarna_Checkout_Connector
     * @throws \Klarna_Checkout_Exception
     */
    protected function _getCheckoutConnector($store = null)
    {
        if (null === $this->_connector) {
            $versionConfig = $this->configHelper->getVersionConfig($store);
            $url = $versionConfig['production_url'];
            if ($this->configHelper->getApiConfigFlag('test_mode', $store)) {
                $url = $versionConfig['testdrive_url'];
            }

            $userAgent = new \Klarna_Checkout_UserAgent();
            $userAgent->addField(
                'Magento',
                [
                    'name'    => $this->versionInfo->getMageEdition(),
                    'version' => $this->versionInfo->getMageVersion()
                ]
            );
            $userAgent->addField(
                'MAGE_MODE',
                [
                    'name'    => ucwords($this->versionInfo->getMageMode()),
                    'version' => 'Mode'
                ]
            );
            $userAgent->addField(
                'KcoModule',
                [
                    'name'    => 'core',
                    'version' => $this->versionInfo->getVersion('klarna/module-kco')
                ]
            );
            $userAgent->addField(
                'KredModule',
                [
                    'name'    => 'addon',
                    'version' => $this->versionInfo->getVersion('klarna/module-kred')
                ]
            );
            $userAgent->addField(
                'OMModule',
                [
                    'name'    => 'addon',
                    'version' => $this->versionInfo->getVersion('klarna/module-om')
                ]
            );
            $this->_connector = Connector::create(
                $this->configHelper->getApiConfig('shared_secret', $store),
                $url,
                $userAgent
            );
        }

        return $this->_connector;
    }

    public function getConfig()
    {
        return $this->configHelper->getVersionConfig($this->store);
    }

    /**
     * Update merchant references for a Klarna order
     *
     * @param string $orderId
     * @param string $reference1
     * @param string $reference2
     *
     * @return DataObject
     */
    public function updateMerchantReferences($orderId, $reference1, $reference2 = null)
    {
        $response = new DataObject;
        $merchantReference = [
            'orderid1' => $reference1
        ];

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (!empty($reference2)) {
            $merchantReference['orderid2'] = $reference2;
        }

        try {
            $updateRequest = $this->_getCheckoutOrder($orderId);
            $updateRequest->update([
                'merchant_reference' => $merchantReference
            ]);

            $this->_debug($updateRequest);
            $response->setIsSuccessful(true);
        } catch (\Klarna_Checkout_ApiErrorException $e) {
            $response->setIsSuccessful(false);
            $response->setError($e->getMessage());
            $response->setExtra($e->getPayload());
        }

        return $response;
    }

    /**
     * Get the fraud status of an order to determine if it should be accepted or denied within Magento
     *
     * Return value of 1 means accept
     * Return value of 0 means still pending
     * Return value of -1 means deny
     *
     * @param string $orderId
     *
     * @return int
     */
    public function getFraudStatus($orderId)
    {
        $klarnaOrder = $this->orderRepository->getByReservationId($orderId);
        $pushQueue = $this->pushqueueRepository->getByCheckoutId($klarnaOrder->getKlarnaCheckoutId());

        return $pushQueue->getId() ? 1 : 0;
    }

    /**
     * Get order details for a completed Klarna order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function getPlacedKlarnaOrder($orderId)
    {
        $response = $this->_getCheckoutOrder($orderId);
        $response->fetch();
        $response = new DataObject($response->marshal());
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function resetForStore($store, $methodCode)
    {
        // Intentionally do nothing
        $this->store = $store;
        $this->initKlarnaOrderManagement();
    }

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId()
    {
        return $this->getKlarnaOrder()->getOrderId();
    }

    /**
     * Get Klarna checkout order details
     *
     * @return DataObject
     */
    public function getKlarnaOrder()
    {
        if ($this->klarnaOrder === null) {
            $this->klarnaOrder = new DataObject();
        }

        return $this->klarnaOrder;
    }

    /**
     * Set Klarna checkout order details
     *
     * @param DataObject $klarnaOrder
     *
     * @return $this
     */
    public function setKlarnaOrder(DataObject $klarnaOrder)
    {
        $this->klarnaOrder = $klarnaOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBuilderType($builderType)
    {
        $this->builderType = $builderType;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptures($id)
    {
        $captures = $this->getPlacedKlarnaOrder($id)->getCaptures();
        $response = [];
        foreach ($captures as $capture) {
            $response[$capture['klarna_reference']] = new DataObject($capture);
        }
        return $response;
    }
}
