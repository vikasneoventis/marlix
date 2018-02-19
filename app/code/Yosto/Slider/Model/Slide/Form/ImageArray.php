<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\Slide\Form;

use Magento\Framework\Option\ArrayInterface;
/**
 * Class ImageArray
 * @package Yosto\Slider\Model\Slide\Form
 */
class ImageArray implements ArrayInterface
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $imageCollection = $objectManager->create('Yosto\Slider\Model\ResourceModel\Image\Collection');
        $imageCollection->addFieldToFilter('status',1)
            ->setOrder('sort_order','asc')
            ->load();
        $options=array();
        foreach ($imageCollection as $image) {
            $options[] =array('value'=>$image->getImageId(),'label'=>$image->getName());
        }
        return $options;
    }


}