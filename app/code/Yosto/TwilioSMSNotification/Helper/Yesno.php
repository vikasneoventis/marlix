<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Yesno
 * @package Yosto\TwilioSMSNotification\Helper
 */
class Yesno implements ArrayInterface
{
    const YES = 1;
    const NO = 0;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::YES => __('Yes'),
            self::NO => __('No')
        ];

        return $options;
    }
}