<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\CollectionUpdater\Option;

use MageWorx\OptionBase\Model\CollectionUpdate\AbstractUpdater;
use MageWorx\OptionFeatures\Model\OptionDescription;

class Description extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions($conditions)
    {
        return [$this->getTableAlias() => $this->getTableName($conditions['entity_type'])];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(OptionDescription::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(OptionDescription::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        return 'main_table.mageworx_option_id = ' . $this->getTableAlias() . '.mageworx_option_id';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return ['description' => $this->getTableAlias() . '.description'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'option_description';
    }
}
