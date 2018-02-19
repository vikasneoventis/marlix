<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class OrderStateChangeObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class OrderStateChangeObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $order = $observer->getEvent()->getOrder();
            $transport = $observer->getEvent()->getTrabsport();
        }
    }
}