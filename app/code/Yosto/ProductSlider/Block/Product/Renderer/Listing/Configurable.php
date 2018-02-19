<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ProductSlider\Block\Product\Renderer\Listing;

use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as RendererConfigurable;

/**
 * Class Configurable
 * @package Yosto\ProductSlider\Block\Product\Renderer\Listing
 */
class Configurable extends RendererConfigurable
{
    protected $_widgetName;

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . '-' . $this->getWidgetName();
    }


    public function getWidgetName()
    {
        return $this->_widgetName;
    }

    public function setWidgetName($widgetName)
    {
        $this->_widgetName = $widgetName;
        return $this;
    }

}