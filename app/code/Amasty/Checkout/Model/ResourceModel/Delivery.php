<?php
namespace Amasty\Checkout\Model\ResourceModel;

class Delivery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amcheckout_delivery', 'id');
    }
}
