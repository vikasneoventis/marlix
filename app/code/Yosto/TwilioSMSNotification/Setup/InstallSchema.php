<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\TwilioSMSNotification\Setup;

use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Yosto\TwilioSMSNotification\Helper\Constant;

/**
 * Class InstallSchema
 * @package Yosto\TwilioSMSNotification\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install Schema
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $context->getVersion();
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table Twilio
         */
        if ($installer->getConnection()->isTableExists($installer->getTable(Constant::TWILIOSMS_TABLE)) != true) {
            $table = $installer->getConnection()->newTable($installer->getTable(Constant::TWILIOSMS_TABLE))
                ->addColumn(
                    Constant::TWILIOSMS_ID,
                    Table::TYPE_INTEGER,
                    null,
                    [
                        Constant::IS_IDENTITY => true,
                        Constant::IS_NULLABLE => false,
                        Constant::IS_UNSIGNED => true,
                        Constant::IS_PRIMARY => true
                    ],
                    'Twilio common log primary key'
                )
                ->addColumn(
                    Constant::CATEGORY,
                    Table::TYPE_TEXT,
                    255,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Category'
                )
                ->addColumn(
                    Constant::TIME,
                    Table::TYPE_DATETIME,
                    null,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Time sent'
                )
                ->addColumn(
                    Constant::PHONE_LIST,
                    Table::TYPE_TEXT,
                    5000,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Phone list'
                )
                ->addColumn(
                    Constant::MESSAGE,
                    Table::TYPE_TEXT,
                    2000,
                    [
                        Constant::IS_NULLABLE => false
                    ],
                    'Message'
                )
                ->setComment(Constant::TWILIOSMS_TABLE_COMMENT)
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}