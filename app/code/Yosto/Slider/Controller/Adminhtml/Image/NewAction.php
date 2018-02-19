<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class NewAction
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class NewAction extends Image
{
    public function execute()
    {
        $this->_forward('edit');
    }
}