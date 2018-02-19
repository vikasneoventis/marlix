<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class PlaceOrderObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class PlaceOrderObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
        , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $order_id = $observer->getEvent()->getOrderIds()[0];
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $new_order = $this->_scopeConfig->getValue(
                Constant::NEW_ORDER_PATH,
                $this->_storeScope);
            $new_order_customer_place = $this->_scopeConfig->getValue(
                Constant::NEW_ORDER_CUSTOMER_PLACE_PATH,
                $this->_storeScope);
            $ownerPhone = $this->_scopeConfig->getValue(
                Constant::OWNER_PHONE_PATH
                , $this->_storeScope);
            $orderModel = $this->_orderFactory->create();
            $order = $orderModel->load($order_id);
            $telephone = $order->getBillingAddress()->getTelephone();
            if (trim($new_order) != '') {
                $messageSent = $this->getMessage(Constant::ORDER_ID_REPLACE, sprintf("#%s", $order->getIncrementId()), $new_order);
                foreach ($this->listPhone($ownerPhone) as $oP) {
                    if (trim($oP) != "") {
                        $this->sendSMS($phone,trim($oP),$messageSent,Constant::NEW_ORDER,$order);
                    }
                }
                $productName = '';
                foreach ($order->getAllItems() as $item) {
                    $productName = $productName .number_format($item->getData()['qty_ordered'],0). " " . $item->getData()['name'] . ',';
                }
                $productName = trim($productName, ",");
                $messageSentCustomer = $this->getMessage(Constant::ORDER_ID_REPLACE, sprintf("#%s", $order->getIncrementId()), $new_order_customer_place);
                $messageSentCustomer = $this->getMessage(
                    Constant::GRAND_TOTAL_REPLACE,
                    $order->getGrandTotal().$order->getBaseCurrencyCode() ,
                    $messageSentCustomer
                );
                $messageSentCustomer = $this->getMessage(
                    Constant::PRODUCTS,
                    $productName ,
                    $messageSentCustomer
                );
                $this->sendSMS($phone,$telephone,$messageSentCustomer,Constant::NEW_ORDER,$order);
            }
        }
    }
}