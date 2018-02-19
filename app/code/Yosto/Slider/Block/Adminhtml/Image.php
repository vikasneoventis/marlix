<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml;

/**
 * Class Image
 * @package Yosto\Slider\Block\Adminhtml
 */
class Image extends  \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Yosto_Slider';
        $this->_headerText = __('Manage Image');
        $this->_addButtonLabel = __('Add Image');
        parent::_construct();
    }
}