<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel;

class Stat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('amasty_file_stat', 'id');
    }

    public function truncate()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
