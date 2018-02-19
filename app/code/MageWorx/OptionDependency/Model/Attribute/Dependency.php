<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\Attribute;

use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionDependency\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionDependency\Model\Config;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \MageWorx\OptionDependency\Model\Converter;

class Dependency implements AttributeInterface
{
    /**
     * @var string
     */
    protected $saveSql = "";

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product
     */
    protected $entity;

    /**
     * @var Helper
     */
    protected $options;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param JsonHelper $jsonHelper
     * @param ObjectManager $objectManager
     * @param Converter $converter
     */
    public function __construct(
        ObjectManager $objectManager,
        ResourceConnection $resource,
        Helper $helper,
        Converter $converter,
        JsonHelper $jsonHelper
    ) {
        $this->objectManager = $objectManager;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->converter = $converter;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'field_hidden_dependency';
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        $map = [
            'product' => Config::TABLE_NAME,
            'group' => Config::OPTIONTEMPLATES_TABLE_NAME
        ];
        return $map[$this->entity->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function clearData()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyData($entity, $options)
    {
        $this->entity = $entity;
        $this->options = $options;

        $this->clearOldDependencies();
        $this->saveDependencies();
    }

    /**
     * Clear old dependencies
     * @return void
     */
    protected function clearOldDependencies()
    {
        $connection = $this->resource->getConnection();

        if ($this->entity->getType() == 'group') {
            $connection->delete(
                $this->resource->getTableName($this->getTableName()),
                [
                    'group_id = ?' => $this->entity->getDataObjectId(),
                ]
            );
        } elseif ($this->entity->getType() == 'product') {
            $select = $connection->select()
                ->reset()
                ->from(['dep' => $this->resource->getTableName($this->getTableName())])
                ->joinLeft(
                    ['cpo' => $this->resource->getTableName('catalog_product_option')],
                    'cpo.mageworx_option_id = dep.child_option_id',
                    []
                );
            if ($this->entity->getDataObject()->getIsAfterTemplateSave()) {
                $select->where("(cpo.group_option_id IS NOT NULL OR cpo.group_option_id != 0) AND dep.product_id = ".$this->entity->getDataObjectId());
            } else {
                $select->where("dep.product_id = ".$this->entity->getDataObjectId());
            }
            $sql = $select->deleteFromSelect('dep');
            $connection->query($sql);

            $select = $connection->select()
                ->reset()
                ->from(['dep' => $this->resource->getTableName($this->getTableName())])
                ->joinLeft(
                    ['cpo' => $this->resource->getTableName('catalog_product_option')],
                    'cpo.mageworx_option_id = dep.parent_option_id',
                    []
                );
            if ($this->entity->getDataObject()->getIsAfterTemplateSave()) {
                $select->where("(cpo.group_option_id IS NOT NULL OR cpo.group_option_id != 0) AND dep.product_id = ".$this->entity->getDataObjectId());
            } else {
                $select->where("dep.product_id = ".$this->entity->getDataObjectId());
            }
            $sql = $select->deleteFromSelect('dep');
            $connection->query($sql);
        }
        return;
    }

    /**
     * Save dependencies
     * @return void
     */
    protected function saveDependencies()
    {
        $connection = $this->resource->getConnection();

        if (empty($this->options)) {
            return;
        }

        $data = [];
        foreach ($this->options as $option) {
            $this->addData($data, $option);

            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $value) {
                $this->addData($data, $value);
            }
        }
        if (!$data) {
            return;
        }
        $connection->insertMultiple($this->resource->getTableName($this->getTableName()), $data);
    }

    /**
     * Add dependencies data from object to overall data array
     * @param $data - option or value.
     * @param $object - option or value.
     * @return void
     */
    protected function addData(&$data, $object)
    {
        $childOptionId = isset($object['mageworx_option_id']) ? $object['mageworx_option_id'] : null;
        $childOptionTypeId = isset($object['mageworx_option_type_id']) ? $object['mageworx_option_type_id'] : '';
        $dataObjectId = $this->entity->getDataObjectId();
        $fieldHiddenDependency = isset($object['field_hidden_dependency']) ? $object['field_hidden_dependency'] : null;

        // exit if option or value has no dependencies
        if (!$fieldHiddenDependency) {
            return;
        }
        $savedDependencies = $this->jsonHelper->jsonDecode($fieldHiddenDependency);

        if ($this->entity->getType() == 'product') {
            $savedDependencies = $this->convertDependencies($savedDependencies, $dataObjectId);
        }
        // delete non-existent options from dependencies
        $savedDependencies = $this->processDependencies($savedDependencies);
        $savedDependencies = $this->convertRecordIdToMageworxId($savedDependencies);

        foreach ($savedDependencies as $dependency) {
            $parentOptionId = $dependency[0];
            $parentOptionTypeId = $dependency[1];
            $data[] = [
               'child_option_id' => $childOptionId,
               'child_option_type_id' => $childOptionTypeId,
               'parent_option_id' => $parentOptionId,
               'parent_option_type_id' => $parentOptionTypeId,
               $this->entity->getDataObjectIdName() => $dataObjectId
            ];
        }
        return;
    }

    /**
     * Convert group dependencies to product ones
     *
     * @param array $savedDependencies
     * @param int $dataObjectId
     * @return array
     */
    protected function convertDependencies($savedDependencies, $dataObjectId)
    {
        //convert mageworx_id to magento_id on template
        $this->converter->setData($savedDependencies)
            ->setProductId($dataObjectId)
            ->setConvertTo(Converter::CONVERTING_MODE_MAGENTO)
            ->setConvertWhere(Converter::CONVERTING_ENTITY_GROUP);
        $convertedGroupDependencies = $this->converter->convert();

        //convert magento_id to mageworx_id on product
        $this->converter->setData($convertedGroupDependencies)
            ->setProductId($dataObjectId)
            ->setConvertTo(Converter::CONVERTING_MODE_MAGEWORX)
            ->setConvertWhere(Converter::CONVERTING_ENTITY_PRODUCT);
        return $this->converter->convert();
    }

    /**
     * Convert group dependencies to product ones
     *
     * @param array $savedDependencies
     * @return array
     */
    protected function processDependencies($savedDependencies)
    {
        $result = [];

        foreach ($savedDependencies as $key => $dependency) {
            if (!$this->isValidDependency($dependency)) {
                continue;
            }
            $result[$key] = $dependency;
        }

        return $result;
    }

    /**
     * Check if dependency is valid
     *
     * @param array $dependency
     * @return bool
     */
    protected function isValidDependency($dependency)
    {
        $isValueMatch = false;
        $isOptionMatch = false;
        $depOptionId = (string)$dependency[0];
        $depValueId = (string)$dependency[1];

        foreach ($this->options as $option) {
            $optionId = (string)$option['mageworx_option_id'];
            $optionRecordId = isset($option['record_id']) ? (string)$option['record_id'] : '-1';

            if (!in_array($depOptionId, [$optionId, $optionRecordId])) {
                continue;
            }
            $isOptionMatch = true;

            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $value) {
                $valueId = (string)$value['mageworx_option_type_id'];
                $valueRecordId = isset($value['record_id']) ? (string)$value['record_id'] : '-1';

                if (!in_array($depValueId, [$valueId, $valueRecordId])) {
                    continue;
                }
                $isValueMatch = true;
            }
        }

        return $isValueMatch && $isOptionMatch;
    }

    /**
     * Convert recordId to mageworxId
     *
     * @param array $savedDependencies
     * @return array
     */
    protected function convertRecordIdToMageworxId($savedDependencies)
    {
        $result = [];

        foreach ($savedDependencies as $key => $dependency) {
            $depOptionId = (string)$dependency[0];
            $depValueId = (string)$dependency[1];

            foreach ($this->options as $option) {
                $optionId = (string)$option['mageworx_option_id'];
                $optionRecordId = isset($option['record_id']) ? (string)$option['record_id'] : '-1';

                if (!in_array($depOptionId, [$optionId, $optionRecordId])) {
                    continue;
                }
                $result[$key][0] = $optionId;

                $values = isset($option['values']) ? $option['values'] : [];
                foreach ($values as $value) {
                    $valueId = (string)$value['mageworx_option_type_id'];
                    $valueRecordId = isset($value['record_id']) ? (string)$value['record_id'] : '-1';

                    if (!in_array($depValueId, [$valueId, $valueRecordId])) {
                        continue;
                    }
                    $result[$key][1] = $valueId;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($object)
    {
        return '';
    }
}
