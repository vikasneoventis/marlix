<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\AdvancedReport\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Revenue
 * @package Yosto\AdvancedReport\Model\ResourceModel
 */
class Revenue extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_created','id');
    }
}