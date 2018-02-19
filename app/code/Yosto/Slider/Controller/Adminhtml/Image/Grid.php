<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class Grid
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class Grid extends Image
{
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}