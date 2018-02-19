<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use MageWorx\OptionFeatures\Model\ProductAttributes;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\Data\ProductInterface;

class Collection
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param ResourceConnection $resource
     * @param BaseHelper $baseHelper
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        BaseHelper $baseHelper,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->baseHelper = $baseHelper;
        $this->helper = $helper;
    }

    /**
     * @param ProductCollection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad($subject, $printQuery = false, $logQuery = false)
    {
        $this->addAbsolutes($subject);

        return [$printQuery, $logQuery];
    }

    /**
     * Add absolutes to product collection
     *
     * @param ProductCollection $collection
     * @return ProductCollection $collection
     */
    private function addAbsolutes($collection)
    {
        $from = $collection->getSelect()->getPart('from');
        if (isset($from['product_absolutes'])) {
            // Do nothing if tables already has been joined
            return $collection;
        }
        if ($from[ProductCollection::MAIN_TABLE_ALIAS]['tableName'] ==
            $this->resource->getTableName('mageworx_optiontemplates_group')) {
            $tableName = $this->resource->getTableName(ProductAttributes::OPTIONTEMPLATES_TABLE_NAME);
            $condition = '`' . ProductCollection::MAIN_TABLE_ALIAS . '`.`group_id` = `product_absolutes`.`group_id`';
        } else {
            $tableName = $this->resource->getTableName(ProductAttributes::TABLE_NAME);
            $condition = '`' . ProductCollection::MAIN_TABLE_ALIAS . '`.`' .
                $this->baseHelper->getLinkField(ProductInterface::class) .
                '` = `product_absolutes`.`product_id`';
        }

        $collection->getSelect()->joinLeft(
            [
                'product_absolutes' => $tableName
            ],
            $condition,
            [
                'absolute_price' => 'product_absolutes.' . Helper::KEY_ABSOLUTE_PRICE,
                'absolute_cost' => 'product_absolutes.' . Helper::KEY_ABSOLUTE_COST,
                'absolute_weight' => 'product_absolutes.' . Helper::KEY_ABSOLUTE_WEIGHT,
            ]
        );

        return $collection;
    }
}
