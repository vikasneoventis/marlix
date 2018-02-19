<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\CollectionUpdate;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\CollectionUpdaterInterface;
use MageWorx\OptionBase\Helper\Data as Helper;

abstract class AbstractUpdater implements CollectionUpdaterInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
    }

    /**
     * Get from conditions for sql join
     *
     * @param string $conditions
     * @return array
     */
    public function getFromConditions($conditions)
    {
        return [];
    }

    /**
     * Get table name for sql join
     *
     * @param string $entityType
     * @return string
     */
    public function getTableName($entityType)
    {
        return '';
    }

    /**
     * Get sql join's "ON" condition clause
     *
     * @return string
     */
    public function getOnConditionsAsString()
    {
        return '';
    }

    /**
     * Get columns for sql join
     *
     * @return array
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * Get table alias for sql join
     *
     * @return string
     */
    public function getTableAlias()
    {
        return '';
    }
}
