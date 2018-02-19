<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\CustomTab\Model\Select;

/**
 * Class Status
 * @package Yosto\CustomTab\Model\System\Config\Select
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format:
       array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Enable')],
            ['value' => 0, 'label' => __('Disable')],
        ];
    }
}