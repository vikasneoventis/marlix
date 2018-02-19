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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Registration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;

/**
 * Register a new user when they check the box
 *
 * @package Klarna\Kco\Observer
 */
class MerchantCheckboxCreateAccount implements ObserverInterface
{
    use Customer;

    /**
     * @var Registration
     */
    protected $registration;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * MerchantCheckboxCreateAccount constructor.
     *
     * @param Registration                     $registration
     * @param CustomerRegistry                 $customerRegistry
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param CustomerRepositoryInterface      $customerRepository
     */
    public function __construct(
        Registration $registration,
        CustomerRegistry $customerRegistry,
        OrderCustomerManagementInterface $orderCustomerService,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->registration = $registration;
        $this->customerRegistry = $customerRegistry;
        $this->orderCustomerService = $orderCustomerService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($observer->getChecked() && $this->registration->isAllowed()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getQuote();
            /** @var OrderInterface $order */
            $order = $observer->getOrder();

            if ($quote->getCustomerId() || $this->_checkIfObjectCustomerAlreadyExist($quote)) {
                return;
            }
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->orderCustomerService->create($order->getId());
            $customer->setDob($order->getCustomerDob());
            $customer->setGender($order->getCustomerGender());
            $this->customerRepository->save($customer);
        }
    }
}
