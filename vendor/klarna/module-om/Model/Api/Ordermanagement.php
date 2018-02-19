<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Model\Api;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\Api\BuilderFactory;
use Klarna\Ordermanagement\Api\ApiInterface;
use Klarna\Ordermanagement\Model\Api\Rest\Service\Ordermanagement as OrdermanagementApi;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Ordermanagement
 *
 * @package Klarna\Ordermanagement\Model\Api
 */
class Ordermanagement implements ApiInterface
{
    /**
     * Order fraud statuses
     */
    const ORDER_FRAUD_STATUS_ACCEPTED = 'ACCEPTED';
    const ORDER_FRAUD_STATUS_REJECTED = 'REJECTED';
    const ORDER_FRAUD_STATUS_PENDING  = 'PENDING';

    const RET_ORDER_FRAUD_STATUS_ACCEPTED = 1;
    const RET_ORDER_FRAUD_STATUS_REJECTED = -1;
    const RET_ORDER_FRAUD_STATUS_PENDING  = 0;

    /**
     * Order notification statuses
     */
    const ORDER_NOTIFICATION_FRAUD_REJECTED = 'FRAUD_RISK_REJECTED';
    const ORDER_NOTIFICATION_FRAUD_ACCEPTED = 'FRAUD_RISK_ACCEPTED';

    /**
     * @var DataObject
     */
    protected $klarnaOrder;

    /**
     * @var OrdermanagementApi
     */
    protected $om;

    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var bool
     */
    protected $isRecursiveCall;

    /**
     * API type code
     *
     * @var string
     */
    protected $builderType = '';

    /**
     * OrdermanagementApi constructor.
     *
     * @param OrdermanagementApi $om
     * @param ConfigHelper       $helper
     * @param BuilderFactory     $builderFactory
     * @param ManagerInterface   $eventManager
     * @param string             $builderType
     * @internal param BuilderInterface $builder
     */
    public function __construct(
        OrdermanagementApi $om,
        ConfigHelper $helper,
        BuilderFactory $builderFactory,
        ManagerInterface $eventManager,
        $builderType = ''
    ) {
        $this->om = $om;
        $this->helper = $helper;
        $this->builderFactory = $builderFactory;
        $this->eventManager = $eventManager;
        $this->builderType = $builderType;
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
        $klarnaOrder = $this->om->getOrder($orderId);
        $klarnaOrder = new DataObject($klarnaOrder);
        switch ($klarnaOrder->getFraudStatus()) {
            case self::ORDER_FRAUD_STATUS_ACCEPTED:
                return self::RET_ORDER_FRAUD_STATUS_ACCEPTED;
            case self::ORDER_FRAUD_STATUS_REJECTED:
                return self::RET_ORDER_FRAUD_STATUS_REJECTED;
            case self::ORDER_FRAUD_STATUS_PENDING:
            default:
                return self::RET_ORDER_FRAUD_STATUS_PENDING;
        }
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
        $response = $this->om->acknowledgeOrder($orderId);
        $response = new DataObject($response);
        return $response;
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
        $response = $this->om->updateMerchantReferences($orderId, $reference1, $reference2);
        $response = new DataObject($response);
        return $response;
    }

    /**
     * Capture an amount on an order
     *
     * @param string  $orderId
     * @param float   $amount
     * @param Invoice $invoice
     *
     * @return DataObject
     * @throws \Klarna\Core\Exception
     */
    public function capture($orderId, $amount, $invoice = null)
    {
        $data['captured_amount'] = $this->helper->toApiFloat($amount);

        /**
         * Get items for capture
         */
        if ($invoice instanceof Invoice) {
            $orderItems = $this->_getGenerator()
                               ->setObject($invoice)
                               ->collectOrderLines($invoice->getStore())
                               ->getOrderLines(true);

            if ($orderItems) {
                $data['order_lines'] = $orderItems;
            }
        }

        /**
         * Set shipping delay for capture
         *
         * Change this setting when items will not be shipped for x amount of days after capture.
         *
         * For instance, you capture on Friday but won't ship until Monday. A 3 day shipping delay would be set.
         */
        $shippingDelayObject = new DataObject(['shipping_delay' => 0]);

        $this->eventManager->dispatch(
            'klarna_capture_shipping_delay',
            ['shipping_delay_object' => $shippingDelayObject]
        );

        if ($shippingDelayObject->getShippingDelay()) {
            $data['shipping_delay'] = $shippingDelayObject->getShippingDelay();
        }

        $response = $this->om->captureOrder($orderId, $data);
        $response = new DataObject($response);

        /**
         * If a capture fails, attempt to extend the auth and attempt capture again.
         * This work in certain cases that cannot be detected via api calls
         */
        if (!$this->isRecursiveCall && !$response->getIsSuccessful()) {
            $extendResponse = $this->om->extendAuthorization($orderId);
            $extendResponse = new DataObject($extendResponse);

            if ($extendResponse->getIsSuccessful()) {
                $this->isRecursiveCall = true;
                $response = $this->capture($orderId, $amount);
                $this->isRecursiveCall = false;

                return $response;
            }
        }

        if ($response->getIsSuccessful()) {
            $captureId = $this->om
                ->getLocationResourceId($response->getResponseObject()->getHeader('Location'));

            if ($captureId) {
                $captureDetails = $this->om->getCapture($orderId, $captureId);
                $captureDetails = new DataObject($captureDetails);

                if ($captureDetails->getKlarnaReference()) {
                    $captureDetails->setTransactionId($captureDetails->getKlarnaReference());

                    return $captureDetails;
                }
            }
        }

        return $response;
    }

    protected function _getGenerator()
    {
        return $this->builderFactory->create($this->builderType);
    }

    /**
     * Refund for an order
     *
     * @param string     $orderId
     * @param float      $amount
     * @param Creditmemo $creditMemo
     *
     * @return DataObject
     * @throws \Klarna\Core\Exception
     */
    public function refund($orderId, $amount, $creditMemo = null)
    {
        $data['refunded_amount'] = $this->helper->toApiFloat($amount);

        /**
         * Get items for refund
         */
        if ($creditMemo instanceof Creditmemo) {
            $orderItems = $this->_getGenerator()
                               ->setObject($creditMemo)
                               ->collectOrderLines($creditMemo->getStore())
                               ->getOrderLines(true);

            if ($orderItems) {
                $data['order_lines'] = $orderItems;
            }
        }

        $response = $this->om->refund($orderId, $data);
        $response = new DataObject($response);

        $response->setTransactionId($this->om->getLocationResourceId($response));

        return $response;
    }

    /**
     * Cancel an order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function cancel($orderId)
    {
        $response = $this->om->cancelOrder($orderId);
        $response = new DataObject($response);
        return $response;
    }

    /**
     * Release the authorization on an order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function release($orderId)
    {
        $response = $this->om->releaseAuthorization($orderId);
        $response = new DataObject($response);
        return $response;
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
        $response = $this->om->getOrder($orderId);
        $response = new DataObject($response);
        return $response;
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
    public function resetForStore($store, $methodCode)
    {
        $versionConfig = $this->helper->getVersionConfig($store);
        $this->setBuilderType($this->helper->getOmBuilderType($versionConfig, $methodCode));
        $user = $this->helper->getApiConfig('merchant_id', $store);
        $password = $this->helper->getApiConfig('shared_secret', $store);
        $url = $versionConfig['production_url'];
        if ($this->helper->getApiConfigFlag('test_mode', $store)) {
            $url = $versionConfig['testdrive_url'];
        }
        $this->om->resetForStore($user, $password, $url);
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
}
