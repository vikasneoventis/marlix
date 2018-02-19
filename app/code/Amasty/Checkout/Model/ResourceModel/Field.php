<?php
namespace Amasty\Checkout\Model\ResourceModel;

class Field extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amcheckout_field', 'id');
    }
}
