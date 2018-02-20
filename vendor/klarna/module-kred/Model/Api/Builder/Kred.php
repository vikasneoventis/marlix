<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model\Api\Builder;

use Klarna\Core\Exception as KlarnaException;
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

/**
 * Api request builder for Kred
 */
class Kred extends \Klarna\Core\Model\Api\Builder
{
    /**
     * Kred constructor.
     * @param EventManager $eventManager
     * @param Collector $collector
     * @param Url $url
     * @param ConfigHelper $configHelper
     * @param ScopeConfigInterface $config
     * @param DirectoryHelper $directoryHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ObjectManager $objManager
     * @param array $data
     */
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
    ) {
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
     *
     * @return $this
     * @throws Klarna_Kco_Exception
     */
    public function generateRequest($type = self::GENERATE_TYPE_CREATE)
    {
        parent::generateRequest($type);

        switch ($type) {
            case self::GENERATE_TYPE_CREATE:
                return $this->_generateCreate();
            case self::GENERATE_TYPE_UPDATE:
                return $this->_generateUpdate();
            default:
                throw new KlarnaException('Invalid request type');
        }
    }

    /**
     * Generate create request
     *
     * @return $this
     */
    protected function _generateCreate()
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
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
            $create = $this->processAddresses($create, $store);
            if ('nortic' === $this->configHelper->getApiConfig('api_version', $store)) {
                unset($create['billing_address']); // For some reason this is read-only in SE/FI
            }
        }

        /**
         * GUI
         */
        $gui = $this->getGuiOptions($store);
        if ($gui) {
            $create['gui']['options'] = $gui;
        }
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
        $additional = $this->getMerchantCheckbox($store, $quote);
        if ($additional) {
            $create['options']['additional_checkbox'] = $additional;
        }

        if($this->configHelper->isB2bEnabled($store)){
            $create['options']['allowed_customer_types'] = ['person','organization'];
        }

        /**
         * Cart items
         */
        $create['cart']['items'] = $this->getOrderLines();

        /**
         * Merchant reference
         */
        $merchantReferences = $this->getMerchantReferences($quote);

        if ($merchantReferences->getData('merchant_reference_1')) {
            $create['merchant_reference']['orderid1'] = $merchantReferences->getData('merchant_reference_1');
        }

        if (!empty($merchantReferences['merchant_reference_2'])) {
            $create['merchant_reference']['orderid2'] = $merchantReferences->getData('merchant_reference_2');
        }

        /**
         * Merchant configuration & Urls
         */
        $urlParams = [
            '_nosid'         => true,
            '_forced_secure' => true
        ];

        $create['merchant'] = $this->processMerchantUrls($store, $urlParams);

        $this->setRequest($create, self::GENERATE_TYPE_CREATE);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getExternalMethods($enabledExternalMethods, $store)
    {
        $methods = parent::getExternalMethods($enabledExternalMethods, $store);
        if (!$methods) {
            return null;
        }
        foreach ($methods as &$method) {
            if (isset($method['redirect_url'])) {
                $method['redirect_uri'] = $method['redirect_url'];
                unset($method['redirect_url']);
            }
            if (isset($method['image_url'])) {
                $method['image_uri'] = $method['image_url'];
                unset($method['image_url']);
            }
        }
        return $methods;
    }

    /**
     * Pre-process Merchant URLs
     *
     * @param $store
     * @param $urlParams
     * @return array
     */
    public function processMerchantUrls($store, $urlParams)
    {
        $merchant_urls = new DataObject([
            'id'                => $this->configHelper->getApiConfig('merchant_id', $store),
            'terms_uri'         => rtrim($this->getTermsUrl($store), '/'),
            'checkout_uri'      => $this->url->getDirectUrl('checkout/klarna', $urlParams),
            'confirmation_uri'  => $this->url->getDirectUrl(
                'checkout/klarna/confirmation/id/{checkout.order.id}',
                $urlParams
            ),
            'push_uri'          => $this->url->getDirectUrl('klarna/api/push/id/{checkout.order.id}', $urlParams),
            'validation_uri'    => $this->url->getDirectUrl('kco/api/validate/id/{checkout.order.id}', $urlParams),
            'back_to_store_uri' => $this->url->getBaseUrl($urlParams)
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
     * Generate update request
     *
     * @return $this
     */
    protected function _generateUpdate()
    {
        $create = [
            'cart' => [
                'items' => $this->getOrderLines()
            ]
        ];

        $this->setRequest($create, self::GENERATE_TYPE_UPDATE);

        return $this;
    }

    /**
     * Fix address fields for Kred
     *
     * @param string[][] $create
     * @return string[][]
     */
    protected function processAddresses($create, $store)
    {
        foreach (['shipping_address', 'billing_address'] as $address) {
            if (!isset($create[$address])) {
                continue;
            }
            if (!isset($create[$address]['street_address'])) {
                continue;
            }
            if (isset($create[$address]['street_address2'])) { // merge street1 and street2 and unset street2
                $create[$address]['street_address'] .= ' ' . $create[$address]['street_address2'];
                unset($create[$address]['street_address2']);
                continue;
            }
            if ($this->configHelper->getApiConfig('api_version', $store) === 'dach') {
                if (preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $create[$address]['street_address'], $tmp)) {
                    $streetName = isset($tmp[1]) ? $tmp[1] : '';
                    $streetNumber = isset($tmp[2]) ? $tmp[2] : '';
                    $create[$address]['street_name'] = $streetName;
                    $create[$address]['street_number'] = $streetNumber;
                    unset($create[$address]['street_address']);
                }
            }
        }

        if (empty($create['shipping_address']) && isset($create['billing_address'])) {
            $create['shipping_address'] = $create['billing_address'];
        }
        unset($create['shipping_address']['region']); // Not used by Kred
        return $create;
    }

    /**
     * Get customer details
     *
     * @param $quote
     * @return array|null
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
