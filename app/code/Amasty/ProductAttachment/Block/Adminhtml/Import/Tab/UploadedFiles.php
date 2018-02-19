<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Import\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class UploadedFiles extends \Magento\Framework\View\Element\Text\ListText implements TabInterface
{

    public function getHtmlId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Uploaded Files');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Uploaded Files');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
