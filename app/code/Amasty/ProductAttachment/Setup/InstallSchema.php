<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Setup;

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
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('amasty_file'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'file_path',
                Table::TYPE_TEXT,
                null,
                ['default' => null, 'nullable' => false],
                'File Url'
            )
            ->addColumn(
                'file_name',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'File Name'
            )
            ->addColumn(
                'file_url',
                Table::TYPE_TEXT,
                null,
                ['default' => null, 'nullable' => false],
                'File Link'
            )
            ->addColumn(
                'file_size',
                Table::TYPE_INTEGER,
                null,
                ['default' => '0', 'nullable' => false],
                'File Size'
            )
            ->addColumn(
                'file_type',
                Table::TYPE_TEXT,
                255,
                ['default' => '', 'nullable' => false],
                'File Type'
            )
            ->addIndex(
                $installer->getIdxName('amasty_file', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_file',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_file_store'))
            ->addColumn(
               'id',
               Table::TYPE_INTEGER,
               null,
               ['identity' => true, 'unsigned' => true,
                'nullable' => false, 'primary' => true],
               'Id'
            )
            ->addColumn(
               'file_id',
               Table::TYPE_INTEGER,
               null,
               ['unsigned' => true, 'nullable' => false],
               'File Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
               'label',
               Table::TYPE_TEXT,
                255,
               ['default' => null, 'nullable' => false],
               'Label'
            )
            ->addColumn(
                'is_visible',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Visible'
            )
            ->addColumn(
                'position',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            ->addColumn(
                'show_for_ordered',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Show only if a Product has been Ordered'
            )->addColumn(
                'customer_group_is_default',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 1],
                'Customer Group is from store_id=0'
            )
            ->addIndex(
               $installer->getIdxName('amasty_file_store',
                   ['file_id', 'store_id'],
                   \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
               ),
               ['file_id', 'store_id'],
               ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
               $installer->getFkName(
                   'amasty_file_store',
                   'file_id',
                   'amasty_file',
                   'id'
               ),
               'file_id',
               $installer->getTable('amasty_file'),
               'id',
               Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_file_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_file_stat'))
            ->addColumn(
               'id',
               Table::TYPE_INTEGER,
               null,
               ['identity' => true, 'unsigned' => true,
                'nullable' => false, 'primary' => true],
               'Id'
            )
            ->addColumn(
               'file_id',
               Table::TYPE_INTEGER,
               null,
               ['unsigned' => true, 'nullable' => false],
               'File Id'
            )
            ->addColumn(
               'product_id',
               Table::TYPE_INTEGER,
               null,
               ['unsigned' => true, 'nullable' => false],
               'Product Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer ID'
            )
            ->addColumn(
                'file_path',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'File Url'
            )
            ->addColumn(
                'file_name',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'File Name'
            )
            ->addColumn(
                'downloaded_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Downloaded At'
            )
            ->addColumn(
                'rating',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rating'
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_stat', ['file_id']
               ),
               ['file_id']
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_stat', ['product_id']
               ),
               ['product_id']
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_stat', ['store_id']
               ),
               ['store_id']
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_stat', ['customer_id']
               ),
               ['customer_id']
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_stat', ['downloaded_at']
               ),
               ['downloaded_at']
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_file_icon'))
            ->addColumn(
               'id',
               Table::TYPE_INTEGER,
               null,
               ['identity' => true, 'unsigned' => true,
                'nullable' => false, 'primary' => true],
               'Id'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Type of File'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Image name'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Active'
            )
            ->addIndex(
               $installer->getIdxName(
                   'amasty_file_icon', ['is_active']
               ),
               ['is_active']
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_file_customer_group'))
            ->addColumn(
               'id',
               Table::TYPE_INTEGER,
               null,
               ['identity' => true, 'unsigned' => true,
                'nullable' => false, 'primary' => true],
               'Id'
            )
            ->addColumn(
               'file_id',
               Table::TYPE_INTEGER,
               null,
               ['unsigned' => true, 'nullable' => false],
               'File Id'
            )
            ->addColumn(
               'store_id',
               Table::TYPE_SMALLINT,
               null,
               ['unsigned' => true, 'nullable' => false, 'default' => '0'],
               'Store ID'
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'amasty_file_customer_group', ['customer_group_id']
                ),
                ['customer_group_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_file_customer_group',
                    ['file_id', 'store_id', 'customer_group_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['file_id', 'store_id', 'customer_group_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_file_customer_group',
                    'file_id',
                    'amasty_file',
                    'id'
                ),
                'file_id',
                $installer->getTable('amasty_file'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_file_customer_group', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
