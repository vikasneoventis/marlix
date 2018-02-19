<?php

/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ImageProductSlide\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Yosto\ImageProductSlide\Helper\Constant;

/**
 * Class InstallData
 * @package Yosto\ImageProductSlide\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        ModuleDataSetupInterface $setup, ModuleContextInterface $context
    ) {
        $context->getVersion();
        $installer = $setup;
        $installer->startSetup();

        /**
         * Data install for Config table
         */
        if ($installer->getConnection()
            ->isTableExists($installer->getTable(Constant::SLIDE_IMAGE_TABLE))
        ) {
            $data = [
                [
                    Constant::ANIMATION_SPEED => '600',
                    Constant::SLIDESHOW_SPEED => '6000',
                    Constant::DIRECTION => 'horizontal',
                    Constant::REVERSE => 'false',
                    Constant::PAUSE_ON_ACTION => 'true',
                    Constant::PAUSE_ON_HOVER => 'true',
                    Constant::RANDOMIZE => 'false',
                    Constant::ANIMATION => 'slide',
                ],
            ];
            $installer->getConnection()
                ->insertMultiple($installer->getTable(Constant::SLIDE_IMAGE_TABLE), $data);
        }
        $installer->endSetup();
    }
}