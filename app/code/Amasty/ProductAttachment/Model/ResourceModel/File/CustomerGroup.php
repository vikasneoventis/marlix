<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\File;

class CustomerGroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_file_customer_group', 'id');
    }
}
