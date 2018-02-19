<?php

namespace Potato\ImageOptimization\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ImageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\ImageOptimization\Api\Data\ImageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
