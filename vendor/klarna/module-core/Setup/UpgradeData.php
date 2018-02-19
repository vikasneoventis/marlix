<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades DB data for a module
     *
     * @param ModuleDataSetupInterface $installer
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $configTable = $installer->getTable('core_config_data');

            $oldKeys = [
                'payment/klarna_kco/merchant_id',
                'payment/klarna_kco/shared_secret',
                'payment/klarna_kco/api_version',
                'payment/klarna_kco/test_mode',
                'payment/klarna_kco/debug',
            ];

            $newKeys = [
                'klarna/api/merchant_id',
                'klarna/api/shared_secret',
                'klarna/api/api_version',
                'klarna/api/test_mode',
                'klarna/api/debug',
            ];
            foreach ($oldKeys as $id => $oldKey) {
                $newKey = $newKeys[$id];
                $installer->getConnection()->update($configTable, ['path' => $newKey], "`path`='{$oldKey}'");
            }

            $keys = '\'' . implode('\',\'', $oldKeys) . '\'';
            $keys = str_replace('klarna_kco', 'klarna_kp', $keys);
            $installer->getConnection()->delete($configTable, "`path` in ({$keys})");
        }
        $installer->endSetup();
    }
}
