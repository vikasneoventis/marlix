<?php
namespace Amasty\Checkout\Model\ResourceModel\Field;

class Store extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amcheckout_field_store', 'id');
    }
}
