<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\AdvancedReport\Model;


use Magento\Framework\Model\AbstractModel;

/**
 * Class Revenue
 * @package Yosto\AdvancedReport\Model
 */
class Revenue extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Yosto\AdvancedReport\ResourceModel\Revenue');
    }

}