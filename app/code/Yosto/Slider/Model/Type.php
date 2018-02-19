<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model;

use Magento\Framework\Model\AbstractModel;
use Yosto\Slider\Helper\Constant;
/**
 * Class Type
 * @package Yosto\Slider\Model
 */
class Type extends AbstractModel
{
    protected function _construct()
    {
       $this->_init(Constant::TYPE_RESOURCE_MODEL);
    }

}