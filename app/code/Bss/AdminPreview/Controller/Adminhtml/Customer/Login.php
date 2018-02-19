<?php

namespace Bss\AdminPreview\Controller\Adminhtml\Customer;

/**
 * LoginAsCustomer login action
 */
class Login extends \Magento\Backend\App\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');

        $login = $this->_objectManager
            ->create('\Bss\AdminPreview\Model\Login')
            ->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
        }

        $user = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        $login->generate($user->getId());

        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore($customer->getStoreId());
        $url = $this->_objectManager->get('Magento\Framework\Url')
            ->setScope($store);

        $redirectUrl = $url->getUrl('adminpreview/customer/index', ['secret' => $login->getSecret(), '_nosid' => true]);

        $this->getResponse()->setRedirect($redirectUrl);
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdminPreview::login_button');
    }
}
