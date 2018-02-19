<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model;

use Yosto\TwilioSMSNotification\Helper\Constant;
use \Magento\Framework\Model\AbstractModel;

/**
 * Class TwilioSMS
 * @package Yosto\TwilioSMSNotification\Model
 */
class TwilioSMS extends AbstractModel
{
    public function _construct()
    {
        $this->_init(Constant::TWILIOSMS_RESOURCE_MODEL);
    }
}