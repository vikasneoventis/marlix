<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

use \Amasty\ProductAttachment\Controller\Adminhtml;

class Index extends Adminhtml\Icon
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_ProductAttachment::files');
        $resultPage->addBreadcrumb(__('Icon'), __('Icon'));
        $resultPage->addBreadcrumb(__('Icon Management'), __('Icon Management'));
        $resultPage->getConfig()->getTitle()->prepend(__('Icon Management'));

        return $resultPage;
    }
}
