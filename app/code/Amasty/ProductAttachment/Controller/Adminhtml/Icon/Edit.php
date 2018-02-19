<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

class Edit extends \Amasty\ProductAttachment\Controller\Adminhtml\Icon
{

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $iconId = (int) $this->getRequest()->getParam('id');
        $icon = $this->createIconModel();
        $icon->load($iconId);


        if ($iconId != 0 && $icon && !$icon->getId()) {
            $this->messageManager->addError(__('This icon no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/d');
        }

        $this->registry->register('amfile_icon_id', $icon->getId());
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('amfile_icon_edit');
        $resultPage->setActiveMenu('Amasty_ProductAttachment::icon');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Attachment Icon'));
        $resultPage->getConfig()->getTitle()->prepend(
            __(sprintf('%s Icon', $icon->getId() ? 'Edit' : 'New')));

        return $resultPage;
    }
}
