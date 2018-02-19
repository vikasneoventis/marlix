<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\Slider\Block\Adminhtml;
use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Slide
 * @package Yosto\Slider\Block\Adminhtml
 */
class Slide extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Yosto_Slider';
        $this->_headerText = __('Manage Slide');
        $this->_addButtonLabel = __('Add Slide');
        parent::_construct();
    }
}