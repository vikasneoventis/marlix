<?php

namespace Trollweb\Bring\Model\Config\Source;

use \Trollweb\Bring\Helper\Price;

class PriceRoundingStrategy implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Price::ROUND_NONE, 'label' => __("No rounding")],
            ['value' => Price::ROUND_NEAREST_INT, 'label' => __("Round to closest integer")],
            ['value' => Price::ROUND_UP_INT, 'label' => __("Round up to closest integer")],
            ['value' => Price::ROUND_DOWN_INT, 'label' => __("Round down to closest integer")],
            ['value' => Price::ROUND_NEAREST_TEN, 'label' => __("Round to closest integer divisible by ten")],
            ['value' => Price::ROUND_UP_TEN, 'label' => __("Round up to closest integer divisible by ten")],
            ['value' => Price::ROUND_DOWN_TEN, 'label' => __("Round down to closest integer divisible by ten")],
        ];
    }

    public function toArray()
    {
        return [
            Price::ROUND_NONE => __("No rounding"),
            Price::ROUND_NEAREST_INT => __("Round to closest integer"),
            Price::ROUND_UP_INT => __("Round up to closest integer"),
            Price::ROUND_DOWN_INT => __("Round down to closest integer"),
            Price::ROUND_NEAREST_TEN => __("Round to closest integer divisible by ten"),
            Price::ROUND_UP_TEN => __("Round up to closest integer divisible by ten"),
            Price::ROUND_DOWN_TEN => __("Round down to closest integer divisible by ten"),
        ];
    }
}
