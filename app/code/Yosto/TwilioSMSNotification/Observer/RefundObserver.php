<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class RefundObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class RefundObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        $orderModel = $this->_orderFactory->create();
        if ($isEnable == Constant::IS_ENABLE) {
            $creditMemo = $observer->getEvent()->getCreditmemo();
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $refund = $this->_scopeConfig->getValue(
                Constant::REFUND_CREDITMEMO_PATH,
                $this->_storeScope);
            $order = $orderModel->load($creditMemo->getOrderId());
            $telephone = $order->getBillingAddress()->getTelephone();
            if (trim($refund) != '') {
                $messageSent = $this->getMessage(
                    Constant::ORDER_ID_REPLACE,
                    sprintf("#%s", $order->getIncrementId()),
                    $refund
                );
                $messageSent = $this->getMessage(
                    Constant::GRAND_TOTAL_REPLACE,
                    $order->getGrandTotal().$creditMemo->getBaseCurrencyCode(),
                    $messageSent
                );
                $this->sendSMS($phone, $telephone, $messageSent, Constant::REFUND_CREDITMEMO,$order);
            }
        }
    }
}