<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ProductAttachmentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attachments list.
     *
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface[]
     */
    public function getItems();

    /**
     * Set attachments list.
     *
     * @param \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
