<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Traits;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

trait Customer
{

    /**
     * @var CustomerRegistry
     */
    public $customerRegistry;

    /**
     * Determine if a customer already exist on an order or quote
     *
     * @param $quote
     * @return bool
     */
    public function _checkIfObjectCustomerAlreadyExist($quote)
    {
        $customer = $this->getCustomer($quote);
        return (bool)$customer->getId();
    }

    /**
     * Get customer object from quote or look it up using email from quote
     *
     * @param $quote
     * @return DataObject|\Magento\Customer\Model\Customer
     */
    public function getCustomer($quote)
    {
        try {
            return $this->customerRegistry->retrieveByEmail(
                $quote->getCustomerEmail(),
                $quote->getStore()->getWebsiteId()
            );
        } catch (NoSuchEntityException $e) { // Customer doesn't exist
            return new DataObject(['id' => 0]);
        }
    }
}
