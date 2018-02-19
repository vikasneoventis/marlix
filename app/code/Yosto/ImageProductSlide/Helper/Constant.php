<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Helper;

/**
 * Class Constant
 * @package Yosto\CurrencyConverter\Helper
 */
class Constant
{
    const GALLERY_RESOURCE_MODEL
        = 'Yosto\ImageProductSlide\Model\ResourceModel\Gallery';
    const GALLERY_VALUE_RESOURCE_MODEL
        = 'Yosto\ImageProductSlide\Model\ResourceModel\GalleryValue';
    const GALLERY_TABLE = 'catalog_product_entity_media_gallery';
    const GALLERY_VALUE_TABLE = 'catalog_product_entity_media_gallery_value';
    const GALLERY_TABLE_VALUE_ID = 'value_id';
    const GALLERY_VALUE_TABLE_VALUE_ID = 'value_id';
    const GALLERY_MODEL = 'Yosto\ImageProductSlide\Model\Gallery';
    const GALLERY_VALUE_MODEL = 'Yosto\ImageProductSlide\Model\GalleryValue';

    const SLIDE_IMAGE_TABLE = 'yosto_image_product_slide_config';

    const SLIDE_IMAGE_ID = 'slide_id';
    const ANIMATION_SPEED = 'animation_speed';
    const SLIDESHOW_SPEED = 'slideshow_speed';
    const DIRECTION = 'direction';
    const REVERSE = 'reverse';
    const PAUSE_ON_ACTION = 'pause_on_action';
    const PAUSE_ON_HOVER = 'pause_on_hover';
    const RANDOMIZE = 'randomize';
    const ANIMATION = 'animation';

    const SLIDE_MODEL = 'Yosto\ImageProductSlide\Model\Slide';
    const SLIDE_RESOURCE_MODEL = 'Yosto\ImageProductSlide\Model\ResourceModel\Slide';

    const SLIDE_TABLE_COMMENT = 'Config for product image show';

    const IS_IDENTITY = 'identity';
    const IS_UNSIGNED = 'unsigned';
    const IS_NULLABLE = 'nullable';
    const IS_PRIMARY = 'primary';
    const DEFAULT_PROPERTY = 'default';
    const DB_TYPE = 'type';
    const INNO_DB = 'InnoDB';
    const CHARSET = 'charset';
    const UTF8 = 'utf8';
}