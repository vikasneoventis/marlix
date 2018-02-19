<?php

namespace Bss\AdminPreview\Controller\Customer;

/**
 * LoginAsCustomer proceed action
 */
class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_redirect('customer/account');
    }

}
