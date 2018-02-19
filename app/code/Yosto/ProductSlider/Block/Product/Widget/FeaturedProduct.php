<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ProductSlider\Block\Product\Widget;

/**
 * Class FeaturedProduct
 * @package Yosto\ProductSlider\Block\Product\Widget
 */
class FeaturedProduct extends AbstractWidget
{
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getProductCollection()
    {
        /**
         * Get product collection
         */
        $productCollection = $this->_productCollectionFactory->create();
        $this->_addProductAttributesAndPrices(
            $productCollection
        )
            ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addAttributeToFilter('featured', 1)
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getCurrentPage());
        return $productCollection;
    }

    protected function getDetailsRendererList()
    {
        $this->setWidgetName('yosto_featured_product_slider');
        return parent::getDetailsRendererList();
    }
}