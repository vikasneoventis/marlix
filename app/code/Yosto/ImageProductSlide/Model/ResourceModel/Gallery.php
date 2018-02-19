<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Model\ResourceModel;

use Yosto\ImageProductSlide\Helper\Constant;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Gallery
 * @package Yosto\ImageProductSlide\Model\ResourceModel
 */
class Gallery extends AbstractDb
{
    protected function _construct()
    {
        $this->_init
        (
            Constant::GALLERY_TABLE,
            Constant::GALLERY_TABLE_VALUE_ID
        );
    }
}