<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model\ResourceModel;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class TwilioSMS
 * @package Yosto\TwilioSMSNotification\Model\ResourceModel
 */
class TwilioSMS extends AbstractDb
{
    protected function _construct()
    {
        $this->_init
        (
            Constant::TWILIOSMS_TABLE,
            Constant::TWILIOSMS_ID
        );
    }
}