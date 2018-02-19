<?php
/**
 * Copyright � 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\TwilioSMSNotification\Helper;

/**
 * Class Data
 * @package Yosto\TwilioSMSNotification\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    public function getAccountSid($storeId = null)
    {
        return $this->scopeConfig->getValue
        (
            'twiliosmsnotification/twilioconfig/account_sid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getAccountToken($storeId = null)
    {
        return $this->scopeConfig->getValue
        (
            'twiliosmsnotification/twilioconfig/account_token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getPhone($storeId = null)
    {
        return $this->scopeConfig->getValue
        (
            'twiliosmsnotification/twilioconfig/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );

    }

    public function getYourPhone($storeId = null)
    {
        return $this->scopeConfig->getValue
        (
            'twiliosmsnotification/twilioconfig/your_phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }
}