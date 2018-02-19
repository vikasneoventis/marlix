<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Model;

use \Magento\Framework\Model\AbstractModel;
use Yosto\ImageProductSlide\Helper\Constant;

/**
 * Class GalleryValue
 * @package Yosto\ImageProductSlide\Model
 */
class GalleryValue extends AbstractModel
{
    public function _construct()
    {
        $this->_init(Constant::GALLERY_VALUE_RESOURCE_MODEL);
    }
}