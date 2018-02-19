<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\CollectionUpdater\Option\Value;

use MageWorx\OptionBase\Model\CollectionUpdate\AbstractUpdater;
use MageWorx\OptionFeatures\Model\Image;

class BaseImage extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions($conditions)
    {
        $tableName = $this->getTableName($conditions['entity_type']);
        return [$this->getTableAlias() => $tableName];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(Image::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(Image::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        $onConditions = 'main_table.mageworx_option_type_id = ' . $this->getTableAlias() . '.mageworx_option_type_id';
        $onConditions .= ' AND ' . $this->getTableAlias() . '.base_image = "1"';
        return $onConditions;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [
            'base_image' => $this->getTableAlias() . '.value',
            'base_image_type' => $this->getTableAlias() . '.media_type'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'option_value_base_image';
    }
}
