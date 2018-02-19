<?php

namespace Amasty\Checkout\Block\Adminhtml\Field;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;

class Edit extends FormContainer
{
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_field';
        $this->_blockGroup = 'Amasty_Checkout';

        parent::_construct();

        $this->buttonList->remove('reset');
    }

    public function getHeaderText()
    {
        return __('Manage Checkout Fields');
    }
}
