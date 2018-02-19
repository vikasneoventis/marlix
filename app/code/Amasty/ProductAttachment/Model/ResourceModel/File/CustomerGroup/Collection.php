<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\File\CustomerGroup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\ProductAttachment\Model\File\CustomerGroup',
            'Amasty\ProductAttachment\Model\ResourceModel\File\CustomerGroup'
        );
    }
}