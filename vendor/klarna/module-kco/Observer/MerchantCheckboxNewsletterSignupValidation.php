<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Observer;

use Klarna\Kco\Helper\Checkout;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Validate the merchant checkbox should display for newsletter signup
 *
 * @package Klarna\Kco\Observer
 */
class MerchantCheckboxNewsletterSignupValidation implements ObserverInterface
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @var Checkout
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * MerchantCheckboxNewsletterSignupValidation constructor.
     *
     * @param Session              $session
     * @param Subscriber           $subscriber
     * @param Checkout             $helper
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        Session $session,
        Subscriber $subscriber,
        Checkout $helper,
        ScopeConfigInterface $config
    ) {
        $this->session = $session;
        $this->subscriber = $subscriber;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ((!$this->config->isSetFlag(Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG)
                && !$this->session->isLoggedIn())
            || !$this->helper->isModuleOutputEnabled('Magento_Newsletter')
        ) {
            $observer->getState()->setEnabled(false);

            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        $customerEmail = $quote->getCustomerEmail() ?: $quote->getCustomer()->getEmail();
        $newsLetter = $this->subscriber->loadByEmail($customerEmail);

        $observer->getState()->setEnabled(!$newsLetter->isSubscribed());
    }
}
