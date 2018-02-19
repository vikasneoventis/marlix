<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

class Index extends \Amasty\ProductAttachment\Controller\Adminhtml\Import
{

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_ProductAttachment::import');
        $resultPage->addBreadcrumb(__('Product Attachment'), __('Product Attachment'));
        $resultPage->addBreadcrumb(__('Mass File Import'), __('Mass File Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Mass File Import'));

        return $resultPage;
    }
}
