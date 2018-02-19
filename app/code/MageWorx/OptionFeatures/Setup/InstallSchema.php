<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\ProductAttributes;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

    /**
     * InstallSchema constructor.
     * @param \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
     * @param \MageWorx\OptionBase\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\OptionBase\Model\Installer $optionBaseInstaller,
        OptionBaseHelper $helper
    ) {
        $this->optionBaseInstaller = $optionBaseInstaller;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'mageworx_optionfeatures_product_attributes'
         */
        $tableGroup = $installer->getConnection()->newTable(
            $installer->getTable(ProductAttributes::TABLE_NAME)
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Product ID'
        )->addColumn(
            Helper::KEY_ABSOLUTE_COST,
            Table::TYPE_BOOLEAN,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
            ],
            'Absolute Cost Flag'
        )->addColumn(
            Helper::KEY_ABSOLUTE_WEIGHT,
            Table::TYPE_BOOLEAN,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
            ],
            'Absolute Weight Flag'
        )->addColumn(
            Helper::KEY_ABSOLUTE_PRICE,
            Table::TYPE_BOOLEAN,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
            ],
            'Absolute Price Flag'
        )->addIndex(
            $installer->getIdxName(ProductAttributes::TABLE_NAME, ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName(
                ProductAttributes::TABLE_NAME,
                'product_id',
                'catalog_product_entity',
                $this->helper->isEnterprise() ? 'row_id' : 'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            $this->helper->isEnterprise() ? 'row_id' : 'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($tableGroup);

        $this->optionBaseInstaller->install();

        $installer->endSetup();
    }
}
