<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Helper;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\Store;

/**
 * Helper to support checkout
 */
class Checkout extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Checkout constructor.
     *
     * @param Context $context
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    /**
     * Determine if KCO checkout is enabled
     *
     * By checking if the Klarna payment method and Checkout is enabled
     *
     * @param Store    $store
     * @param Customer $customer
     *
     * @return bool
     */
    public function kcoEnabled($store = null, $customer = null)
    {
        if (!($this->klarnaCheckoutEnabled($store) && $this->klarnaPaymentEnabled($store))) {
            return false;
        }

        if (null === $customer) {
            $customer = $this->session->getCustomer();
        }

        $customerGroupId = $customer->getId() ? $customer->getGroupId() : 0;
        $disabledCustomerGroups = $this->getDisabledGroups($store);
        $disabledCustomerGroups = trim($disabledCustomerGroups);

        if ('' === $disabledCustomerGroups) {
            return true;
        }

        if (!is_array($disabledCustomerGroups)) {
            $disabledCustomerGroups = explode(',', (string)$disabledCustomerGroups);
        }

        return !in_array($customerGroupId, $disabledCustomerGroups);
    }

    /**
     * Check if Klarna checkout is enabled
     *
     * @param Store $store
     *
     * @return bool
     */
    public function klarnaCheckoutEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag('checkout/klarna_kco/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $store);
    }

    /**
     * Check if the Klarna payment method is enabled
     *
     * @param Store $store
     *
     * @return bool
     */
    public function klarnaPaymentEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag('payment/klarna_kco/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $store);
    }

    /**
     * Get disabled customer groups
     *
     * @param Store $store
     * @return string|string[]
     */
    public function getDisabledGroups($store = null)
    {
        return $this->scopeConfig->getValue('payment/klarna_kco/disable_customer_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $store);
    }
}
