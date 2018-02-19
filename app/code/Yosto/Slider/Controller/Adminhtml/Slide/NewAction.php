<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;
use Yosto\Slider\Controller\Adminhtml\Slide;

/**
 * Class NewAction
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class NewAction extends Slide
{
    public function execute(){
        $this->_forward('edit');
    }
}