<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Gateway\Command;

use Klarna\Core\Exception as KlarnaException;
use Magento\Payment\Gateway\Command;

class Capture extends AbstractCommand
{
    /**
     * Capture command
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
        $amount = $commandSubject['amount'];
        $klarnaOrder = $this->getKlarnaOrder($payment->getOrder());

        if (!$klarnaOrder->getId() || !$klarnaOrder->getReservationId()) {
            $e = new KlarnaException(__('Unable to capture payment for this order.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        /** @var \Magento\Store\Model\Store $store */
        $response = $this->getOmApi($order)
                         ->capture($klarnaOrder->getReservationId(), $amount, $payment->getInvoice());

        if (!$response->getIsSuccessful()) {
            if ($response->getErrorCode() === 'CAPTURE_NOT_ALLOWED') {
                //TODO: Implement https://developers.klarna.com/api/#get-all-captures-for-one-order
                $e = new KlarnaException(__('Payment capture not allowed.'));
                $this->messageManager->addErrorMessage($e->getMessage());
                throw $e;
            }
            $e = new KlarnaException(__('Payment capture failed, please try again.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }

        if ($response->getTransactionId()) {
            $payment->setTransactionId($response->getTransactionId());
        }
        return null;
    }
}
