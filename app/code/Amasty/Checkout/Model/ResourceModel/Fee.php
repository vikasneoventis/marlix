<?php
namespace Amasty\Checkout\Model\ResourceModel;

class Fee extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amcheckout_additional_fee', 'id');
    }
}
