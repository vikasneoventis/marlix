<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Setup;

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
         * Create table 'klarna_kco_quote'
         */
        $table = $installer->getConnection()
                           ->newTable($installer->getTable('klarna_kco_quote'))
                           ->addColumn(
                               'kco_quote_id',
                               Table::TYPE_INTEGER,
                               null,
                               [
                                           'identity' => true,
                                           'unsigned' => true,
                                           'nullable' => false,
                                           'primary'  => true,
                                       ],
                               'Checkout Id'
                           )
                           ->addColumn(
                               'klarna_checkout_id',
                               Table::TYPE_TEXT,
                               255,
                               [],
                               'Klarna Checkout Id'
                           )
                           ->addColumn(
                               'is_active',
                               Table::TYPE_SMALLINT,
                               null,
                               [
                                           'nullable' => false,
                                           'default'  => '0',
                                       ],
                               'Is Active'
                           )
                           ->addColumn(
                               'quote_id',
                               Table::TYPE_INTEGER,
                               null,
                               [
                                           'unsigned' => true,
                                           'nullable' => false,
                                       ],
                               'Quote Id'
                           )
                           ->addColumn(
                               'is_changed',
                               Table::TYPE_SMALLINT,
                               null,
                               [
                                           'nullable' => false,
                                           'default'  => '0',
                                       ],
                               'Is Changed'
                           )
                           ->addForeignKey(
                               $installer->getFkName(
                                   'klarna_kco_quote',
                                   'quote_id',
                                   'quote',
                                   'entity_id'
                               ),
                               'quote_id',
                               $installer->getTable('quote'),
                               'entity_id',
                               Table::ACTION_CASCADE,
                               Table::ACTION_CASCADE
                           )
                           ->setComment('Klarna Checkout Quote');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
