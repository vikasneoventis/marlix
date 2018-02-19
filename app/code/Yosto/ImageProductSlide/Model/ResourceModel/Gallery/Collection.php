<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Model\ResourceModel\Gallery;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Yosto\ImageProductSlide\Helper\Constant;

/**
 * Class Collection
 * @package Yosto\ImageProductSlide\Model\ResourceModel\Gallery
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            Constant::GALLERY_MODEL,
            Constant::GALLERY_RESOURCE_MODEL
        );
    }
}