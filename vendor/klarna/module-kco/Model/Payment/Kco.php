<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Payment;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Helper\Checkout as CheckoutHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Klarna Payment
 */
class Kco implements MethodInterface
{
    const METHOD_CODE = 'klarna_kco';

    const ACTION_ORDER             = 'order';
    const ACTION_AUTHORIZE         = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Kco constructor.
     *
     * @param Adapter        $adapter
     * @param CheckoutHelper $checkoutHelper
     * @param ConfigHelper   $configHelper
     * @param EventManager   $eventManager
     */
    public function __construct(
        Adapter $adapter,
        CheckoutHelper $checkoutHelper,
        ConfigHelper $configHelper,
        EventManager $eventManager
    ) {
        $this->adapter = $adapter;
        $this->checkoutHelper = $checkoutHelper;
        $this->configHelper = $configHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->checkoutHelper->kcoEnabled($storeId);
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->adapter->getCode();
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return $this->adapter->getFormBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->adapter->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function setStore($storeId)
    {
        $this->adapter->setStore($storeId);
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
        return $this->adapter->getStore();
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        return $this->adapter->canOrder();
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->adapter->canAuthorize();
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->adapter->canCapture();
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getInfoInstance()->getOrder();

        if ($order && $order->getId()) {
            $canInvoicePartialObject = new DataObject([
                'can_partial' => $this->configHelper->getPartialPaymentSupport($order->getStore())
            ]);

            $checkoutType = $this->configHelper->getCheckoutType($order->getStore());
            $eventData    = [
                'flag_object' => $canInvoicePartialObject,
                'order'       => $order
            ];

            $this->eventManager->dispatch('kco_payment_can_capture_partial_per_invoice', $eventData);
            $this->eventManager->dispatch("kco_payment_type_{$checkoutType}_can_capture_partial_per_invoice", $eventData);

            return $canInvoicePartialObject->getCanPartial();
        }

        return $this->adapter->canCapturePartial();
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return $this->adapter->canCaptureOnce();
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->adapter->canRefund();
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getInfoInstance()->getOrder();

        if ($order && $order->getId()) {
            $canInvoicePartialObject = new DataObject([
                'can_partial' => $this->configHelper->getPartialPaymentSupport($order->getStore())
            ]);

            $checkoutType = $this->configHelper->getCheckoutType($order->getStore());
            $eventData    = [
                'flag_object' => $canInvoicePartialObject,
                'order'       => $order
            ];

            $this->eventManager->dispatch('kco_payment_can_refund_partial_per_invoice', $eventData);
            $this->eventManager->dispatch("kco_payment_type_{$checkoutType}_can_refund_partial_per_invoice", $eventData);

            return $canInvoicePartialObject->getCanPartial();
        }

        return $this->adapter->canRefundPartialPerInvoice();
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return $this->adapter->canVoid();
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return $this->adapter->canUseInternal();
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return $this->adapter->canUseCheckout();
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return $this->adapter->canEdit();
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return $this->adapter->canFetchTransactionInfo();
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->adapter->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return $this->adapter->isGateway();
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return $this->adapter->isOffline();
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return $this->adapter->isInitializeNeeded();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        return $this->adapter->canUseForCountry($country);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->adapter->canUseForCurrency($currencyCode);
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return $this->adapter->getInfoBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->adapter->getInfoInstance();
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->adapter->setInfoInstance($info);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        $this->adapter->validate();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function order(InfoInterface $payment, $amount)
    {
        $this->adapter->order($payment, $amount);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->adapter->authorize($payment, $amount);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->adapter->capture($payment, $amount);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->adapter->refund($payment, $amount);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancel(InfoInterface $payment)
    {
        $this->adapter->cancel($payment);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function void(InfoInterface $payment)
    {
        $this->adapter->void($payment);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return $this->adapter->canReviewPayment();
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->adapter->acceptPayment($payment);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        $this->adapter->denyPayment($payment);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->adapter->getConfigData($field, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function assignData(DataObject $data)
    {
        $this->adapter->assignData($data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $store = null;
        if ($quote !== null) {
            $store = $quote->getStore();
        }
        $available = $this->adapter->isAvailable($quote);
        if (!$this->checkoutHelper->kcoEnabled($store)) {
            return $available;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->adapter->initialize($paymentAction, $stateObject);
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return $this->adapter->getConfigPaymentAction();
    }
}
