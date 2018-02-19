<?php

namespace BoostMyShop\BarcodeLabel\Model\Config\Source;

class Unit implements \Magento\Framework\Option\ArrayInterface
{


    public function toOptionArray()
    {
        $options = array();

        $options[] = array('value' => 'cm', 'label' => 'Centimeters');
        $options[] = array('value' => 'inch', 'label' => 'Inches');

        return $options;
    }

}
