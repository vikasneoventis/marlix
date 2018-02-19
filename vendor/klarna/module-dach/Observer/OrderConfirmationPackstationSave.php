<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Observer;

use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Address;

class OrderConfirmationPackstationSave implements ObserverInterface
{
    /**
     * @var Kco
     */
    protected $kco;

    /**
     * OrderConfirmationPackstationSave constructor.
     *
     * @param Kco $kco
     */
    public function __construct(Kco $kco)
    {
        $this->kco = $kco;
    }

    /**
     * Save care of on an order with packstation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $checkout = $observer->getCheckout();
        if (!$observer->getQuote()->isVirtual() && ($careOf = $checkout->getData('shipping_address/care_of'))) {
            $shippingAddress = new DataObject($checkout->getShippingAddress());
            $shippingAddress->setStreetAddress2(__('C/O ') . $careOf);
            $this->kco->updateCheckoutAddress($shippingAddress, Address::TYPE_SHIPPING);
        }
    }
}
