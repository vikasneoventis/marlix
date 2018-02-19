<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\ProductSlider\Block\Product\Widget;

use Magento\Catalog\Block\Product\Context;

/**
 * Class NewWidget
 * @package Yosto\ProductSlider\Block\Product\Widget
 */
class NewWidget extends AbstractWidget
{
    protected function getDetailsRendererList()
    {
        $this->setWidgetName('yosto_new_product_slider');
        return parent::getDetailsRendererList();
    }
}