<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\Slide\Widget;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class SlideArray
 * @package Yosto\Slider\Model\Slide\Widget
 */
class SlideArray implements ArrayInterface
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $slideCollection = $objectManager->create('Yosto\Slider\Model\ResourceModel\Slide\Collection');
        $slideCollection->addFieldToFilter('status',1)->load();
        $options=[];
       foreach ($slideCollection as $slide) {
            $options[]=['value'=>$slide->getSlideId(),'label'=>$slide->getTitle()];
        }
        return $options;
    }

}