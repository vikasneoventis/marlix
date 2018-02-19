<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Truefalse
 * @package Yosto\ImageProductSlide\Helper
 */
class Truefalse implements ArrayInterface
{
    const TRUE = 1;
    const FALSE = 0;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::TRUE => __('True'),
            self::FALSE => __('False')
        ];

        return $options;
    }
}