<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Adminhtml;

use MageWorx\OptionBase\Model\Entity\Base as BaseEntityModel;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use \Magento\Framework\App\Request\Http as HttpRequest;
use \Magento\Framework\Registry;

class DuplicateDependency
{
    /**
     * @var BaseEntityModel
     */
    protected $baseEntityModel;

    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    protected $resource;

    protected $connection;

    public function __construct(
        BaseEntityModel $baseEntityModel,
        OptionBaseHelper $helper,
        HttpRequest $request,
        Registry $registry
    ) {
    
        $this->baseEntityModel = $baseEntityModel;
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    public function aroundCopy($subject, \Closure $proceed, $product)
    {
        $result = $proceed($product);

        $oldProductId = $this->helper->isEnterprise() ? $product->getRowId() : $product->getEntityId();
        $newProductId = $this->helper->isEnterprise() ? $result->getRowId() : $result->getEntityId();

        $mapMageworxOptionId = $this->registry->registry('mapMageworxOptionId');
        $mapMageworxOptionTypeId = $this->registry->registry('mapMageworxOptionTypeId');

        $this->clearRegistryData();
        $this->createConnection();

        $dependency = $this->getDependency($oldProductId);

        if (!$dependency) {
            return $result;
        }

        $dependency = $this->updateDependency(
            $dependency,
            $newProductId,
            $mapMageworxOptionId,
            $mapMageworxOptionTypeId
        );

        $this->saveDependency($dependency);

        return $result;
    }

    /**
     * Clear the registered data to free memory.
     *
     * @return $this
     */
    protected function clearRegistryData()
    {
        $this->registry->unregister('mapMageworxOptionId');
        $this->registry->unregister('mapMageworxOptionTypeId');

        return $this;
    }

    /**
     * Create connection with the database.
     *
     * @return $this
     */
    protected function createConnection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $this->resource = $resource;
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get dependencies from the duplicated product.
     *
     * @param int $productId
     * @return array
     */
    protected function getDependency($productId)
    {
        $table = $this->resource->getTableName('mageworx_option_dependency');
        $sql = $this->connection->select()
            ->from(
                $table,
                ['child_option_id', 'child_option_type_id', 'parent_option_id', 'parent_option_type_id', 'product_id']
            )
            ->where('product_id = ?', $productId);

        return $this->connection->fetchAll($sql);
    }

    /**
     * Update old dependencies with new mageworx_id.
     *
     * @param array $dependency
     * @param int $newProductId
     * @param array $mapMageworxOptionId
     * @param array $mapMageworxOptionTypeId
     * @return array
     */
    protected function updateDependency($dependency, $newProductId, $mapMageworxOptionId, $mapMageworxOptionTypeId)
    {
        foreach ($dependency as $id => $row) {
            $dependency[$id]['child_option_id'] = $mapMageworxOptionId[$row['child_option_id']];
            $dependency[$id]['parent_option_id'] = $mapMageworxOptionId[$row['parent_option_id']];
            $dependency[$id]['child_option_type_id'] = $mapMageworxOptionTypeId[$row['child_option_type_id']];
            $dependency[$id]['parent_option_type_id'] = $mapMageworxOptionTypeId[$row['parent_option_type_id']];
            $dependency[$id]['product_id'] = $newProductId;
        }

        return $dependency;
    }

    /**
     * Save puplicated dependencies to the database.
     *
     * @param array $dependency
     */
    protected function saveDependency($dependency)
    {
        $table = $this->resource->getTableName('mageworx_option_dependency');

        $this->connection->insertMultiple($table, $dependency);
    }
}
