<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\Image\Grid;

use Magento\Framework\Option\ArrayInterface;
/**
 * Class Status
 * @package Yosto\Slider\Model\Image\Grid
 */
class Status implements ArrayInterface
{
    const ENABLED = 1;
    const DISABLED = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::ENABLED => __('Enabled'),
            self::DISABLED => __('Disabled')
        ];

        return $options;
    }
}