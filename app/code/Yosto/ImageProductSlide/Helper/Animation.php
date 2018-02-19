<?php
/**
 * Created by PhpStorm.
 * User: LINHND
 * Date: 6/29/2016
 * Time: 11:31 AM
 */

namespace Yosto\ImageProductSlide\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Animation
 * @package Yosto\ImageProductSlide\Helper
 */
class Animation implements ArrayInterface
{
    const FADE = 'fade';
    const SLIDE = 'slide';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::FADE => __('Fade'),
            self::SLIDE => __('Slide')
        ];

        return $options;
    }
}