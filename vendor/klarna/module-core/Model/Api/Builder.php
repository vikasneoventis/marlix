<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Api;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\Checkout\Orderline\Collector;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Url;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Base class to generate API configuration
 *
 * @method Builder setShippingUnitPrice($integer)
 * @method int getShippingUnitPrice()
 * @method Builder setShippingTaxRate($integer)
 * @method int getShippingTaxRate()
 * @method Builder setShippingTotalAmount($integer)
 * @method int getShippingTotalAmount()
 * @method Builder setShippingTaxAmount($integer)
 * @method int getShippingTaxAmount()
 * @method Builder setShippingTitle($string)
 * @method string getShippingTitle()
 * @method Builder setShippingReference($integer)
 * @method int getShippingReference()
 * @method Builder setDiscountUnitPrice($integer)
 * @method int getDiscountUnitPrice()
 * @method Builder setDiscountTaxRate($integer)
 * @method int getDiscountTaxRate()
 * @method Builder setDiscountTotalAmount($integer)
 * @method int getDiscountTotalAmount()
 * @method Builder setDiscountTaxAmount($integer)
 * @method int getDiscountTaxAmount()
 * @method Builder setDiscountTitle($integer)
 * @method int getDiscountTitle()
 * @method Builder setDiscountReference($integer)
 * @method int getDiscountReference()
 * @method Builder setTaxUnitPrice($integer)
 * @method int getTaxUnitPrice()
 * @method Builder setTaxTotalAmount($integer)
 * @method int getTaxTotalAmount()
 * @method Builder setItems(array $array)
 * @method array getItems()
 */
class Builder extends DataObject implements BuilderInterface
{

    /**
     * @var Collector
     */
    protected $_orderLineCollector = null;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var array
     */
    protected $_orderLines = [];

    /**
     * @var \Magento\Sales\Model\AbstractModel|\Magento\Quote\Model\Quote
     */
    protected $_object = null;

    /**
     * @var array
     */
    protected $_request = [];

