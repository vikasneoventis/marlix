<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\ResourceModel\Slide;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Yosto\Slider\Helper\Constant;
/**
 * Class Collection
 * @package Yosto\Slider\Model\ResourceModel\Slide
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Constant::SLIDE_MODEL, Constant::SLIDE_RESOURCE_MODEL);
    }

    public function joinToGetImages()
    {
        $slideImageTable = $this->getTable(Constant::SLIDE_IMAGE_TABLE);
        $imageTable = $this->getTable(Constant::IMAGE_TABLE);
        $this->getSelect()->join(
            $slideImageTable . " as slide_image",
            "main_table.slide_id = slide_image.slide_id"
        )->join(
            $imageTable . "as image",
            'slide_image.image_id = image.image_id'
        )->where("image.status = 1")
        ->where('main_table.status = 1');
    }
}