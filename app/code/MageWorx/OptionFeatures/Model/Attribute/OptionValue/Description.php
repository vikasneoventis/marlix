<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionTypeDescriptionFactory as DescriptionFactory;

class Description implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var DescriptionFactory
     */
    protected $descriptionFactory;

    /**
     * @var DescriptionCollection
     */
    protected $descriptionCollection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
        $this->descriptionFactory = $descriptionFactory;
        $this->descriptionCollection = $descriptionCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_DESCRIPTION;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        $map = [
            'product' => OptionTypeDescription::TABLE_NAME,
            'group' => OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isDescriptionEnabled()) {
            return;
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                $descriptions[$value['mageworx_option_type_id']] = $value['description'];
            }
        }

        $this->saveDescription($descriptions);
    }

    /**
     * Save descriptions
     *
     * @param $items
     * @return void
     */
    protected function saveDescription($items)
    {
        //$storeId = $this->helper->resolveCurrentStoreId();
        //@TODO store view assignment
        $storeId = 0;
        foreach ($items as $itemKey => $itemValue) {
            $this->deleteOldDescription($itemKey, $storeId);

            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName($this->getTableName($this->entity->getType()));
            $data = [
                'mageworx_option_type_id' => $itemKey,
                'store_id' => $storeId,
                'description' => $itemValue,
            ];
            $connection->insert($tableName, $data);
        }
    }

    /**
     * Delete old option value description
     *
     * @param $mageworxOptionTypeId
     * @param $storeId
     * @return void
     */
    protected function deleteOldDescription($mageworxOptionTypeId, $storeId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($this->getTableName());

        if ($this->entity->getType() == 'product') {
            $select = $connection->select()
                ->reset()
                ->from(['descr' => $tableName])
                ->joinLeft(
                    ['cpotv' => $this->resource->getTableName('catalog_product_option_type_value')],
                    'cpotv.mageworx_option_type_id = descr.mageworx_option_type_id',
                    []
                )
                ->where('cpotv.option_id IS NULL');
            $sql = $select->deleteFromSelect('descr');
            $connection->query($sql);
        }

        $connection->delete(
            $tableName,
            [
                'mageworx_option_type_id = ?' => $mageworxOptionTypeId,
                'store_id  = ?' => $storeId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($object)
    {
        return $object->getData($this->getName());
    }
}
