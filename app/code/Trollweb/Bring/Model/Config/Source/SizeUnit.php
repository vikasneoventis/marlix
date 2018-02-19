<?php

namespace Trollweb\Bring\Model\Config\Source;

use \Trollweb\Bring\Helper\Measurement;

class SizeUnit implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Measurement::SIZE_UNIT_MM, 'label' => __('Millimeter')],
            ['value' => Measurement::SIZE_UNIT_CM, 'label' => __('Centimeter')],
            ['value' => Measurement::SIZE_UNIT_DM, 'label' => __('Decimeter')],
            ['value' => Measurement::SIZE_UNIT_M, 'label' => __('Meter')],
        ];
    }

    public function toArray()
    {
        return [
            Measurement::SIZE_UNIT_MM => __('Millimeter'),
            Measurement::SIZE_UNIT_CM => __('Centimeter'),
            Measurement::SIZE_UNIT_DM => __('Decimeter'),
            Measurement::SIZE_UNIT_M => __('Meter'),
        ];
    }
}
