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


interface ProductAttachmentStoreConfigInterface
{
    const ID = 'id';
    const FILE_ID = 'file_id';

    const STORE_ID = 'store_id';
    const LABEL = 'label';
    const IS_VISIBLE = 'is_visible';
    const POSITION = 'position';
    const SHOW_FOR_ORDERED = 'show_for_ordered';
    const CUSTOMER_GROUP_IS_DEFAULT = 'customer_group_is_default';

    /**
     * @return int $storeId
     */
    public function getStoreId();

    /**
     * @return string $label
     */
    public function getLabel();

    /**
     * @return int $isVisible
     */
    public function getIsVisible();

    /**
     * @return int $position
     */
    public function getPosition();

    /**
     * @return int $showForOrdered
     */
    public function getShowForOrdered();

    /**
     * @return int $customerGroupIsDefault
     */
    public function getCustomerGroupIsDefault();


    /**
     * @param int $storeId
     *
     * @return ProductAttachmentStoreConfig
     */
    public function setStoreId($storeId);

    /**
     * @param string $label
     *
     * @return ProductAttachmentStoreConfigInterface
     */
    public function setLabel($label);

    /**
     * @param int $isVisible
     *
     * @return ProductAttachmentStoreConfigInterface
     */
    public function setIsVisible($isVisible);

    /**
     * @param int $position
     *
     * @return ProductAttachmentStoreConfigInterface
     */
    public function setPosition($position);

    /**
     * @param int $showForOrdered
     *
     * @return ProductAttachmentStoreConfigInterface
     */
    public function setShowForOrdered($showForOrdered);

    /**
     * @param int $customerGroupIsDefault
     *
     * @return ProductAttachmentStoreConfigInterface
     */
    public function setCustomerGroupIsDefault($customerGroupIsDefault);

}
