<?php

namespace Potato\ImageOptimization\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable('potato_image_optimization_image'),
            'error_type',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null,
                'nullable' => true,
                'comment' => 'Error type'
            ]
        );
        $setup->endSetup();
    }
}
