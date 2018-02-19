<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\ResourceModel;

use Yosto\Slider\Helper\Constant;
/**
 * Class Type
 * @package Yosto\Slider\Model\ResourceModel
 */
class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(Constant::TYPE_TABLE,Constant::TYPE_TABLE_ID);
    }

}