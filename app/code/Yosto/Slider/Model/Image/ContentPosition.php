<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\Image;

/**
 * Class ContentPosition
 * @package Yosto\Slider\Model\Image
 */
class ContentPosition implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            "left" => __('Left'),
            "center" => __('Center'),
            "right" => __('Right')
        ];
    }


}