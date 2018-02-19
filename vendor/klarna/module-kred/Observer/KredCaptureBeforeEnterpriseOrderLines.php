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

use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;

class KredCaptureBeforeEnterpriseOrderLines implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * KredCaptureBeforeEnterpriseOrderLines constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getObject();

        /** @var \Klarna\Core\Api\ApiInterface $api */
        $api = $observer->getApi();
        try {
            $this->_captureCustomerbalance($invoice, $api)
                 ->_captureGiftcard($invoice, $api)
                 ->_captureReward($invoice, $api);
        } catch (KlarnaApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }
    }

    /**
     * Capture enterprise order line reward
     *
     * @param Invoice                       $invoice
     * @param \Klarna\Core\Api\ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _captureReward(Invoice $invoice, $api)
    {
        if (0 >= abs($invoice->getBaseRewardCurrencyAmount())) {
            return $this;
        }

        /** @var OrderInterface $order */
        $order = $invoice->getOrder();
        // Round numbers to deal with floating point math issues
        $invoiceAmt = round($invoice->getBaseRewardCurrencyAmount(), 2);
        $orderAmt = round($order->getBaseRewardCurrencyAmount(), 2);
        if ($invoiceAmt != $orderAmt) {
            throw new KlarnaApiException(__('Cannot capture partial reward amount for invoice.'));
        }
        $api->addArtNo(1, 'reward');

        return $this;
    }

    /**
     * Capture enterprise order line giftcard
     *
     * @param Invoice                       $invoice
     * @param \Klarna\Core\Api\ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _captureGiftcard(Invoice $invoice, $api)
    {
        if (0 >= abs($invoice->getBaseGiftCardsAmount())) {
            return $this;
        }

        /** @var OrderInterface $order */
        $order = $invoice->getOrder();
        // Round numbers to deal with floating point math issues
        $invoiceAmt = round($invoice->getBaseGiftCardsAmount(), 2);
        $orderAmt = round($order->getBaseGiftCardsAmount(), 2);
        if ($invoiceAmt != $orderAmt) {
            throw new KlarnaApiException(__('Cannot capture partial gift card amount for invoice.'));
        }
        $api->addArtNo(1, 'giftcardaccount');

        return $this;
    }

    /**
     * Capture enterprise order line customer balance
     *
     * @param Invoice                       $invoice
     * @param \Klarna\Core\Api\ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _captureCustomerbalance($invoice, $api)
    {
        if (0 >= abs($invoice->getCustomerBalanceAmount())) {
            return $this;
        }

        /** @var OrderInterface $order */
        $order = $invoice->getOrder();
        // Round numbers to deal with floating point math issues
        $invoiceAmt = round($invoice->getCustomerBalanceAmount(), 2);
        $orderAmt = round($order->getCustomerBalanceAmount(), 2);
        if ($invoiceAmt != $orderAmt) {
            throw new KlarnaApiException(__('Cannot capture partial store credit amount for invoice.'));
        }
        $api->addArtNo(1, 'customerbalance');

        return $this;
    }
}
