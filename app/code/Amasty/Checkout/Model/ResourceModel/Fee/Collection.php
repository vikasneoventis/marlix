<?php

namespace Amasty\Checkout\Model\ResourceModel\Fee;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Checkout\Model\Fee', 'Amasty\Checkout\Model\ResourceModel\Fee');
    }
}
