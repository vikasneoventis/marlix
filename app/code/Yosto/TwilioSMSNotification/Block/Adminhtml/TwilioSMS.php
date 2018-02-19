<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class TwilioSMS
 * @package Yosto\TwilioSMSNotification\Block\Adminhtml
 */
class TwilioSMS extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Yosto_TwilioSMSNotification';
        $this->_headerText = __('All Logs');
        parent::_construct();
        $this->removeButton('add');
    }
}