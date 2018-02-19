<?php
/**
 * Created by PhpStorm.
 * User: LINHND
 * Date: 6/29/2016
 * Time: 11:32 AM
 */

namespace Yosto\ImageProductSlide\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Direction
 * @package Yosto\ImageProductSlide\Helper
 */
class Direction implements ArrayInterface
{
    const HORIZONTAL = 'horizontal';
    const VERTICAL = 'vertical';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::HORIZONTAL => __('Horizontal'),
            self::VERTICAL => __('Vertical')
        ];

        return $options;
    }
}