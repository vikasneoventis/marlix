<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */


namespace Amasty\DuplicateCategories\Block\Adminhtml\Category\Duplicate;

class Categories extends \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
{
    protected $_categoryIds;
    protected $_selectedNodes = null;

    protected function _prepareLayout()
    {
        $this->setTemplate('Amasty_DuplicateCategories::category/duplicate/categories.phtml');
    }

    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

}
