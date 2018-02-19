<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class OrderCancelObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class OrderCancelObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $item = $observer->getEvent()->getItem();
            $telephone = $item->getOrder()->getBillingAddress()->getTelephone();
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $order_cancelled = $this->_scopeConfig->getValue(
                Constant::ORDER_CANCELED_PATH
                , $this->_storeScope);
            if (trim($order_cancelled) != '') {
                $messageSent = $this->getMessage(
                    Constant::ORDER_ID_REPLACE,
                    sprintf("#%s", $item->getOrder()->getIncrementId()),
                    $order_cancelled);
                $this->sendSMS($phone, $telephone, $messageSent, Constant::ORDER_CANCELED,$item->getOrder());
            }
        }
    }
}