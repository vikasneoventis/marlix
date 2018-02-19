<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Controller\Klarna;

use Klarna\Core\Api\OrderInterface;
use Klarna\Core\Api\OrderRepositoryInterface;
use Klarna\Kco\Api\QuoteInterface;
use Klarna\Kco\Api\QuoteRepositoryInterface;
use Psr\Log\LogLevel;

class Confirmation extends Action
{

    public function execute()
    {
        $checkoutId = $this->getRequest()->getParam('id');

        $this->log('Confirmation called for order ' . $checkoutId);

        if (!$checkoutId) {
            $this->messageManager->addErrorMessage(__('How did you get here?'));
            return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
        }

        $klarnaQuote = $this->getKlarnaQuote($checkoutId);
        $quote = $this->quoteRepository->get($klarnaQuote->getQuoteId());
        $order = null;

        $this->getKco()->setKlarnaQuote($klarnaQuote);

        if (!$quote->getId()) {
            $this->messageManager->addErrorMessage(__('Unable to process order. Please try again'));
            return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
        }

        $reservationId = null;

        try {
            /** @var \Klarna\Core\Model\Order $klarnaOrder */
            $klarnaOrder = $this->getKlarnaOrder($checkoutId);
            if ($klarnaOrder->getId()) {
                $this->messageManager->addErrorMessage(__('Order already exist.'));
                return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
            }

            $checkout = $this->getKco()->setQuote($quote)->getKlarnaCheckout();
            $reservationId = $this->getKco()->getApiInstance($quote->getStore())->getReservationId();

            // Check if checkout is complete before placing the order
            if ($checkout->getStatus() !== 'checkout_complete' && $checkout->getStatus() !== 'created') {
                $this->messageManager->addErrorMessage(__('Unable to process order. Please try again'));
                return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
            }

            // Make sure Magento Addresses match Klarna
            $this->_updateOrderAddresses($checkout);

            $quote->collectTotals();

            // Validate order totals
            $this->validateOrderTotal($checkout, $quote);

            $this->_eventManager->dispatch(
                'kco_confirmation_create_order_before',
                [
                    'quote'           => $quote,
                    'checkout'        => $checkout,
                    'klarna_order_id' => $checkoutId,
                ]
            );

            $order = $this->getKco()->setQuote($quote)->saveOrder();

            $this->_eventManager->dispatch(
                'kco_confirmation_create_order_after',
                [
                    'quote'           => $quote,
                    'order'           => $order,
                    'klarna_order_id' => $checkoutId,
                ]
            );

            $klarnaOrder->setData(
                [
                    'klarna_order_id' => $checkoutId,
                    'reservation_id'  => $reservationId,
                    'order_id'        => $order->getId()
                ]
            );
            $this->saveKlarnaOrder($klarnaOrder);

            try {
                /**
                 * a flag to set that there will be redirect to third party after confirmation
                 */
                $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
                /**
                 * we only want to send to customer about new order when there is no redirect to third party
                 */
                if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                    /** @var \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender */
                    $orderSender = $this->_objectManager->get('Magento\Sales\Model\Order\Email\Sender\OrderSender');
                    $orderSender->send($order);
                }
            } catch (\Exception $e) {
                // We don't want to cancel the order at this point, only just log the error
                $this->logger->critical($e);
            }
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            $this->_eventManager->dispatch(
                'kco_confirmation_failed',
                [
                    'order'           => $order,
                    'quote'           => $quote,
                    'method_code'     => 'klarna_kco',
                    'klarna_order_id' => $checkoutId,
                    'reason'          => $e->getMessage()
                ]
            );

            $this->messageManager->addErrorMessage(__('Unable to complete order. Please try again'));

            return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
        }

        return $this->resultRedirectFactory->create()->setPath('checkout/klarna/success');
    }

    /**
     * Get klarna quote
     *
     * @param string $checkoutId
     * @return QuoteInterface
     */
    protected function getKlarnaQuote($checkoutId)
    {
        return $this->_objectManager->get(QuoteRepositoryInterface::class)->getByCheckoutId($checkoutId);
    }

    /**
     * Get klarna order
     *
     * @param string $checkoutId
     * @return OrderInterface
     */
    protected function getKlarnaOrder($checkoutId)
    {
        return $this->_objectManager->get(OrderRepositoryInterface::class)->getByKlarnaOrderId($checkoutId);
    }

    /**
     * Save klarna order
     *
     * @param OrderInterface $klarnaOrder
     * @return OrderInterface
     */
    protected function saveKlarnaOrder(OrderInterface $klarnaOrder)
    {
        return $this->_objectManager->get(OrderRepositoryInterface::class)->save($klarnaOrder);
    }
}
