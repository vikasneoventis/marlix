<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\ResourceModel\SlideImage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Yosto\Slider\Helper\Constant;
/**
 * Class Collection
 * @package Yosto\Slider\Model\ResourceModel\SlideImage
 */
class Collection extends AbstractCollection
{
    public function _construct(){
        $this->_init(Constant::SLIDE_IMAGE_MODEL,Constant::SLIDE_IMAGE_RESOURCE_MODEL);
    }
}