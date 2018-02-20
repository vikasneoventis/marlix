<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Model\Api\Builder;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\Api\Builder;
use Klarna\Core\Model\Checkout\Orderline\Collector;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\Url;
use Magento\Quote\Model\Quote as MageQuote;

class Kasper extends \Klarna\Core\Model\Api\Builder
{
    public function __construct(
        EventManager $eventManager,
        Collector $collector,
        Url $url,
        ConfigHelper $configHelper,
        ScopeConfigInterface $config,
        DirectoryHelper $directoryHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ObjectManager $objManager,
        array $data = []
    )
    {
        parent::__construct(
            $eventManager,
            $collector,
            $url,
            $configHelper,
            $config,
            $directoryHelper,
            $coreDate,
            $dateTime,
            $objManager,
            $data
        );
        $this->prefix = 'kco';
    }

    /**
     * Generate KCO request
     *
     * @param string $type
     * @return $this
     */
    public function generateRequest($type = self::GENERATE_TYPE_CREATE)
    {
        $this->resetOrderLines();
        parent::generateRequest($type);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getObject();
        $store = $quote->getStore();
        $create = [
            'purchase_country'  => $this->directoryHelper->getDefaultCountry($store),
            'purchase_currency' => $quote->getBaseCurrencyCode(),
            'locale'            => str_replace('_', '-', $this->configHelper->getLocaleCode()),
        ];

        /**
         * Pre-fill customer details
         */
        if ($this->getConfigFlag('merchant_prefill', $store)) {
            $create = $this->prefill($create, $quote, $store);
        }

        /**
         * GUI
         */
        $create['gui']['options'] = $this->getGuiOptions($store);

        /**
         * External payment methods
         */
        $create['external_payment_methods'] = $this->getExternalMethods(
            $this->configHelper->getPaymentConfig('external_payment_methods', $store),
            $store
        );

        /**
         * Options
         */
        $create['options'] = $this->getOptions($store);

        /**
         * Merchant checkbox
         */
        $create['options']['additional_checkbox'] = $this->getMerchantCheckbox($store, $quote);

        /**
         * Shipping methods drop down
         */
        if ($this->configHelper->getShippingInIframe($store)) {
            $create['shipping_options'] = $this->_getShippingMethods($quote);
        }

        /**
         * Totals
         */
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $create['order_amount'] = $this->configHelper->toApiFloat($address->getBaseGrandTotal());
        $create['order_lines'] = $this->getOrderLines();
        $create['order_tax_amount'] = $this->configHelper->toApiFloat($address->getBaseTaxAmount());

        if ($shippingCountries = $this->configHelper->getPaymentConfig('shipping_countries', $store)) {
            $create['shipping_countries'] = explode(',', $shippingCountries);
        }

        /**
         * Merchant reference
         */
        $merchantReferences = $this->getMerchantReferences($quote);

        if ($merchantReferences->getData('merchant_reference_1')) {
            $create['merchant_reference1'] = $merchantReferences->getData('merchant_reference_1');
        }

        if (!empty($merchantReferences['merchant_reference_2'])) {
            $create['merchant_reference2'] = $merchantReferences->getData('merchant_reference_2');
        }

        /**
         * Urls
         */
        $urlParams = [
            '_nosid' => true,
            '_forced_secure' => true
        ];

        $create['merchant_urls'] = $this->processMerchantUrls($store, $urlParams);

        $this->setRequest($create, $type);

        return $this;
    }

    public function getOptions($store)
    {
        $options = parent::getOptions($store);
        $options['require_validate_callback_success'] = true;
        $options['title_mandatory'] = $this->getConfigFlag('title_mandatory', $store) &&
            $this->configHelper->getTitleMandatorySupport($store);
        $options['shipping_in_iframe'] = $this->configHelper->getShippingInIframe($store);
        if ($this->configHelper->isB2bEnabled($store)) {
            $options['allowed_customer_types'] = ['person', 'organization'];
        }
        return $options;
    }

    /**
     * Get available shipping methods for a quote for the api init
     *
     * @param MageQuote $quote
     *
     * @return array
     */
    protected function _getShippingMethods(MageQuote $quote)
    {
        $rates = [];
        if ($quote->isVirtual()) {
            return $rates;
        }

        /** @var \Magento\Quote\Model\Quote\Address\Rate $rate */
        foreach ($quote->getShippingAddress()->getAllShippingRates() as $rate) {
            if (!$rate->getCode() || !$rate->getMethodTitle()) {
                continue;
            }

            $rates[] = [
                'id'          => $rate->getCode(),
                'name'        => $rate->getMethodTitle(),
                'price'       => $this->configHelper->toApiFloat($rate->getPrice()),
                'promo'       => '',
                'tax_amount'  => 0,
                'tax_rate'    => 0,
                'description' => $rate->getMethodDescription(),
                'preselected' => $rate->getCode() == $quote->getShippingAddress()->getShippingMethod()
            ];
        }
        return $this->configHelper->removeDuplicates($rates);
    }

    /**
     * Pre-process Merchant URLs
     *
     * @param $store
     * @param $urlParams
     * @return mixed
     */
    public function processMerchantUrls($store, $urlParams)
    {
        $merchant_urls = new DataObject([
            'terms' => $this->getTermsUrl($store),
            'checkout' => $this->url->getDirectUrl('checkout/klarna', $urlParams),
            'confirmation' => $this->url->getDirectUrl(
                'checkout/klarna/confirmation/id/{checkout.order.id}',
                $urlParams
            ),
            'push'           => $this->url->getDirectUrl('kco/api/disabled', $urlParams),
            'address_update' => $this->url->getDirectUrl('kco/api/addressUpdate/id/{checkout.order.id}', $urlParams),
            'validation'     => $this->url->getDirectUrl('kco/api/validate/id/{checkout.order.id}', $urlParams),
            'notification'   => $this->url->getDirectUrl('kco/api/disabled', $urlParams)
        ]);

        $this->eventManager->dispatch(
            'klarna_prepare_merchant_urls',
            [
                'urls'       => $merchant_urls,
                'url_params' => new DataObject($urlParams)
            ]
        );

        $this->eventManager->dispatch(
            'kco_prepare_merchant_urls',
            [
                'urls'       => $merchant_urls,
                'url_params' => new DataObject($urlParams)
            ]
        );

        return $merchant_urls->toArray();
    }

    /**
     * Get customer details
     *
     * @param MageQuote $quote
     * @return array
     */
    public function getCustomerData($quote)
    {
        $store = $quote->getStore();
        $customerData = [];
        if (!$quote->getCustomerIsGuest()) {
            $customer = $quote->getCustomer();
            if ($this->configHelper->isB2bCustomer($customer->getId(),$store)) {
                $customerData['type'] = 'organization';
                $organizationId = $this->configHelper->getBusinessIdAttributeValue($customer->getId(),$store);
                if(!empty($organizationId)){
                    $customerData['organization_registration_id'] = $organizationId;
                }
            }
            if ($quote->getCustomerDob()) {
                $customerData = [
                    'date_of_birth' => $this->coreDate->date('Y-m-d', $quote->getCustomerDob())
                ];
            }
        }
        return $customerData;
    }
}

