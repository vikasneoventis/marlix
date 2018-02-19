<?php

namespace Amasty\Checkout\Model\ResourceModel\Delivery;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Checkout\Model\Delivery', 'Amasty\Checkout\Model\ResourceModel\Delivery');
    }
}
