<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Amasty\ProductAttachment\Helper\Pub\Deploy
     */
    protected $pubDeployer;

    public function __construct(
        \Amasty\ProductAttachment\Helper\Pub\Deploy $pubHelper
    ) {
        $this->pubDeployer = $pubHelper;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()->insertMultiple(
            $installer->getTable('amasty_file_icon'),
            [
                ['type' => 'flash',  'image' => 'fla-24_32.png'],
                ['type' => 'ini',    'image' => 'ini-24_32.png'],
                ['type' => 'jpg',    'image' => 'jpeg-24_32.png'],
                ['type' => 'mp3',    'image' => 'mp3-24_32.png'],
                ['type' => 'readme', 'image' => 'readme-24_32.png'],
                ['type' => 'txt',    'image' => 'text-24_32.png'],
                ['type' => 'zip',    'image' => 'zip-24_32.png'],
                ['type' => 'avi',    'image' => 'avi-24_32.png'],
            ]
        );
        $installer->endSetup();

        $this->pubDeployer->deployPubFolder();
    }
}
