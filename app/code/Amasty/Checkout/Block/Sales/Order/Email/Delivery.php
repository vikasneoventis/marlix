<?php
namespace Amasty\Checkout\Block\Sales\Order\Email;

class Delivery extends \Amasty\Checkout\Block\Sales\Order\Info\Delivery
{
    protected function _construct()
    {
        parent::_construct();

        $this
            ->setTemplate('Amasty_Checkout::sales/order/email/delivery.phtml')
            ->setData('area', 'frontend')
        ;
    }
}

