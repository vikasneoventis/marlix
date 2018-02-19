<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\ProductSlider\Block\Product\Widget;

/**
 * Class MostViewed
 * @package Yosto\ProductSlider\Block\Product\Widget
 */
class MostViewed extends AbstractWidget
{
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getProductCollection()
    {
        /*
         * When period is null, bestseller will be grouped by product_id
         * and order by SUM('qty_ordered')
         */
        $periodType = $this->getData('mode');
        $ratingLimit = $this->getData('rating_limit');
        if ($periodType == 'all') {
            $periodType = null;
            $ratingLimit = $this->getData('products_count');
        } else {
            $periodType = $this->getData('period');
        }

        $collection = $this->_mostViewedCollectionFactory
            ->create()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setRatingLimit($ratingLimit)
            ->setPeriod($periodType);

        /**
         * if user sets date range to filer
         */
        if ($this->getData('from') && $this->getData('to')) {
            $collection->setDateRange($this->getData('from'), $this->getData('to'));
        }
        /**
         * Get Product Ids, if product is a child product of a configurable product,
         * Parent_id will be added.
         */


        $productIds = [];
        foreach ($collection as $item) {
            $parentId = $this->_catalogProductTypeConfigurable->getParentIdsByChild($item->getData('product_id'));
            if (isset($parentId[0])) {
                $productIds[] = $parentId[0];
            } else {
                $productIds[] = $item->getData('product_id');
            }
        }
        /**
         * Get product collection
         */
        $productCollection = $this->_productCollectionFactory->create();
        $this->_addProductAttributesAndPrices(
            $productCollection
        )
            ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getCurrentPage());
        return $productCollection;
    }
    protected function getDetailsRendererList()
    {
        $this->setWidgetName('yosto_most_viewed_product_slider');
        return parent::getDetailsRendererList();
    }
}