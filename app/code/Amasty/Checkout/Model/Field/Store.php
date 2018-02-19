<?php
namespace Amasty\Checkout\Model\Field;

use Magento\Framework\Model\AbstractModel;

class Store extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\Checkout\Model\ResourceModel\Field\Store');
    }
}
