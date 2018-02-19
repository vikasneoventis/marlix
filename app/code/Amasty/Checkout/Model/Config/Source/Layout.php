<?php

namespace Amasty\Checkout\Model\Config\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '2columns', 'label' => '2 Columns'],
            ['value' => '3columns', 'label' => '3 Columns'],
        ];
    }
}
