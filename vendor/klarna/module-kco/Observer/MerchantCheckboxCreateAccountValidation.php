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

use Klarna\Kco\Traits\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Validate the merchant checkbox should display for user signup
 *
 * @package Klarna\Kco\Observer
 */
class MerchantCheckboxCreateAccountValidation implements ObserverInterface
{
    use Customer;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Registration
     */
    protected $registration;

    /**
     * MerchantCheckboxCreateAccountValidation constructor.
     *
     * @param Session          $session
     * @param Registration     $registration
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(Session $session, Registration $registration, CustomerRegistry $customerRegistry)
    {
        $this->session = $session;
        $this->registration = $registration;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerExist = $this->_checkIfObjectCustomerAlreadyExist($observer->getQuote());
        $enabled = !$customerExist && !$this->session->isLoggedIn()
            && $this->registration->isAllowed();
        $observer->getState()->setEnabled($enabled);
    }
}
