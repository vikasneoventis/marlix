<?php

namespace Amasty\Checkout\Model\Config\Source;

class Payment extends \Magento\Payment\Model\Config\Source\Allmethods
{
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $options = parent::toOptionArray();

        array_unshift($options, ['value' => '', 'label' => ' ']);

        return $options;
    }
}
