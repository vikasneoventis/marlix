<?php

namespace Bss\AdminPreview\Controller\Adminhtml\Customer;

/**
 * Adminpreview LoginAsCustomer log action
 */
class Index extends \Magento\Backend\App\Action
{
	/**
     * Login as customer log
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_objectManager
            ->create('\Bss\AdminPreview\Model\Login')
            ->deleteNotUsed();

        $this->_view->loadLayout();
        $this->_setActiveMenu('Bss_AdminPreview::login_log');
        $title = __('Login As Customer Log ');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdminPreview::login_log');
    }
}
