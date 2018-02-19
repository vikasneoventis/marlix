<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\Observer;

class AddGroupSelect implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Add child block to product options tab
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $observer->getFullActionName();

        if ($fullActionName != 'catalog_product_options') {
            return;
        }

        $layout = $observer->getLayout();
        $block = $layout->getBlock('admin.product.options');
        if ($block && ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options)) {
            $block->addChild(
                'mageworx_option_groups',
                'MageWorx\OptionTemplates\Block\Adminhtml\Product\Edit\Tab\GroupSelect'
            );
        }
    }
}
