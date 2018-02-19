<?php
namespace Amasty\Checkout\Controller\Adminhtml;

abstract class Field extends \Magento\Backend\App\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Checkout::checkout_settings_fields');
    }
}
