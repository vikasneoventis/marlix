<?php

namespace Amasty\Checkout\Model\ResourceModel\Field\Store;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('\Amasty\Checkout\Model\Field\Store', '\Amasty\Checkout\Model\ResourceModel\Field\Store');
    }
}
