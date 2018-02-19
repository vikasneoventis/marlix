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

use Amasty\ProductAttachment\Api\Data\ProductAttachmentStoreConfigInterface;
use Magento\Framework\DataObject;

class ProductAttachmentStoreConfig extends DataObject implements ProductAttachmentStoreConfigInterface
{
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    public function getIsVisible()
    {
        return $this->getData(self::IS_VISIBLE);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getShowForOrdered()
    {
        return $this->getData(self::SHOW_FOR_ORDERED);
    }

    public function getCustomerGroupIsDefault()
    {
        return $this->getData(self::CUSTOMER_GROUP_IS_DEFAULT);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    public function setShowForOrdered($showForOrdered)
    {
        return $this->setData(self::SHOW_FOR_ORDERED, $showForOrdered);
    }

    public function setCustomerGroupIsDefault($customerGroupIsDefault)
    {
        return $this->setData(self::CUSTOMER_GROUP_IS_DEFAULT, $customerGroupIsDefault);
    }

}
