<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Observer;

use Yosto\TwilioSMSNotification\Helper\Constant;
use Yosto\TwilioSMSNotification\Helper\TwilioSend;

/**
 * Class InvoiceCreatedObserver
 * @package Yosto\TwilioSMSNotification\Observer
 */
class InvoiceCreatedObserver extends TwilioSend implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->_scopeConfig->getValue(
            Constant::ENABLE_PATH
            , $this->_storeScope);
        if ($isEnable == Constant::IS_ENABLE) {
            $invoice = $observer->getEvent()->getInvoice();
            $telephone = $invoice->getBillingAddress()->getTelephone();
            $phone = $this->_scopeConfig->getValue(
                Constant::TWILIO_PHONE_PATH
                , $this->_storeScope);
            $new_invoice = $this->_scopeConfig->getValue(
                Constant::INVOICE_CREATED_PATH
                , $this->_storeScope);
            if (trim($new_invoice) != '') {
                $messageSent = $this->getMessage(
                    Constant::ORDER_ID_REPLACE,
                    sprintf("#%s", $invoice->getOrder()->getIncrementId()),
                    $new_invoice
                );
                $messageSent = $this->getMessage(
                    Constant::GRAND_TOTAL_REPLACE,
                    $invoice->getOrder()->getGrandTotal().$invoice->getOrder()->getBaseCurrencyCode() ,
                    $messageSent
                );
                $this->sendSMS($phone,$telephone,$messageSent,Constant::INVOICE_CREATED, $invoice->getOrder());
            }
        }
    }
}