<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Api\Data;


interface ProductAttachmentCustomerGroupInterface
{
    const STORE_ID = 'store_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * @param int $storeId
     *
     * @return ProductAttachmentCustomerGroupInterface
     */
    public function setStoreId($storeId);

    /**
     * @param int $customerGroupId
     *
     * @return ProductAttachmentCustomerGroupInterface
     */
    public function setCustomerGroupId($customerGroupId);
}
