<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Model;


use Amasty\ProductAttachment\Api\Data\ProductAttachmentCustomerGroupInterface;
use Magento\Framework\DataObject;

class ProductAttachmentCustomerGroup extends DataObject implements ProductAttachmentCustomerGroupInterface
{
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @param int $storeId
     *
     * @return ProductAttachmentCustomerGroupInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @param int $customerGroupId
     *
     * @return ProductAttachmentCustomerGroupInterface
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }
}
