<?php
/**
 * This file is part of the Klarna Kco module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Gateway\Command;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;

class Initialize extends DataObject implements CommandInterface
{
    const TYPE_AUTH = 'authorization';

    /**
     * @var Kco
     */
    protected $kco;

    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * Initialize constructor.
     *
     * @param Kco          $kco
     * @param ConfigHelper $helper
     * @param array        $data
     */
    public function __construct(Kco $kco, ConfigHelper $helper, array $data = [])
    {

        parent::__construct($data);
        $this->kco = $kco;
        $this->helper = $helper;
    }

    /**
     * Initialize command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $commandSubject['payment']->getPayment();
        /** @var DataObject $stateObject */
        $stateObject = $commandSubject['stateObject'];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        /** @var \Magento\Store\Model\Store $store */
        $store = $order->getStore();
        $message = __('Pending authorization');
        if (0 >= $order->getGrandTotal()) {
            $stateObject->setState(Order::STATE_NEW);
        } elseif ($this->helper->getVersionConfig($store)->getPaymentReview()) {
            $stateObject->setStatus('pending_payment');
            $stateObject->setState(Order::STATE_PAYMENT_REVIEW);
        } else {
            $message = __('Authorized payment');
            $stateObject->setStatus($this->helper->getProcessedOrderStatus($store));
            $stateObject->setState(Order::STATE_PROCESSING);
        }

        $stateObject->setIsNotified(false);

        $transactionId = $this->kco->getApiInstance($store)->getReservationId();

        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setTransactionId($transactionId)->setIsTransactionClosed(0);
        $payment->addTransaction(self::TYPE_AUTH);

        return null;
    }
}
