<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Model\Slide\Form;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TypeArray
 * @package Yosto\Slider\Model\Slide\Form
 */
class TypeArray implements ArrayInterface
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $typeCollection = $objectManager->create('Yosto\Slider\Model\ResourceModel\Type\Collection');
        $typeCollection->load();
        $options=[];
        foreach ($typeCollection as $type) {
            $options[$type->getTypeId()] = $type->getTitle();
        }

        return $options;
    }

}