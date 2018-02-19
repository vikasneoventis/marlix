<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

use Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

/**
 * Class Grid
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS
 */
class Grid extends TwilioSMS
{
    public function execute()
    {
        return $this->_resultPageFactory->create();
    }
}