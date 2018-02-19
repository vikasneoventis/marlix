<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DataProvider\Category;

use Magento\Framework\App\ResourceConnection;
use MageWorx\SeoXTemplates\Model\ConverterCategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use MageWorx\SeoAll\Helper\LinkFieldResolver;

class Eav extends \MageWorx\SeoXTemplates\Model\DataProvider\Category
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     *
     * @var int
     */
    protected $_defaultStore;

    /**
     *
     * @var int
     */
    protected $_storeId;

    /**
     *
     * @var array
     */
    protected $_attributeCodes = [];

    /**
     *
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var \MageWorx\SeoAll\Helper\LinkFieldResolver
     */
    protected $linkFieldResolver;

    /**
     * Eav constructor.
     * @param ResourceConnection $resource
     * @param ConverterCategoryFactory $converterCategoryFactory
     * @param Category $categoryResource
     * @param LinkFieldResolver $linkFieldResolver
     */
    public function __construct(
        ResourceConnection $resource,
        ConverterCategoryFactory $converterCategoryFactory,
        Category $categoryResource,
        LinkFieldResolver $linkFieldResolver
    ) {
        parent::__construct($resource, $converterCategoryFactory);
        $this->linkFieldResolver = $linkFieldResolver;
        $this->categoryResource = $categoryResource;
    }


    /**
     * Retrieve data
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \MageWorx\SeoXTemplates\Model\AbstractTemplate $template
     * @param int|null $customStoreId
     * @return array
     */
    public function getData($collection, $template, $customStoreId = null)
    {
        if (!$collection) {
            return false;
        }

        $this->_collection = $collection;
        $this->_storeId    = $customStoreId ? $customStoreId : $template->getStoreId();

        $this->_attributeCodes = $template->getAttributeCodesByType();

        $attributes  = [];
        $connection  = $this->_getConnection();

        $select        = $connection->select()
            ->from($this->_resource->getTableName('eav_entity_type'))
            ->where("entity_type_code = 'catalog_category'");
        $categoryTypeId = $connection->fetchOne($select);

        foreach ($this->_attributeCodes as $_attrName) {
            $select                 = $connection->select()
                ->from($this->_resource->getTableName('eav_attribute'))
                ->where("entity_type_id = $categoryTypeId AND (attribute_code = '" . $_attrName . "')");

            if ($res = $connection->fetchRow($select)) {
                $attributes[$_attrName] = $res;
            }
        }

        $categoryIds       = array_keys($this->getCollectionIds());
        $categoryIdsString = implode(',', $categoryIds);

        $data = [];

        $linkField = $this->getLinkField();
        foreach ($attributes as $attribute) {
            $idsByAttribute = [
                'insert' => array_fill_keys($categoryIds, []),
                'update' => []
            ];

            $select = $connection->select([$linkField])
                ->from($this->_resource->getTableName('catalog_category_entity') . '_' . $attribute['backend_type'])
                ->where("attribute_id = '$attribute[attribute_id]' AND $linkField IN ({$categoryIdsString}) AND store_id = {$this->_storeId} AND CHAR_LENGTH(value) > 0");

            $existRecords = $connection->fetchAll($select);

            foreach ($existRecords as $record) {
                if ($template->isScopeForAll()) {
                    $idsByAttribute['update'][$record[$linkField]] = ['old_value' => $record['value']];
                }
                unset($idsByAttribute['insert'][$record[$linkField]]);
            }

            $attributeHash = $attribute['attribute_id'] . '#' .  $attribute['attribute_code'] . '#' . $attribute['backend_type'];
            $data[$attributeHash] = $idsByAttribute;
        }

        $this->fillData($template, $data);

        return $data;
    }

    /**
     * Add data for each entityId
     *
     *
     *
     * @param array $data
     */
    protected function fillData($template, &$data)
    {
        $linkField = $this->getLinkField();
        $connect = $this->getCollectionIds();
        foreach ($data as $attributeHash => $attributeData) {
            list($attributeId, $attributeCode) = explode('#', $attributeHash);

            $converter = $this->converterCategoryFactory->create($attributeCode);

            foreach ($attributeData as $insertTypeName => $insertData) {
                foreach ($insertData as $entityId => $emptyValue) {
//                    $microtime = microtime(1);
                    $attributeValue = '';
                    $category = $this->_collection->getItemById($connect[$entityId]);
                    if ($category) {
                        $attributeValue = $converter->convert($category->setStoreId($this->_storeId), $template->getCode());
                    }

//                    echo "<br><font color = green>" . number_format((microtime(1) - $microtime), 5) . " sec need for " . get_class($this) . "</font>";

                    if ($attributeValue) {
                        $data[$attributeHash][$insertTypeName][$entityId] = array_merge($data[$attributeHash][$insertTypeName][$entityId], [
                            'attribute_id' => $attributeId,
                            $linkField     => $entityId,
                            'store_id'     => $this->_storeId,
                            'value'        => $attributeValue,
                        ]);
                    } else {
                        unset($data[$attributeCode][$insertTypeName][$entityId]);
                    }
                }
            }
        }
    }

    public function getAttributeCodes()
    {
        return $this->_attributeCodes;
    }

    /**
     * return array row_id => entity_id or entity_id => entity_id
     */
    public function getCollectionIds()
    {
        $data = [];
        $linkField = $this->getLinkField();
        foreach ($this->_collection as $item) {
            $data[$item->getData($linkField)] = $item->getData('entity_id');
        }
        return $data;
    }

    /**
     * @return string
     */
    protected function getLinkField()
    {
        return $this->linkFieldResolver->getLinkField(CategoryInterface::class, 'entity_id');
    }
}
