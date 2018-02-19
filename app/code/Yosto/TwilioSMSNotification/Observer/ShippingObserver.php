<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class ShippingObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class ShippingObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $shipment = $observer->getEvent()->getShipment();
            $telephone = $shipment->getBillingAddress()->getTelephone();
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $new_shipment = $this->_scopeConfig->getValue(
                Constant::SHIPMENT_CREATED_PATH
                , $this->_storeScope);
            if (trim($new_shipment) != '') {
                $messageSent = $this->getMessage(
                    Constant::ORDER_ID_REPLACE,
                    sprintf("#%s", $shipment->getOrder()->getIncrementId()),
                    $new_shipment
                );
                $messageSent = $this->getMessage(
                    Constant::GRAND_TOTAL_REPLACE,
                    $shipment->getOrder()->getGrandTotal().$shipment->getOrder()->getBaseCurrencyCode() ,
                    $messageSent
                );
                $this->sendSMS($phone,$telephone,$messageSent,Constant::SHIPMENT_CREATED, $shipment->getOrder());
            }
        }
    }
}