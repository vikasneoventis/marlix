<?php

namespace Bss\AdminPreview\Controller\Customer;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $login = $this->_initLogin();
        if (!$login) {
            $this->_redirect('/');
            return;
        }

        try {
            $login->authenticateCustomer();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->messageManager->addSuccess(
            __('You are logged in as customer: %1', $login->getCustomer()->getName())
        );

        $this->_redirect('*/*/proceed');
    }

    /**
     * Init login info
     * @return false || \Bss\AdminPreview\Model\Login
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            $this->messageManager->addError(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $login = $this->_objectManager
            ->create('\Bss\AdminPreview\Model\Login')
            ->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            $this->messageManager->addError(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }

}
