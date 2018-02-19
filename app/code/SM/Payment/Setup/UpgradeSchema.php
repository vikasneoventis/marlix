<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SM\Payment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->createPaymentTable($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->dummyPayment($setup);
        }
    }

    protected function createPaymentTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_payment'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_payment')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            25,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet Id'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true,],
            'Title'
        )->addColumn(
            'payment_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true,],
            'Data'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        )->addColumn(
            'is_dummy',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Dummy'
        )->addColumn(
            'allow_amount_tendered',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Allow Amount Tendered'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    protected function dummyPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->truncateTable($paymentTable);
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'     => "cash",
                    'title'    => "Cash",
                    'is_dummy' => 1,
                    'payment_data'     => json_encode([])
                ],
                [
                    'type'     => "tyro",
                    'title'    => "Tyro Gateway",
                    'is_dummy' => 0,
                    'payment_data'     => json_encode(['mid' => 'provided by Tyro', 'tid' => 'provided by Tyro', 'api_key' => 'provided by Tyro']),
                ],
                [
                    'type'     => "credit_card",
                    'title'    => "Credit card",
                    'is_dummy' => 1,
                    'payment_data'     => json_encode([])
                ],
                [
                    'type'     => "credit_card",
                    'title'    => "Debit card",
                    'is_dummy' => 1,
                    'payment_data'     => json_encode([])
                ],
                [
                    'type'     => "credit_card",
                    'title'    => "Visa card",
                    'is_dummy' => 1,
                    'payment_data'     => json_encode([])
                ]
            ]
        );
    }
}