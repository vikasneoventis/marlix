<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\ResourceModel\Image;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Yosto\Slider\Helper\Constant;
/**
 * Class Collection
 * @package Yosto\Slider\Model\ResourceModel\Image
 */
class Collection extends  AbstractCollection
{
    public function _construct(){
        $this->_init(Constant::IMAGE_MODEL,Constant::IMAGE_RESOURCE_MODEL);
    }
}