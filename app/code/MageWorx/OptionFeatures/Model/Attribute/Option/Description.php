<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionDescriptionFactory as DescriptionFactory;

class Description implements AttributeInterface
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
     * @var DescriptionFactory
     */
    protected $descriptionFactory;

    /**
     * @var DescriptionCollection
     */
    protected $descriptionCollection;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection
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
            'product' => OptionDescription::TABLE_NAME,
            'group' => OptionDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isOptionDescriptionEnabled()) {
            return;
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            $descriptions[$option['mageworx_option_id']] = $option['description'];
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
                OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID => $itemKey,
                OptionDescription::COLUMN_NAME_STORE_ID => $storeId,
                OptionDescription::COLUMN_NAME_DESCRIPTION => $itemValue,
            ];
            $connection->insert($tableName, $data);
        }
    }

    /**
     * Delete old option description
     *
     * @param $mageworxOptionId
     * @param $storeId
     * @return void
     */
    protected function deleteOldDescription($mageworxOptionId, $storeId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($this->getTableName());
        $connection->delete(
            $tableName,
            [
                OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID . ' = ?' => $mageworxOptionId,
                OptionDescription::COLUMN_NAME_STORE_ID . '  = ?' => $storeId,
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
