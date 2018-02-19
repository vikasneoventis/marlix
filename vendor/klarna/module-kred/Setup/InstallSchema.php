<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'klarna_kco_push_queue'
         */
        $table = $installer->getConnection()
                           ->newTable($installer->getTable('klarna_kco_push_queue'))
                           ->addColumn('push_queue_id', Table::TYPE_INTEGER, null, [
                               'identity' => true,
                               'unsigned' => true,
                               'nullable' => false,
                               'primary'  => true,
                           ], 'Queue Id')
                           ->addColumn('klarna_checkout_id', Table::TYPE_TEXT, 255, [], 'Klarna Checkout Id')
                           ->addColumn('count', Table::TYPE_INTEGER, null, [
                               'unsigned' => true,
                               'nullable' => false,
                           ], 'Count')
                           ->addColumn('creation_time', Table::TYPE_TIMESTAMP, null, [
                               'nullable' => false,
                               'default'  => Table::TIMESTAMP_INIT
                           ], 'Creation Time')
                           ->addColumn('update_time', Table::TYPE_TIMESTAMP, null, [], 'Modification Time')
                           ->setComment('Klarna Checkout Push Queue');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
