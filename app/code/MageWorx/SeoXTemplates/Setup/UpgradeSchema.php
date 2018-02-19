<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<') && $this->productMetadata->getEdition() == 'Enterprise') {
            $this->modifyForeignKeyForEnterpriseEdition($installer);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->installCategoryFilterTables($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function modifyForeignKeyForEnterpriseEdition($installer)
    {
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mageworx_seoxtemplates_template_relation_product'),
            $installer->getFkName(
                'mageworx_seoxtemplates_template_relation_product',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            )
        );
        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                'mageworx_seoxtemplates_template_relation_product',
                'product_id',
                'sequence_product',
                'sequence_value'
            ),
            $installer->getTable('mageworx_seoxtemplates_template_relation_product'),
            'product_id',
            $installer->getTable('sequence_product'),
            'sequence_value',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mageworx_seoxtemplates_template_relation_category'),
            $installer->getFkName(
                'mageworx_seoxtemplates_template_relation_category',
                'category_id',
                'catalog_category_entity',
                'entity_id'
            )
        );
        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                'mageworx_seoxtemplates_template_relation_category',
                'category_id',
                'sequence_catalog_category',
                'sequence_value'
            ),
            $installer->getTable('mageworx_seoxtemplates_template_relation_category'),
            'category_id',
            $installer->getTable('sequence_catalog_category'),
            'sequence_value',
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function installCategoryFilterTables($installer)
    {
        /**
         * Create table 'mageworx_seoxtemplates_template_category_filter'
         */
        $tableTemplateCategory = $installer->getConnection()
            ->newTable($installer->getTable('mageworx_seoxtemplates_template_categoryfilter'))
            ->addColumn('template_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ], 'Template ID')

            ->addColumn('attribute_id', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Category Attribute ID')

            ->addColumn('type_id', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Template Type')

            ->addColumn('name', Table::TYPE_TEXT, 255, [
                'nullable'  => false,
            ], 'Template Name')

            ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Store ID')

            ->addColumn('code', Table::TYPE_TEXT, '64k', [
                'nullable'  => false,
            ], 'Template Code')

            ->addColumn('assign_type', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Assign Type')

            ->addColumn('priority', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Priority')

            ->addColumn('date_modified', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
            ], 'Last Modify Date')

            ->addColumn('date_apply_start', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
            ], 'Last Apply Start Date')

            ->addColumn('date_apply_finish', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
            ], 'Last Apply Finish Date')

            ->addColumn('scope', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
                'default'  => 1,
            ], 'Scope')

            ->addColumn('is_use_cron', Table::TYPE_SMALLINT, null, [
                'unsigned'  => true,
                'nullable'  => false,
                'default'  => 2,
            ], 'Is Use Cron')

            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_seoxtemplates_template_categoryfilter',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_seoxtemplates_template_categoryfilter',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Template Category Filter Table (created by MageWorx SeoXTemplates extension)');

        $installer->getConnection()->createTable($tableTemplateCategory);

        /**
         * Create table 'mageworx_seoxtemplates_template_relation_categoryfilter'
         */
        $tableTemplateCategoryRelation  = $installer->getConnection()
            ->newTable($installer->getTable('mageworx_seoxtemplates_template_relation_categoryfilter'))
            ->addColumn('id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ], 'ID')

            ->addColumn('template_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Template ID')

            ->addColumn('category_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
            ], 'Category ID')

            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_seoxtemplates_template_relation_categoryfilter',
                    'template_id',
                    'mageworx_seoxtemplates_template_categoryfilter',
                    'template_id'
                ),
                'template_id',
                $installer->getTable('mageworx_seoxtemplates_template_categoryfilter'),
                'template_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Template Category To Category Link Table (created by MageWorx SeoXTemplates extension)');

            $tableTemplateCategoryRelation->addForeignKey(
                $installer->getFkName(
                    'mageworx_seoxtemplates_template_relation_categoryfilter',
                    'category_id',
                    'catalog_category_entity',
                    'entity_id'
                ),
                'category_id',
                $installer->getTable('catalog_category_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($tableTemplateCategoryRelation);
    }
}
