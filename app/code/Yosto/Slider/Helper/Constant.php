<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.txt for details.
 */
namespace Yosto\Slider\Helper;

/**
 * Contains database table information.
 *
 * Class Constant
 * @package Yosto\Slider\Helper
 */
class Constant
{
    const SLIDE_TABLE='yosto_slider_slide';
    const TYPE_TABLE='yosto_slider_type';
    const IMAGE_TABLE='yosto_slider_image';
    const SLIDE_IMAGE_TABLE='yosto_slider_slide_image';

    const SLIDE_TABLE_ID='slide_id';
    const TYPE_TABLE_ID='type_id';
    const IMAGE_TABLE_ID='image_id';
    const SLIDE_IMAGE_TABLE_ID='slide_image_id';

    const SLIDE_TABLE_COMMENT='Slide table';
    const TYPE_TABLE_COMMENT='Type table';
    const IMAGE_TABLE_COMMENT='Image table';
    const SLIDE_IMAGE_TABLE_COMMENT='Slide Image Table';

    const SLIDE_MODEL='Yosto\Slider\Model\Slide';
    const TYPE_MODEL='Yosto\Slider\Model\Type';
    const IMAGE_MODEL='Yosto\Slider\Model\Image';
    const SLIDE_IMAGE_MODEL='Yosto\Slider\Model\SlideImage';

    const SLIDE_RESOURCE_MODEL='Yosto\Slider\Model\ResourceModel\Slide';
    const TYPE_RESOURCE_MODEL='Yosto\Slider\Model\ResourceModel\Type';
    const IMAGE_RESOURCE_MODEL='Yosto\Slider\Model\ResourceModel\Image';
    const SLIDE_IMAGE_RESOURCE_MODEL='Yosto\Slider\Model\ResourceModel\SlideImage';
}