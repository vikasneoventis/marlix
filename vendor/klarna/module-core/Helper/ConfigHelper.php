<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Helper;

use Klarna\Core\Exception as KlarnaException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Config\Processor\Placeholder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class ConfigHelper extends AbstractHelper
{
    /**
     * Payment method
     *
     * @var string
     */
    protected $code = '';

    /**
     * Observer event prefix
     *
     * @var string
     */
    protected $eventPrefix = '';

    /**
     * Configuration cache for api versions
     *
     * @var array
     */
    protected $_versionConfigCache = [];

    /**
     * @var DataInterface
     */
    protected $config;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var Placeholder
     */
    protected $placeholder;

    /**
     * Data helper constructor.
     *
     * @param Context       $context
     * @param DataInterface $config
     * @param Resolver      $resolver
     * @param Placeholder   $placeholder
     * @param string        $code
     * @param string        $eventPrefix
     */
    public function __construct(
        Context $context,
        DataInterface $config,
        Resolver $resolver,
        Placeholder $placeholder,
        $code = 'klarna_kco',
        $eventPrefix = 'kco'
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->resolver = $resolver;
        $this->placeholder = $placeholder;
        if (class_exists('\Magento\Store\Model\Config\Placeholder')) {
            // Using Object Manager because of changes made by core in 2.1.3 that are backwards incompatible with no
            // apparent workaround
            $this->placeholder = ObjectManager::getInstance()->create('\Magento\Store\Model\Config\Placeholder');
        }
        $this->code = $code;
        $this->eventPrefix = $eventPrefix;
    }

    /**
     * Get external payment details
     *
     * @param string         $code
     * @param StoreInterface $store
     *
     * @return DataObject
     */
    public function getExternalPaymentDetails($code, $store)
    {
        $options = $this->config->get(sprintf('external_payment_methods/%s', $code));
        if ($options === null) {
            $options = [];
        }
        unset($options['label']);

        foreach ($options as $option => $value) {
            if (false !== stripos($option, 'url') && !parse_url($value, PHP_URL_SCHEME)) {
                $options[$option] = $this->getProcessedUrl($value, $store);
            }
        }

        $options = array_filter($options);

        return new DataObject($options);
    }

    /**
     * Get url using url template variables
     *
     * @param string         $value
     * @param StoreInterface $store
     *
     * @return string
     */
    public function getProcessedUrl($value, $store)
    {
        $data = [
            'url' => $value,
            'web' => [
                'unsecure' => [
                    'base_url' => $this->scopeConfig->getValue(
                        'web/unsecure/base_url',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    )
                ],
                'secure'   => [
                    'base_url' => $this->scopeConfig->getValue(
                        'web/secure/base_url',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    )
                ]
            ]
        ];
        $value = $this->placeholder->process($data)['url'];

        return $this->_urlBuilder->escape($value);
    }

    /**
     * Get the order status that should be set on orders that have been processed by Klarna
     *
     * @param Store  $store
     * @param string $paymentMethod
     *
     * @return string
     */
    public function getProcessedOrderStatus($store = null, $paymentMethod = null)
    {
        return $this->getPaymentConfig('order_status', $store, $paymentMethod);
    }

    /**
     * Get payment config value
     *
     * @param string $config
     * @param Store  $store
     * @param string $paymentMethod
     *
     * @return mixed
     */
    public function getPaymentConfig($config, $store = null, $paymentMethod = null)
    {
        if (!$paymentMethod) {
            $paymentMethod = $this->code;
        }
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->getValue(sprintf('payment/' . $paymentMethod . '/%s', $config), $scope, $store);
    }

    /**
     * Prepare float for API call
     *
     * @param float $float
     *
     * @return int
     */
    public function toApiFloat($float)
    {
        return round($float * 100);
    }

    /**
     * Get the current checkout api type code
     *
     * @param Store $store
     *
     * @return string
     */
    public function getCheckoutType($store = null)
    {
        return $this->getVersionConfig($store)->getType();
    }

    /**
     * Get configuration parameters for a store
     *
     * @param Store $store
     *
     * @return DataObject
     */
    public function getVersionConfig($store = null)
    {
        $version = $this->getApiConfig('api_version', $store);

        if (!array_key_exists($version, $this->_versionConfigCache)) {
            $this->_versionConfigCache[$version] = $this->getCheckoutVersionDetails($version);
        }

        return $this->_versionConfigCache[$version];
    }

    /**
     * Get API config value
     *
     * @param string $config
     * @param Store  $store
     *
     * @return mixed
     */
    public function getApiConfig($config, $store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->getValue(sprintf('klarna/api/%s', $config), $scope, $store);
    }

    /**
     * Get api version details
     *
     * @param string $code
     *
     * @return DataObject
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    public function getCheckoutVersionDetails($code)
    {
        $options = $this->getConfig(sprintf('api_versions/%s', $code));
        if ($options === null) {
            $options = [];
        }
        if (!is_array($options)) {
            $options = [$options];
        }
        if (isset($options['options'])) {
            $options = array_merge($options, $options['options']);
            unset($options['options']);
        }
        $options['code'] = $code;

        // Start with api type global options
        $optionsObject = new DataObject($options);
        $apiTypeConfig = $this->_getApiTypeConfig($optionsObject->getType());
        $apiTypeOptions = $apiTypeConfig->getOptions();
        $apiTypeOptions['ordermanagement'] = $apiTypeConfig->getOrdermanagement();
        $options = array_merge($apiTypeOptions, $options);
        $optionsObject = new DataObject($options);

        $this->_eventManager->dispatch(
            $this->eventPrefix . '_load_version_details',
            [
                'options' => $optionsObject
            ]
        );

        return $optionsObject;
    }

    /**
     * Get Klarna config value for $key
     *
     * @param $key
     * @return mixed
     * @throws \RuntimeException
     */
    protected function getConfig($key)
    {
        return $this->config->get($key);
    }

    /**
     * Get api type configuration
     *
     * @param string $code
     *
     * @return DataObject
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    protected function _getApiTypeConfig($code)
    {
        $typeConfig = $this->getConfig(sprintf('api_types/%s', $code));
        if (!$typeConfig) {
            throw new KlarnaException(__('API type "%1" does not exist!', $code));
        }

        $configObject = new DataObject($typeConfig);

        $this->_eventManager->dispatch(
            $this->eventPrefix . '_load_api_config',
            [
                'options' => $configObject
            ]
        );

        return $configObject;
    }

    /**
     * Get order line times from klarna.xml file
     *
     * @param string $checkoutType
     * @return string[][]
     */
    public function getOrderlines($checkoutType)
    {
        return $this->getConfig(sprintf('order_lines/%s', $checkoutType));
    }

    /**
     * Determine if current store requires a separate line for tax
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getSeparateTaxLine($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getSeparateTaxLine();
    }

    /**
     * Remove duplicate items from a multidimensional array based on a supplied key
     *
     * @param array  $array
     * @param string $key
     * @return array
     */
    public function removeDuplicates(array $array, $key = 'id')
    {
        /** @noinspection CallableInLoopTerminationConditionInspection */
        // The count statement is intentional as the array's size will decrease
        for ($parent_index = 0; $parent_index < count($array); $parent_index++) {
            $duplicate = null;
            /** @noinspection CallableInLoopTerminationConditionInspection */
            // The count statement is intentional as the array's size will decrease
            for ($child_index = $parent_index + 1; $child_index < count($array); $child_index++) {
                if (strcmp($array[$child_index][$key], $array[$parent_index][$key]) === 0) {
                    $duplicate = $child_index;
                    break;
                }
            }
            if (!is_null($duplicate)) {
                array_splice($array, $duplicate, 1);
            }
        }
        return $array;
    }

    /**
     * Get the current locale code
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->resolver->getLocale();
    }

    /**
     * Get the text from a merchant checkbox method
     *
     * Will call merchant checkbox methods
     *
     * @param string $code
     *
     * @return mixed
     */
    public function getMerchantCheckboxText($code = null)
    {
        if (!$code) {
            return null;
        }

        $methodConfig = $this->getMerchantCheckboxMethodConfig($code);

        return $methodConfig->getText();
    }

    /**
     * Get merchant checkbox method configuration details
     *
     * @param string $code
     *
     * @return DataObject
     */
    public function getMerchantCheckboxMethodConfig($code)
    {
        $options = $this->config->get(sprintf('merchant_checkbox/%s', $code));
        if ($options === null) {
            $options = [];
        }
        if (!is_array($options)) {
            $options = [$options];
        }
        $options['code'] = $code;

        return new DataObject($options);
    }

    /**
     * Determine if merchant checkbox should be enabled
     *
     * @param string $code
     * @param array  $args
     *
     * @return bool
     */
    public function getMerchantCheckboxEnabled($code, $args = [])
    {
        if (!$code || -1 == $code) {
            return false;
        }

        $observer = new DataObject();
        $observer->setEnabled(true);
        $args['state'] = $observer;
        $methodConfig = $this->getMerchantCheckboxMethodConfig($code);
        $this->_eventManager->dispatch(
            $this->eventPrefix . '_' . $methodConfig->getValidationEvent(),
            $args
        );
        return $observer->getEnabled();
    }

    /**
     * Determine if current store allows shipping methods to be within the iframe
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getShippingInIframe($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getShippingInIframe();
    }

    /**
     * Determine if current store allows cart totals to be within the iframe
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getCartTotalsInIframe($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getCartTotalsInIframe();
    }

    /**
     * Determine if current store allows shipping callbacks
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getShippingCallbackSupport($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getShippingCallbackSupport();
    }

    /**
     * Determine if current store supports the use of the merchant checkbox feature
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getMerchantCheckboxSupport($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getMerchantCheckboxSupport();
    }

    /**
     * Determine if current store supports the use of date of birth mandatory
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getDateOfBirthMandatorySupport($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getDateOfBirthMandatorySupport();
    }

    /**
     * Determine if current store supports the use of phone mandatory
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getPhoneMandatorySupport($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getPhoneMandatorySupport();
    }

    /**
     * Determine if current store supports the use of phone mandatory
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getOrderMangagementClass($store = null)
    {
        return $this->getVersionConfig($store)->getOrdermanagement();
    }

    /**
     * Determine if current store supports the use of title mandatory
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getTitleMandatorySupport($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getTitleMandatorySupport();
    }

    /**
     * Determine if current store has a delayed push notification from Klarna
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getDelayedPushNotification($store = null)
    {
        return (bool)$this->getVersionConfig($store)->getDelayedPushNotification();
    }

    /**
     * Get checkout config value
     *
     * @param string $config
     * @param Store  $store
     *
     * @return mixed
     */
    public function getCheckoutConfig($config, $store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->getValue(sprintf('checkout/' . $this->code . '/%s', $config), $scope, $store);
    }

    /**
     * Get checkout design config value
     *
     * @param Store $store
     *
     * @return mixed
     */
    public function getCheckoutDesignConfig($store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        $designOptions = $this->scopeConfig->getValue('checkout/' . $this->code . '_design', $scope, $store);

        return is_array($designOptions) ? $designOptions : [];
    }

    /**
     * Get base currencey for store
     *
     * @param Store $store
     *
     * @return mixed
     */
    public function getBaseCurrencyCode($store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->getValue('currency/options/base', $scope, $store);
    }

    /**
     * Check is allowed Guest Checkout
     * Use config settings and observer
     *
     * @param CartInterface                  $quote
     * @param int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isAllowedGuestCheckout(CartInterface $quote, $store = null)
    {
        if ($store === null) {
            $store = $quote->getStore();
        }

        $guestCheckout = $this->getCheckoutConfigFlag('guest_checkout', $store);

        if ($guestCheckout) {
            $result = new DataObject();
            $result->setIsAllowed($guestCheckout);
            $this->_eventManager->dispatch(
                $this->eventPrefix . '_checkout_allow_guest',
                [
                    'quote'  => $quote,
                    'scope'  => $store,
                    'result' => $result
                ]
            );

            $guestCheckout = $result->getIsAllowed();
        }

        return $guestCheckout;
    }

    /**
     * Get checkout config value
     *
     * @param string $config
     * @param Store  $store
     * @param string $paymentMethod
     *
     * @return bool
     */
    public function getCheckoutConfigFlag($config, $store = null, $paymentMethod = null)
    {
        if (null === $paymentMethod) {
            $paymentMethod = $this->code;
        }
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->isSetFlag(sprintf('checkout/' . $paymentMethod . '/%s', $config), $scope, $store);
    }

    /**
     * Get payment config value
     *
     * @param string $config
     * @param Store  $store
     * @param string $paymentMethod
     *
     * @return bool
     */
    public function getPaymentConfigFlag($config, $store = null, $paymentMethod = null)
    {
        if (null === $paymentMethod) {
            $paymentMethod = $this->code;
        }
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->isSetFlag(sprintf('payment/' . $paymentMethod . '/%s', $config), $scope, $store);
    }

    /**
     * Determine if current store supports the use of partial captures and refunds
     *
     * @param Store $store
     *
     * @return bool
     */
    public function getPartialPaymentSupport($store = null)
    {
        return !(bool)$this->getVersionConfig($store)->getPartialPaymentDisabled();
    }

    /**
     * Return Builder Type to use in OM requests
     *
     * @param DataObject $versionConfig
     * @param string     $methodCode
     * @return null|string
     */
    public function getOmBuilderType(DataObject $versionConfig, $methodCode = 'klarna_kco')
    {
        if ($versionConfig->getType() === 'kred') {
            return '\Klarna\Kred\Model\Api\Builder\Kred';
        }
        if ($methodCode === 'klarna_kco') {
            return '\Klarna\Kco\Model\Api\Builder\Kasper';
        }
        if ($methodCode === 'klarna_kp') {
            return '\Klarna\Kp\Model\Api\Builder\Kasper';
        }
        return null;
    }

    /**
     * Get API config value
     *
     * @param string $config
     * @param Store  $store
     *
     * @return bool
     */
    public function getApiConfigFlag($config, $store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return $this->scopeConfig->isSetFlag(sprintf('klarna/api/%s', $config), $scope, $store);
    }

    /**
     * Determine if product price excludes VAT or not
     *
     * @param Store $store
     * @return bool
     */
    public function getPriceExcludesVat($store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return !$this->scopeConfig->isSetFlag('tax/calculation/price_includes_tax', $scope, $store);
    }

    /**
     * Determine if product price excludes VAT or not
     *
     * @param Store $store
     * @return bool
     */
    public function getTaxBeforeDiscount($store = null)
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        return !$this->scopeConfig->isSetFlag('tax/calculation/apply_after_discount', $scope, $store);
    }

    public function getFailureUrl($store = null)
    {
        $failureUrl = $this->getCheckoutConfigFlag('failure_url', $store, 'klarna_kco');
        if (!$failureUrl) {
            $failureUrl = $this->_urlBuilder->getUrl('checkout/cart');
        }
        return $failureUrl;
    }
}
