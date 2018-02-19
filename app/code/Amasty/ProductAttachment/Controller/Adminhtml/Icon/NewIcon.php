<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

class NewIcon extends \Amasty\ProductAttachment\Controller\Adminhtml\Icon
{

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
