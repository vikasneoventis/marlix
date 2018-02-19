<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model;


use Magento\Framework\Model\AbstractModel;

/**
 * Class CommonLog
 * @package Yosto\TwilioSMSNotification\Model
 */
class CommonLog extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Yosto\TwilioSMSNotification\Model\ResourceModel\CommonLog');
    }
}