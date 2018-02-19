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

use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Check if a guest checkout user already has an account. Register the order with that customer.
 *
 * @package Klarna\Kco\Observer
 */
class AssociateGuestOrderWithRegisteredCustomer implements ObserverInterface
{

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    protected $quoteResourceModel;

    /**
     * AssociateGuestOrderWithRegisteredCustomer constructor.
     *
     * @param CustomerRegistry                         $customerRegistry
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResourceModel
     * @internal param QuoteRepository $quoteRepository
     */
    public function __construct(CustomerRegistry $customerRegistry, \Magento\Quote\Model\ResourceModel\Quote $quoteResourceModel)
    {
        $this->customerRegistry = $customerRegistry;
        $this->quoteResourceModel = $quoteResourceModel;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getCheckout()->getQuote();

        if ($quote->getCustomerId()) {
            return;
        }

        $customer = $this->getCustomer($quote);
        if (($customer === null) || (empty($customer->getId()))) {
            return;
        }

        $quote->setCustomer($customer);
        $quote->setCheckoutMethod(Kco::METHOD_CUSTOMER);
        $quote->getShippingAddress()->setCustomerId($customer->getId());
        $quote->getShippingAddress()->setSaveInAddressBook(0);
        $quote->getBillingAddress()->setCustomerId($customer->getId());
        $quote->getBillingAddress()->setSaveInAddressBook(0);
        // STFU and just save the quote
        $this->quoteResourceModel->save($quote->collectTotals());
    }

    /**
     * Get customer from DB if exists, otherwise return empty object
     *
     * @param $quote
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomer($quote)
    {
        try {
            $customer = $this->customerRegistry->retrieveByEmail(
                $quote->getCustomerEmail(),
                $quote->getStore()->getWebsiteId()
            );
            return $customer->getDataModel();
        } catch (\Exception $e) {
            return null;
        }
    }
}
