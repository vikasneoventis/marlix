<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use Yosto\TwilioSMSNotification\Model\TwilioSMSFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Class TwilioSend
 * @package Yosto\TwilioSMSNotification\Helper
 */
class TwilioSend
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * @var TwilioSMSFactory
     */
    protected $_twilioSMSFactory;
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * TwilioSend constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param TwilioSMSFactory $twilioSMSFactory
     * @param DateTime $date
     * @param OrderFactory $orderFactory
     */
    public function __construct
    (
        ScopeConfigInterface $scopeConfig,
        TwilioSMSFactory $twilioSMSFactory,
        DateTime $date,
        OrderFactory $orderFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_twilioSMSFactory = $twilioSMSFactory;
        $this->_date = $date;
        $this->_orderFactory = $orderFactory;
        $this->_objectManager = $objectmanager;
    }

    /**
     * @param $twilioPhone
     * @param $userPhone
     * @param $messageSent
     * @param $category
     */
    function sendSMS($twilioPhone,$userPhone,$messageSent,$category, $order)
    {
        $sid = $this->_scopeConfig->getValue(
            Constant::ACCOUNT_SID_PATH
            , $this->_storeScope);
        $token = $this->_scopeConfig->getValue(
            Constant::ACCOUNT_TOKEN_PATH
            , $this->_storeScope);
        $client = new \Services_Twilio($sid, $token);

        $number = $userPhone;
        try {
            if ($order != null) {
                $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
                $quote = $quoteRepository->get($order->getQuoteId());
                $shippingAddress = $quote->getShippingAddress();
                $countryCode = $shippingAddress->getData('country_id');
                $clientNum = new \Lookups_Services_Twilio($sid, $token);
                $number = $clientNum->phone_numbers->get($userPhone, array("CountryCode" => $countryCode))->phone_number;
            }

            $message = $client->account->messages->create(array(
                Constant::FROM => $twilioPhone, // From a valid Twilio number
                Constant::TO => $number, // Text this number
                Constant::BODY => $messageSent,
            ));
            $twilioModel = $this->_twilioSMSFactory->create();
            $twilioModel->setCategory($category);
            $twilioModel->setTime($this->_date->gmtDate());
            $twilioModel->setPhoneList($userPhone);
            $twilioModel->setMessage($messageSent);
            $twilioModel->save();
        } catch (\Services_Twilio_RestException $e) {
        }
    }
    protected function getMessage($replace, $object, $message)
    {
        return str_replace($replace, $object, $message);
    }
    function listPhone($phone)
    {
        return explode(",", $phone);
    }
}