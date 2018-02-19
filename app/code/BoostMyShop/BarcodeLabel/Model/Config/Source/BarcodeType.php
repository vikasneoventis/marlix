<?php

namespace BoostMyShop\BarcodeLabel\Model\Config\Source;

class BarcodeType implements \Magento\Framework\Option\ArrayInterface
{


    public function toOptionArray()
    {
        $options = array();

        $list = array(
            'Code128'=> 'Code 128',
            'Code25' => 'Code 25',
            'Code25interleaved' => 'Code 25 interleaved',
            'Code39' => 'Code39',
            'Ean2' => 'Ean 2',
            'Ean5' => 'Ean 5',
            'Ean8' => 'Ean 8',
            'Ean13' => 'Ean 13',
            'Identcode' => 'Identcode',
            'Itf14' => 'ITF-14',
            'Leitcode' => 'Leitcode',
            'Planet' => 'Planet',
            'Postnet' => 'Postnet',
            'Royalmail' => 'Royal Mail',
            'Upca' => 'Upc-A',
            'Upce' => 'Upc-E'
        );

        foreach($list as $k => $v)
            $options[] = array('value' => $k, 'label' => $v);

        return $options;
    }

}
