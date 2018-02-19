<?php

namespace Bss\AdminPreview\Controller\Adminhtml\Customer;

/**
 * AdminPreview LoginAsCustomer log grid action
 */
class Grid extends \Magento\Backend\App\Action
{
	/**
     * Login as customer log grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$this->_view->loadLayout(false);
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
