<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection->changeColumn(
                $setup->getTable('amasty_file_stat'),
                'downloaded_at',
                'downloaded_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'length' => null,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                    'comment' => 'Downloaded At'
                ]);
        }

        $setup->endSetup();
    }
}
