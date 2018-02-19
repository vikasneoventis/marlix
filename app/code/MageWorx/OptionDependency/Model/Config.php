<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractExtensibleModel;

class Config extends AbstractExtensibleModel
{
    const TABLE_NAME = 'mageworx_option_dependency';
    const OPTIONTEMPLATES_TABLE_NAME = 'mageworx_optiontemplates_group_option_dependency';

    const COLUMN_NAME_DEPENDENCY_ID            = 'dependency_id';
    const COLUMN_NAME_CHILD_OPTION_ID          = 'child_option_id';
    const COLUMN_NAME_CHILD_OPTION_TYPE_ID     = 'child_option_type_id';
    const COLUMN_NAME_PARENT_OPTION_ID         = 'parent_option_id';
    const COLUMN_NAME_PARENT_OPTION_TYPE_ID    = 'parent_option_type_id';
    const COLUMN_NAME_PRODUCT_ID               = 'product_id';
    const COLUMN_NAME_GROUP_ID                 = 'group_id';
    const COLUMN_NAME_OPTION_TYPE_TITLE_ID     = 'option_type_title_id';
    const COLUMN_NAME_OPTION_TITLE_ID          = 'option_title_id';

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\OptionDependency\Model\ResourceModel\Config');
        $this->setIdFieldName('dependency_id');
    }

    /**
     * Get product options
     * @param integer $productId
     * @return string
     */
    public function allProductOptions($productId)
    {
        return $data = $this->_getResource()->allProductOptions($productId);
    }

    /**
     * Get 'child_option_id' - 'parent_option_type_id' pairs
     * @param integer $productId
     * @return string
     */
    public function getOptionParents($productId)
    {
        $columns = ['child_option_id', 'parent_option_type_id'];
        $data = $this->_getResource()
            ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'child_option_type_id' - 'parent_option_type_id' pairs in json
     * @param integer $productId
     * @return string
     */
    public function getValueParents($productId)
    {
        $columns = ['child_option_type_id', 'parent_option_type_id'];
        $data = $this->_getResource()
            ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'parent_option_type_id' - 'child_option_id' pairs in json
     * @param integer $productId
     * @return string
     */
    public function getOptionChildren($productId)
    {
        $columns = ['parent_option_type_id', 'child_option_id'];
        $data = $this->_getResource()
            ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'parent_option_type_id' - 'child_option_type_id' pairs in json
     * @param integer $productId
     * @return string
     */
    public function getValueChildren($productId)
    {
        $columns = ['parent_option_type_id', 'child_option_type_id'];
        $data = $this->_getResource()
            ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get option types ('mageworx_option_id' => 'type') in json
     * @param integer $productId
     * @return string
     */
    public function getOptionTypes($productId)
    {
        $data = $this->_getResource()
            ->loadOptionTypes($productId);

        return $data;
    }

    /**
     * Retrieve array of mageworx_option_id (mageworx_option_type_id) by option_id (option_type_id)
     * @param string $code
     * @param array $ids
     * @return array
     */
    public function convertToMageworxId($code = 'option', $ids = [])
    {
        $resource = $this->_getResource();

        switch ($code) {
            case 'option':
                $data = $resource->loadMageworxOptionId($ids);
                break;
            case 'value':
                $data = $resource->loadMageworxOptionTypeId($ids);
                break;
        }

        return $data;
    }

    /**
     * Compact array, remove duplicates
     * @param array $array
     * @param array $cols
     * @return array
     */
    protected function compactArray($array, $cols)
    {
        $keyName = $cols[0];
        $valueName = $cols[1];

        $result = [];

        foreach ($array as $row) {
            $key = $row[$keyName];
            $value = $row[$valueName];

            if (!isset($result[$key])) {
                $result[$key][] = $value;
                continue;
            }

            if (in_array($value, $result[$key])) {
                continue;
            }

            $result[$key][] = $value;
        }

        return $result;
    }
}
