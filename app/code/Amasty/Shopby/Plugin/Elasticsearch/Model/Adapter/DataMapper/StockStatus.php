<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Amasty\Shopby\Model\Layer\Filter\Stock as FilterStock;


/**
 * Class StockStatus
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class StockStatus implements DataMapperInterface
{
    const FIELD_NAME = 'stock_status';
    const DOCUMENT_FIELD_NAME = 'quantity_and_stock_status';
    const INDEX_DOCUMENT = 'document';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $inStockProductIds = [];

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockStatusResource;


    /**
     * OnSale constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatusResource
     * @internal param \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface $stockStatusCollection
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatusResource
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $value = isset($context[self::INDEX_DOCUMENT][self::DOCUMENT_FIELD_NAME])
            ? $context[self::INDEX_DOCUMENT][self::DOCUMENT_FIELD_NAME]
            : $this->isProductInStock($entityId, $storeId);
        return [self::FIELD_NAME => $value];
    }

    /**
     * @param $entityId
     * @param $storeId
     * @return int
     */
    private function isProductInStock($entityId, $storeId)
    {
        return in_array($entityId, $this->getInStockProductIds($storeId))
            ? FilterStock::FILTER_IN_STOCK : FilterStock::FILTER_OUT_OF_STOCK;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getInStockProductIds($storeId)
    {
        if (!isset($this->inStockProductIds[$storeId])) {
            $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);
            $this->stockStatusResource->addStockDataToCollection($collection, true);
            $this->inStockProductIds[$storeId] = $collection->getAllIds();
        }

        return $this->inStockProductIds[$storeId];
    }
    
}
