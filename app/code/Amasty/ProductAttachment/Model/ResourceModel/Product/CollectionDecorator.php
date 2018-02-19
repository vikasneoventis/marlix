<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\Product;

class CollectionDecorator extends Collection
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var string
     */
    protected $instanceName = null;

    /**
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection = null;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\Catalog\\Model\\ResourceModel\\Product\\Collection')
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->createProductCollection();
        }
        return $this->collection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function createProductCollection()
    {
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->objectManager->create($this->instanceName);
        $collection->addAttributeToSelect('name');
        $productIds = $this->getProductAttachmentAllProductIds();
        if (!empty($productIds)) {
            $collection->addFieldToFilter('entity_id', $productIds);
        }
        return $collection;

    }

    protected function getProductAttachmentAllProductIds()
    {
        /**
         * @var \Amasty\ProductAttachment\Model\ResourceModel\Stat\Collection $productAttachmentCollection
         */
        $productAttachmentCollection = $this->objectManager->create(
            '\\Amasty\\ProductAttachment\\Model\\ResourceModel\\Stat\\Collection'
        );

        return $productAttachmentCollection->getAllProductIds();

    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getProductCollection()->toOptionArray();
    }
}