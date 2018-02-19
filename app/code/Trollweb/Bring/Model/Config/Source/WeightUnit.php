<?php

namespace Trollweb\Bring\Model\Config\Source;

use \Trollweb\Bring\Helper\Measurement;

class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Measurement::WEIGHT_UNIT_G, 'label' => __('Gram')],
            ['value' => Measurement::WEIGHT_UNIT_KG, 'label' => __('Kilogram')],
        ];
    }

    public function toArray()
    {
        return [
            Measurement::WEIGHT_UNIT_G => __('Gram'),
            Measurement::WEIGHT_UNIT_KG => __('Kilogram'),
        ];
    }
}