    /**
     * @var bool
     */
    protected $_inRequestSet = false;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $coreDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectManager
     */
    protected $objManager;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * Init
     *
     * @param EventManager         $eventManager
     * @param Collector            $collector
     * @param Url                  $url
     * @param ConfigHelper         $configHelper
     * @param ScopeConfigInterface $config
     * @param DirectoryHelper      $directoryHelper
     * @param DateTime\DateTime    $coreDate
     * @param DateTime             $dateTime
     * @param ObjectManager        $objManager
     * @param array                $data
     * @internal param $Url $
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
        parent::__construct($data);
        $this->eventManager = $eventManager;
        $this->_orderLineCollector = $collector;
        $this->url = $url;
        $this->configHelper = $configHelper;
        $this->config = $config;
        $this->directoryHelper = $directoryHelper;
        $this->coreDate = $coreDate;
        $this->dateTime = $dateTime;
        $this->objManager = $objManager;
    }

    /**
     * Generate order body
     *
     * @param string $type
     * @return $this
     */
    public function generateRequest($type = self::GENERATE_TYPE_CREATE)
    {
        $this->collectOrderLines();
        return $this;
    }

    /**
     * Collect order lines
     *
     * @param StoreInterface $store
     * @return $this
     */
    public function collectOrderLines($store = null)
    {
        /** @var \Klarna\Core\Model\Checkout\Orderline\AbstractLine $model */
        foreach ($this->getOrderLinesCollector()->getCollectors($store) as $model) {
            $model->collect($this);
        }

        return $this;
    }

    /**
     * Get totals collector model
     *
     * @return \Klarna\Core\Model\Checkout\Orderline\Collector
     */
    public function getOrderLinesCollector()
    {
        return $this->_orderLineCollector;
    }

    /**
     * Get the object used to generate request
     *
     * @return \Magento\Sales\Model\AbstractModel|\Magento\Quote\Model\Quote
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Set the object used to generate request
     *
     * @param \Magento\Sales\Model\AbstractModel|\Magento\Quote\Model\Quote $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->_object = $object;

        return $this;
    }

    /**
     * Get request
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set generated request
     *
     * @param array  $request
     * @param string $type
     *
     * @return $this
     */
    public function setRequest(array $request, $type = self::GENERATE_TYPE_CREATE)
    {
        $this->_request = $this->cleanNulls($request);

        if (!$this->_inRequestSet) {
            $this->_inRequestSet = true;
            $this->eventManager->dispatch(
                $this->prefix . "_builder_set_request_{$type}",
                [
                    'builder' => $this
                ]
            );

            $this->eventManager->dispatch(
                $this->prefix . '_builder_set_request',
                [
                    'builder' => $this
                ]
            );
            $this->_inRequestSet = false;
        }

        return $this;
    }

    /**
     * Remove items that are not allowed to be null
     *
     * @param array $request
     * @return array
     */
    protected function cleanNulls(array $request)
    {
        $disallowNulls = [
            'customer',
            'billing_address',
            'shipping_address',
            'external_payment_methods'
        ];
        foreach ($disallowNulls as $key) {
            if (empty($request[$key])) {
                unset($request[$key]);
            }
        }
        return $request;
    }

    /**
     * Get order lines as array
     *
     * @param bool $orderItemsOnly
     *
     * @return array
     */
    public function getOrderLines($orderItemsOnly = false)
    {
        /** @var \Klarna\Core\Model\Checkout\Orderline\AbstractLine $model */
        foreach ($this->getOrderLinesCollector()->getCollectors() as $model) {
            if ($model->isIsTotalCollector() && $orderItemsOnly) {
                continue;
            }

            $model->fetch($this);
        }

        return $this->_orderLines;
    }

    /**
     * Add an order line
     *
     * @param array $orderLine
     *
     * @return $this
     */
    public function addOrderLine(array $orderLine)
    {
        $this->_orderLines[] = $orderLine;

        return $this;
    }

    /**
     * Remove all order lines
     *
     * @return $this
     */
    public function resetOrderLines()
    {
        $this->_orderLines = [];

        return $this;
    }

    /**
     * Returns merchant checkbox if set
     *
     * NOTE: Use a plugin on this method to override.  In M1 this fired off kco_merchant_checkbox event
     *
     * @param $store
     * @param $quote
     * @return array|null
     */
    public function getMerchantCheckbox($store, $quote)
    {
        if (!$this->configHelper->getVersionConfig($store)->getMerchantCheckboxSupport()) {
            return null;
        }
        $merchantCheckboxMethod = $this->configHelper->getCheckoutConfig('merchant_checkbox', $store);
        if ($merchantCheckboxMethod === -1) {
            return null;
        }
        if (!$this->configHelper->getMerchantCheckboxEnabled($merchantCheckboxMethod, ['quote' => $quote])) {
            return null;
        }
        $merchantCheckboxObject = new DataObject([
            'text'     => $this->configHelper->getCheckoutConfig('merchant_checkbox_text', $store)
                ?: $this->configHelper->getMerchantCheckboxText($merchantCheckboxMethod),
            'checked'  => $this->getConfigFlag('merchant_checkbox_checked', $store),
            'required' => $this->getConfigFlag('merchant_checkbox_required', $store),
        ]);

        if ($merchantCheckboxObject->getText()) {
            return $merchantCheckboxObject->toArray();
        }
        return null;
    }

    /**
     * Return checkout config flag
     *
     * @param $key
     * @param $store
     * @return bool
     */
    protected function getConfigFlag($key, $store)
    {
        return $this->configHelper->getCheckoutConfigFlag($key, $store);
    }

    /**
     * Get misc options
     *
     * @param $store
     * @return mixed
     */
    public function getOptions($store)
    {
        $options = array_map('trim', array_filter($this->configHelper->getCheckoutDesignConfig($store)));
        return array_merge($options, [
            'allow_separate_shipping_address'   => $this->getConfigFlag('separate_address', $store),
            'phone_mandatory'                   => $this->configHelper->getPhoneMandatorySupport($store),
            'date_of_birth_mandatory'           => $this->getConfigFlag('dob_mandatory', $store) &&
                $this->configHelper->getDateOfBirthMandatorySupport($store),
        ]);
    }

    /**
     * Get merchant references
     *
     * @param $quote
     * @return DataObject
     */
    public function getMerchantReferences($quote)
    {
        $merchantReferences = new DataObject([
            'merchant_reference_1' => $quote->getReservedOrderId(),
            'merchant_reference_2' => ''
        ]);

        $this->eventManager->dispatch(
            $this->prefix . '_merchant_reference_update',
            [
                'quote'                     => $quote,
                'merchant_reference_object' => $merchantReferences
            ]
        );
        return $merchantReferences;
    }

    /**
     * Get Terms URL
     *
     * @param $store
     * @return mixed|string
     */
    public function getTermsUrl($store)
    {
        $termsUrl = $this->config->getValue('checkout/klarna_' . $this->prefix . '/terms_url', 'store', $store);
        if (!parse_url($termsUrl, PHP_URL_SCHEME)) {
            $termsUrl = $this->url->getDirectUrl($termsUrl, ['_nosid' => true]);
            return $termsUrl;
        }
        return $termsUrl;
    }

    /**
     * Populate prefill values
     *
     * @param $create
     * @param $quote
     * @param $store
     * @return mixed
     */
    public function prefill($create, $quote, $store)
    {
        /**
         * Customer
         */
        $create['customer'] = $this->getCustomerData($quote);

        /**
         * Billing Address
         */
        $create['billing_address'] = $this->_getAddressData($quote, Address::TYPE_BILLING);

        /**
         * Shipping Address
         */
        if (isset($create['billing_address']) && $this->getConfigFlag('separate_address', $store)) {
            $create['shipping_address'] = $this->_getAddressData($quote, Address::TYPE_SHIPPING);
        }
        return $create;
    }

    /**
     * Get customer details
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     * @deprecated
     * @see getCustomerData($quote)
     */
    protected function _getCustomerData($quote)
    {
        return $this->getCustomerData($quote);
    }

    /**
     * Get customer details
     *
     * @param $quote
     * @return array|null
     */
    public function getCustomerData($quote)
    {
        if (!$quote->getCustomerIsGuest() && $quote->getCustomerDob()) {
            return [
                'date_of_birth' => $this->coreDate->date('Y-m-d', $quote->getCustomerDob())
            ];
        }

        return null;
    }

    /**
     * Auto fill user address details
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string                                $type
     *
     * @return array
     */
    protected function _getAddressData($quote, $type = null)
    {
        $result = [];
        if ($quote->getCustomerEmail()) {
            $result['email'] = $quote->getCustomerEmail();
        }
        $customer = $quote->getCustomer();

        if ($quote->isVirtual() || $type === Address::TYPE_BILLING) {
            $address = $quote->getBillingAddress();

            if ($customer->getId() && !$address->getPostcode()) {
                $address = $this->getCustomerAddress($customer->getDefaultBilling());
            }
        } else {
            $address = $quote->getShippingAddress();

            if ($customer->getId() && !$address->getPostcode()) {
                $address = $this->getCustomerAddress($customer->getDefaultShipping());
            }
        }

        $resultObject = new DataObject($result);
        if ($address) {
            $address->explodeStreetAddress();
            $copyObj = $this->objManager->get('Magento\Framework\DataObject\Copy');
            $copyObj->copyFieldsetToTarget('sales_convert_quote_address', 'to_klarna', $address, $resultObject);

            if ($address->getCountryId() === 'US') {
                $resultObject->setRegion($address->getRegionCode());
            }
        }

        $street_address = $resultObject->getStreetAddress();
        if (!is_array($street_address)) {
            $street_address = [$street_address];
        }
        if (count($street_address) === 1) {
            $street_address[] = '';
        }
        $resultObject->setStreetAddress($street_address[0]);
        $resultObject->setData('street_address2', $street_address[1]);

        return array_filter($resultObject->toArray());
    }

    /**
     * Get GUI options
     *
     * @param $store
     * @return array
     */
    public function getGuiOptions($store)
    {
        if (!$this->getConfigFlag('auto_focus', $store)) {
            return ['disable_autofocus'];
        }
        return null;
    }

    /**
     * Populate external payment methods array
     *
     * @param array          $enabledExternalMethods
     * @param StoreInterface $store
     *
     * @return array
     */
    protected function getExternalMethods($enabledExternalMethods, $store)
    {
        if (!$enabledExternalMethods) {
            return null;
        }
        $externalMethods = [];
        foreach (explode(',', $enabledExternalMethods) as $externalMethod) {
            $methodDetails = $this->configHelper->getExternalPaymentDetails($externalMethod, $store);
            if (!$methodDetails->isEmpty()) {
                $externalMethods[] = $methodDetails->toArray();
            }
        }
        if (count($externalMethods)) {
            return $externalMethods;
        }
        return null;
    }

    /**
     * Retrieve customer address
     *
     * @param string $address_id
     * @return AddressInterface
     */
    private function getCustomerAddress($address_id)
    {
        if (!$address_id) {
            return null;
        }
        if ($address_id instanceof AddressInterface) {
            return $address_id;
        }
        try {
            return $this->objManager->create(\Magento\Customer\Model\AddressRegistry::class)->retrieve($address_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
