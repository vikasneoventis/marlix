<?php

/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Setup;

use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Yosto\ImageProductSlide\Helper\Constant;

/**
 * Class InstallSchema
 * @package Yosto\ImageProductSlide\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup, ModuleContextInterface $context
    ) {
        $context->getVersion();
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table Slide config
         */
        if ($installer->getConnection()
            ->isTableExists($installer->getTable(Constant::SLIDE_IMAGE_TABLE)) != true
        ) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(Constant::SLIDE_IMAGE_TABLE))
                ->addColumn(
                    Constant::SLIDE_IMAGE_ID,
                    Table::TYPE_INTEGER,
                    null,
                    [
                        Constant::IS_IDENTITY => true,
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true,
                        Constant::IS_PRIMARY => true
                    ],
                    'Slide image config primary key'
                )
                ->addColumn(
                    Constant::ANIMATION_SPEED,
                    Table::TYPE_TEXT,
                    255,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Set the speed of animations, in milliseconds'
                )
                ->addColumn(
                    Constant::SLIDESHOW_SPEED,
                    Table::TYPE_TEXT,
                    255,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Set the speed of the slideshow cycling, in milliseconds'
                )
                ->addColumn(
                    Constant::DIRECTION,
                    Table::TYPE_TEXT,
                    255,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Select the sliding direction, horizontal or vertical'
                )
                ->addColumn(
                    Constant::ANIMATION,
                    Table::TYPE_TEXT,
                    255,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Select your animation type, fade or slide'
                )
                ->addColumn(
                    Constant::REVERSE,
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true
                    ],
                    'Reverse the animation direction'
                )
                ->addColumn(
                    Constant::PAUSE_ON_ACTION,
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true
                    ],
                    'Pause the slideshow when interacting with'
                    .' control elements, highly recommended'
                )
                ->addColumn(
                    Constant::PAUSE_ON_HOVER,
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true
                    ],
                    'Pause the slideshow when hovering over slider,'
                    .' then resume when no longer hovering'
                )
                ->addColumn(
                    Constant::RANDOMIZE,
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true
                    ],
                    'Randomize slide order'
                )
                ->setComment(Constant::SLIDE_TABLE_COMMENT)
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}