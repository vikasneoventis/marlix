<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Block\Info;

use Klarna\Core\Model\OrderRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

class Klarna extends \Magento\Payment\Block\Info
{
    /**
     * Klarna Order Repository
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Klarna constructor.
     *
     * @param Context         $context
     * @param OrderRepository $orderRepository
     * @param array           $data
     */
    public function __construct(Context $context, OrderRepository $orderRepository, array $data = [])
    {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->_template = 'Klarna_Core::payment/info.phtml';
    }

    /**
     * Prepare information for payment
     *
     * @param DataObject|array $transport
     *
     * @return DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $info = $this->getInfo();
        $klarnaReferenceId = $info->getAdditionalInformation('klarna_reference');
        $order = $info->getOrder();
        try {
            $klarnaOrder = $this->orderRepository->getByOrder($order);

            if ($klarnaOrder->getId() && $klarnaOrder->getKlarnaOrderId()) {
                $transport->setData((string)__('Order ID'), $klarnaOrder->getKlarnaOrderId());

                if ($klarnaOrder->getReservationId()
                    && $klarnaOrder->getReservationId() != $klarnaOrder->getKlarnaOrderId()
                ) {
                    $transport->setData((string)__('Reservation'), $klarnaOrder->getReservationId());
                }
            }
        } catch (NoSuchEntityException $e) {
            $transport->setData((string)__('Error'), $e->getMessage());
        }

        if ($klarnaReferenceId) {
            $transport->setData((string)__('Reference'), $klarnaReferenceId);
        }

        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            if ($invoice->getTransactionId()) {
                $invoiceKey = (string)__('Invoice ID (#%1)', $invoice->getIncrementId());
                $transport->setData($invoiceKey, $invoice->getTransactionId());
            }
        }

        return $transport;
    }
}
