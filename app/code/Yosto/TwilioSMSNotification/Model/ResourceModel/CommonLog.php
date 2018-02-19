<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Yosto\TwilioSMSNotification\Helper\Constant;

/**
 * Class CommonLog
 * @package Yosto\TwilioSMSNotification\Model\ResourceModel
 */
class CommonLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(Constant::TWILIOSMS_TABLE, Constant::TWILIOSMS_ID);
    }

}