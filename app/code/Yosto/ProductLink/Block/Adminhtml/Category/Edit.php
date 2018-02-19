<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Modify by x-mage2(Yosto)
 */
namespace Yosto\ProductLink\Block\Adminhtml\Category;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Return URL for refresh input element 'path' in form
     *
     * @return string
     */
    public function getRefreshPathUrl()
    {
        return $this->getUrl('catalog/*/refreshPath', ['_current' => true]);
    }
}