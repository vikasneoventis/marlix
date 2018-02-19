<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;

use Yosto\Slider\Controller\Adminhtml\Slide;
/**
 * Class Grid
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class Grid extends Slide
{
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}