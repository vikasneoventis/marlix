<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model\ResourceModel\TwilioSMS;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Yosto\TwilioSMSNotification\Model\ResourceModel\TwilioSMS
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            Constant::TWILIOSMS_MODEL,
            Constant::TWILIOSMS_RESOURCE_MODEL
        );
    }
}