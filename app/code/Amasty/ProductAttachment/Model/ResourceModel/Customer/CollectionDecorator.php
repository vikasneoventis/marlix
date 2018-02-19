<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\Customer;

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
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $collection = null;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\Customer\\Model\\ResourceModel\\Customer\\Collection')
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function getCustomerCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->createCustomerCollection();
        }
        return $this->collection;
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function createCustomerCollection()
    {
        /**
         * @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
         */
        $collection = $this->objectManager->create($this->instanceName);
        $collection->addNameToSelect();
        $customerIds = $this->getProductAttachmentAllCustomerIds();
        if(!empty($customerIds)) {
            $collection->addFieldToFilter('entity_id', $customerIds);
        }
        return $collection;
        
    }

    protected function getProductAttachmentAllCustomerIds()
    {
        /**
         * @var \Amasty\ProductAttachment\Model\ResourceModel\File\Collection $productAttachmentCollection
         */
        $productAttachmentCollection = $this->objectManager->create(
            '\\Amasty\\ProductAttachment\\Model\\ResourceModel\\Stat\\Collection'
        );

        return $productAttachmentCollection->getAllCustomerIds();

    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getCustomerCollection()->toOptionArray();
    }
}