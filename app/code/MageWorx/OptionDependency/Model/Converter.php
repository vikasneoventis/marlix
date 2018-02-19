<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model;

use \Magento\Framework\App\ResourceConnection;

class Converter
{
    const CONVERTING_ENTITY_PRODUCT  = 'product';
    const CONVERTING_ENTITY_GROUP    = 'template';
    const CONVERTING_MODE_MAGENTO    = 'magento';
    const CONVERTING_MODE_MAGEWORX   = 'mageworx';

    /**
     * @var array
     */
    protected $mapTable = [
        'product' => [
            'option' => 'catalog_product_option',
            'value' => 'catalog_product_option_type_value',
        ],
        'template' => [
            'option' => 'mageworx_optiontemplates_group_option',
            'value' => 'mageworx_optiontemplates_group_option_type_value',
        ],
    ];

    /**
     * @var array
     */
    protected $mapType = [
        'option' => 'option_id',
        'value' => 'option_type_id'
    ];

    /**
     * @var array
     */
    protected $templateFieldMap = [
        'option' => 'option_id',
        'value' => 'option_value_id'
    ];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var integer
     */
    protected $productId;

    /**
     * @var string
     */
    protected $convertTo = self::CONVERTING_MODE_MAGENTO;

    /**
     * @var string
     */
    protected $convertWhere = self::CONVERTING_ENTITY_PRODUCT;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
    
        $this->resource = $resource;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set product id
     *
     * @param integer $productId
     * @return this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * Set convert where
     * Possible values: product, template
     *
     * @param string $convertWhere
     * @return this
     */
    public function setConvertWhere($convertWhere)
    {
        $this->convertWhere = $convertWhere;
        return $this;
    }

    /**
     * Set convert to
     * Possible values: magento, mageworx
     *
     * @param string $convertTo
     * @return this
     */
    public function setConvertTo($convertTo)
    {
        $this->convertTo = $convertTo;
        return $this;
    }

    /**
     * Convert magento/mageworx IDs to mageworx/magento IDs, used to apply template on product
     * @return string
     */
    public function convert()
    {
        if (!$this->canConvert()) {
            return;
        }

        if ($this->convertTo == self::CONVERTING_MODE_MAGENTO) {
            // prepare option|value ids
            $mageworxOptionIds = $this->prepareIds($this->data, 'option');
            $mageworxValueIds = $this->prepareIds($this->data, 'value');

            // load magento ids same to mageworx ids
            $magentoOptionIds = $this->loadMagentoIds($mageworxOptionIds, 'option');
            $magentoValueIds = $this->loadMagentoIds($mageworxValueIds, 'value');

            $this->replaceData($magentoOptionIds, $magentoValueIds);
        }

        if ($this->convertTo == self::CONVERTING_MODE_MAGEWORX) {
            // prepare magento option|value ids
            $magentoOptionIds = $this->prepareIds($this->data, 'option');
            $magentoValueIds = $this->prepareIds($this->data, 'value');

            // load mageworx ids same to magento ids
            $mageworxOptionIds = $this->loadMageworxIds($magentoOptionIds, 'option');
            $mageworxValueIds = $this->loadMageworxIds($magentoValueIds, 'value');

            $this->replaceData($mageworxOptionIds, $mageworxValueIds);
        }

        return $this->data;
    }

    /**
     * Check if there some data to convert
     * @return string
     */
    protected function canConvert()
    {
        if (empty($this->data)) {
            return false;
        }

        return true;
    }

    /**
     * Form array from ID pairs
     * @param array $array
     * @param string $type
     * @return array
     */
    public function prepareIds($array, $type = 'option')
    {
        $result = [];
        if ($type == 'option') {
            $index = 0;
        } else {
            $index = 1;
        }

        foreach ($array as $row) {
            $parentId = $row[$index];

            if (!in_array($parentId, $result)) {
                $result[] = $parentId;
            }
        }

        return $result;
    }

    /**
     * Load magento IDs
     * @param array $mageworxIds
     * @param string $type
     * @return array
     */
    protected function loadMagentoIds($mageworxIds, $type = 'option')
    {
        $connection = $this->resource->getConnection();

        $table = $this->resource->getTableName($this->mapTable[$this->convertWhere][$type]);
        $field = $this->mapType[$type];

        $result = $connection->fetchPairs('SELECT mageworx_'.$field.', '.$field.' FROM '.$table.' WHERE mageworx_'.$field.' IN (\''.implode("','", $mageworxIds).'\');');

        return $result;
    }

    /**
     * Load mageworx IDs
     * @param array $magentoIds
     * @param string $type
     * @return array
     */
    protected function loadMageworxIds($magentoIds, $type = 'option')
    {
        $connection = $this->resource->getConnection();

        $sql = "";
        $table = $this->resource->getTableName($this->mapTable[$this->convertWhere][$type]);
        $field1 = $this->mapType[$type];
        $field2 = $this->templateFieldMap[$type];

        if ($type == 'option') {
            // select from catalog_product_option table
            $sql = 'SELECT group_'.$field2.', mageworx_'.$field1.' FROM '.$table.' WHERE product_id = '.$this->productId;
            $sql .= ' AND group_'.$field2.' IN (\''.implode("','", $magentoIds).'\');';
        } elseif ($type == 'value') {
            // select from catalog_product_option_type_value table
            $sql = 'SELECT main_table.group_'.$field2.', main_table.mageworx_'.$field1.' FROM '.$table.' AS main_table'
                .' LEFT JOIN '. $this->resource->getTableName('catalog_product_option') .' AS cpo ON cpo.option_id = main_table.option_id'
                .' WHERE cpo.product_id = '.$this->productId.' AND main_table.group_'.$field2.' IN (\''.implode("','", $magentoIds).'\');';
        }

        $result = $connection->fetchPairs($sql);

        return $result;
    }

    /**
     * Replace data using third-party arrays
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function replaceData($array1, $array2)
    {
        $data = $this->data;

        foreach ($data as $rowKey => $row) {
            foreach ($row as $key => $id) {
                if (isset($array1[$id])) {
                    $this->data[$rowKey][$key] = $array1[$id];
                } elseif (isset($array2[$id])) {
                    $this->data[$rowKey][$key] = $array2[$id];
                }
            }
        }
    }
}
