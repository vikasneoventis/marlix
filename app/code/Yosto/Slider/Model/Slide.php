<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model;
use Magento\Framework\Model\AbstractModel;
use Yosto\Slider\Helper\Constant;

/**
 * Class Slide
 * @package Yosto\Slider\Model
 */
class Slide extends AbstractModel
{
        public function _construct(){
            $this->_init(Constant::SLIDE_RESOURCE_MODEL);
        }

}