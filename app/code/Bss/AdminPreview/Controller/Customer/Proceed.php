<?php

namespace Bss\AdminPreview\Controller\Customer;

/**
 * LoginAsCustomer proceed action
 */
class Proceed extends \Magento\Framework\App\Action\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $title = __('Login As Customer ');
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }

}
