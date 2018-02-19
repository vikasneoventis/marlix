<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

interface AttributeInterface
{
    /**
     * Get attribute name
     */
    public function getName();

    /**
     * Get table name, used when attribute use individual tables
     */
    public function getTableName();

    /**
     * Apply attribute data
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     * @param array $options
     */
    public function applyData($entity, $options);

    /**
     * Clear attribute data
     */
    public function clearData();

    /**
     * Prepare attribute data for frontend js config
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     */
    public function prepareData($object);
}
