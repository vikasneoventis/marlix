<?php
namespace Inchoo\CatalogWidget\Block\Product;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    const DEFAULT_COLLECTION_SORT_BY = 'name';
    const DEFAULT_COLLECTION_ORDER = 'asc';

    /**
     * Prepare and return product collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function createCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getSortBy(), $this->getSortOrder());

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        if ($this->getRandOrder()) {
            $collection->getSelect()->orderRand();
        }

        return $collection;
    }

    /**
     * Retrieve sort by
     *
     * @return int
     */
    public function getSortBy()
    {
        if (!$this->hasData('sort_by')) {
            $this->setData('sort_by', self::DEFAULT_COLLECTION_SORT_BY);
        }
        return $this->getData('sort_by');
    }

    /**
     * Retrieve sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        if (!$this->hasData('sort_order')) {
            $this->setData('sort_order', self::DEFAULT_COLLECTION_ORDER);
        }
        return $this->getData('sort_order');
    }

    /**
     * Retrieve random order
     *
     * @return boolean
     */
    public function getRandOrder()
    {
        if (!$this->hasData('rand_order')) {
            $this->setData('rand_order', false);
        }
        return $this->getData('rand_order');
    }
}
