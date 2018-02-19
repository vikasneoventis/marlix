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

use Klarna\Core\Exception as KlarnaException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\Subscriber;

class MerchantCheckboxNewsletterSignup implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $messageMananger;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function __construct(CheckoutSession $checkoutSession, CustomerSession $customerSession, Url $url, CustomerRegistry $customerRegistry, ScopeConfigInterface $config, ManagerInterface $messageMananger, Subscriber $subscriber)
    {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->customerRegistry = $customerRegistry;
        $this->config = $config;
        $this->messageMananger = $messageMananger;
        $this->subscriber = $subscriber;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        if ($observer->getChecked() && ($email = ($quote->getCustomerEmail() ?: $quote->getCustomer()->getEmail()))) {
            try {
                if (!\Zend_Validate::is($email, 'EmailAddress')) {
                    new KlarnaException(__('Please enter a valid email address.'));
                }

                if (!$this->config->isSetFlag(Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG)
                    && !$this->customerSession->isLoggedIn()
                ) {
                    new KlarnaException(__(
                        'Sorry, but administrator denied subscription for guests. Please <a href="%1">register</a>.',
                        $this->url->getRegisterUrl()
                    ));
                }

                $status = $this->subscriber->subscribe($email);
                if ($status === Subscriber::STATUS_NOT_ACTIVE) {
                    $this->messageMananger->addSuccess(__('Confirmation request has been sent.'));
                } else {
                    $this->messageMananger->addSuccess(__('Thank you for your subscription.'));
                }
            } catch (KlarnaException $e) {
                $this->messageMananger->addException(
                    $e,
                    __(
                        'There was a problem with the subscription: %1',
                        $e->getMessage()
                    )
                );
            } catch (\Exception $e) {
                $this->messageMananger->addException($e, __('There was a problem with the subscription.'));
            }
        }
    }
}
