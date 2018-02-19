<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Report;

class Clear extends \Amasty\ProductAttachment\Controller\Adminhtml\Report
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->_objectManager->get('Amasty\ProductAttachment\Model\ResourceModel\Stat')->truncate();

            $this->messageManager->addSuccess(__('Downloads report has been cleared.'));
            return $resultRedirect->setPath('*/*/downloads');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/downloads');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::downloads');
    }
}
