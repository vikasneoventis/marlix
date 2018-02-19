<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Plugin;


class TabPosition
{

    /**
     * @var \Amasty\ProductAttachment\Helper\Config
     */
    protected $configHelper;

    public function __construct(\Amasty\ProductAttachment\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    public function afterGetGroupChildNames(
        \Magento\Catalog\Block\Product\View\Description $block, $result)
    {
        if (!$this->configHelper->isTab()) {
            return $result;
        }

        $position = $this->configHelper->getBlockPosition();
        $sibling = $this->configHelper->getBlockSiblingTab();

        if (!in_array($sibling, $block->getChildNames())) {
            $sibling = '-';
        }
        $key = array_search('amfile.attachment', $result);

        if ($key !== false) {
            unset($result[$key]);
        }



        $myResult = [];
        $tabAdded = false;
        foreach ($result as $key => $item) {
            if ($position == 'before' && $tabAdded === false
                && ($item == $sibling || $sibling == '-')
            ) {
                $myResult[] = 'amfile.attachment';
                $tabAdded = true;
            }
            $myResult[] = $item;
            if ($position == 'after' && $tabAdded === false
                && ($item == $sibling || $sibling == '-')
            ) {
                $myResult[] = 'amfile.attachment';
                $tabAdded = true;
            }
        }
        return $myResult;
    }
}