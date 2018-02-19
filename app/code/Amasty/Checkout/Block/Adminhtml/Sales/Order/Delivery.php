<?php
namespace Amasty\Checkout\Block\Adminhtml\Sales\Order;

class Delivery extends \Amasty\Checkout\Block\Sales\Order\Info\Delivery
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Amasty_Checkout::sales/order/delivery.phtml');
    }
}
