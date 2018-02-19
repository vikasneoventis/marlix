<?php
/**
 * Created by PhpStorm.
 * User: LINHND
 * Date: 6/29/2016
 * Time: 11:18 AM
 */

namespace Yosto\ImageProductSlide\Helper;

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