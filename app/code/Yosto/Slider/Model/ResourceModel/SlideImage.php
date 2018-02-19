<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Yosto\Slider\Helper\Constant;
/**
 * Class SlideImage
 * @package Yosto\Slider\Model\ResourceModel
 */
class SlideImage extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(Constant::SLIDE_IMAGE_TABLE,Constant::SLIDE_IMAGE_TABLE_ID);
    }

}