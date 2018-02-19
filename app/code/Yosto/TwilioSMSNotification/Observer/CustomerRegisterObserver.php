<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class CustomerRegisterObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class CustomerRegisterObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $customer = $observer->getEvent()->getCustomer();
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $customer_registration = $this->_scopeConfig->getValue(
                Constant::CUSTOMER_REGISTRATION_PATH
                , $this->_storeScope);
            $ownerPhone = $this->_scopeConfig->getValue(
                Constant::OWNER_PHONE_PATH
                , $this->_storeScope);
            if(trim($customer_registration) != ""){
                $messageSent = $this->getMessage(
                    Constant::NAME_REPLACE,
                    $customer->getFirstname()." ".$customer->getLastname(),
                    $customer_registration
                );
                $messageSent = $this->getMessage(
                    Constant::EMAIL_REPLACE,
                    $customer->getEmail(),
                    $messageSent
                );
                foreach ($this->listPhone($ownerPhone) as $oP) {
                    if (trim($oP) != "") {
                        $this->sendSMS($phone,trim($oP),$messageSent,Constant::CUSTOMER_REGISTRATION, null);
                    }
                }
            }
        }
    }
}