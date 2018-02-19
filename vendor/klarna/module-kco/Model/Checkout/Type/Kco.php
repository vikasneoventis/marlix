<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Checkout\Type;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Api\ApiInterface;
use Klarna\Kco\Api\QuoteInterface;
use Klarna\Kco\Helper\ApiHelper;
use Klarna\Kco\Helper\CartHelper;
use Klarna\Kco\Model\Quote;
use Klarna\Kco\Model\QuoteRepository as KlarnaQuoteRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class Kco
 *
 * @package Klarna\Kco\Model\Checkout\Type
 */
class Kco
{
    /**
     * Checkout types: Checkout as Guest, Register, Logged In Customer
     */
    const METHOD_GUEST    = 'guest';
    const METHOD_REGISTER = 'register';
    const METHOD_CUSTOMER = 'customer';

    /**
     * @var CountryCollection
     */
    protected $countryCollection;

    /**
     * @var MageQuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var DataObject
     */
    protected $_klarnaCheckout;

    /**
     * @var QuoteInterface
     */
    protected $_klarnaQuote;

    /**
     * @var CartInterface
     */
    protected $_quote;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var KlarnaQuoteRepository
     */
    protected $klarnaQuoteRepository;

    /**
     * @var AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ObjectManager
     */
    protected $objManager;

    /**
     * Class constructor
     * Set customer already exists message
     *
     * @param MageQuoteRepository               $quoteRepository
     * @param KlarnaQuoteRepository             $klarnaQuoteRepository
     * @param CheckoutSession                   $checkoutSession
     * @param CustomerSession                   $customerSession
     * @param AddressRegistry                   $addressRegistry
     * @param CustomerRegistry                  $customerRegistry
     * @param LoggerInterface                   $logger
     * @param EventManager                      $eventManager
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartHelper                        $cartHelper
     * @param CountryCollection                 $countryCollection
     * @param ConfigHelper                      $configHelper
     * @param ApiHelper                         $apiHelper
     * @param ObjectManager                     $objManager
     */
    public function __construct(
        MageQuoteRepository $quoteRepository,
        KlarnaQuoteRepository $klarnaQuoteRepository,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        AddressRegistry $addressRegistry,
        CustomerRegistry $customerRegistry,
        LoggerInterface $logger,
        EventManager $eventManager,
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartHelper $cartHelper,
        CountryCollection $countryCollection,
        ConfigHelper $configHelper,
        ApiHelper $apiHelper,
        ObjectManager $objManager
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->klarnaQuoteRepository = $klarnaQuoteRepository;
        $this->addressRegistry = $addressRegistry;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartHelper = $cartHelper;
        $this->countryCollection = $countryCollection;
        $this->configHelper = $configHelper;
        $this->apiHelper = $apiHelper;
        $this->objManager = $objManager;
    }

    /**
     * Initialize quote state to be valid for one page checkout
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initCheckout()
    {
        $quote = $this->getQuote();

        /**
         * Reset multishipping flag before any manipulations with quote address
         * addAddress method for quote object related on this flag
         */
        if ($quote->getIsMultiShipping()) {
            $quote->setIsMultiShipping(false);
        }

        $this->savePayment();
        $this->checkShippingMethod($quote);

        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        if ($this->allowCheckout()) {
            $this->_initKlarnaCheckout();
        }

        return $this;
    }

    /**
     * Quote object getter
     *
     * @return CartInterface
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Declare checkout quote instance
     *
     * @param CartInterface $quote
     * @return $this
     */
    public function setQuote(CartInterface $quote)
    {
        $this->_quote = $quote;

        return $this;
    }

    /**
     * Specify quote payment method
     *
     * @param   array $data
     *
     * @return  $this
     */
    public function savePayment($data = [])
    {
        $data['method'] = 'klarna_kco';
        $quote = $this->getQuote();

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($data['method']);
        } else {
            $quote->getShippingAddress()->setPaymentMethod($data['method']);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $payment = $quote->getPayment();
        $payment->importData($data);

        return $this;
    }

    /**
     * Set default shipping method if one exist
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $quote
     *
     * @return $this
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Klarna\Core\Exception
     */
    public function checkShippingMethod(CartInterface $quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        if ($quote->isVirtual()) {
            return $this;
        }

        /** @var AddressInterface|Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getCountryId()) {
            $defaultDestination = $this->cartHelper->getDefaultDestinationAddress($quote->getStore());
            $shippingAddress->addData($defaultDestination->toArray());
            $shippingAddress->save();
        }

        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface[] $rates */
        $rates = $this->shippingMethodManagement->getList($quote->getId());
        $defaultRate = null;
        $selectedMethodExist = false;
        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $rate */
        foreach ($rates as $rate) {
            if (null === $defaultRate) {
                $defaultRate = $rate;
            }
            $method = $rate->getCarrierCode() . '_' . $rate->getMethodCode();
            if ($method === $shippingAddress->getShippingMethod()) {
                $selectedMethodExist = true;
                break;
            }
        }

        if (!$selectedMethodExist && $defaultRate !== null) {
            $this->saveShippingMethod($defaultRate->getCarrierCode() . '_' . $defaultRate->getMethodCode());
        }

        return $this;
    }

    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     *
     * @return $this
     * @throws KlarnaException
     */
    public function saveShippingMethod($shippingMethod)
    {
        $this->getQuote()->setTotalsCollectedFlag(false);
        $extensionAttributes = $this->getQuote()->getExtensionAttributes();
        if ($extensionAttributes !== null) { // Seems to only work in Magento 2.1+
            $shipping_assignments = $this->getQuote()->getExtensionAttributes()->getShippingAssignments();
            foreach ($shipping_assignments as $assignment) {
                $assignment->getShipping()->setMethod($shippingMethod);
            }
        }
        $this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        $this->getQuote()->collectTotals();
        return $this;
    }

    /**
     * If checkout is allowed for the current customer
     *
     * Checks if guest checkout is allowed and if the customer is a guest or not
     *
     * @return bool
     */
    public function allowCheckout()
    {
        return $this->_customerSession->isLoggedIn()
            || ($this->isAllowedGuestCheckout($this->getQuote())
                && !$this->_customerSession->isLoggedIn());
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
        return $this->configHelper->isAllowedGuestCheckout($quote, $store);
    }

    /**
     * Initialize Klarna checkout
     *
     * Will create or update the checkout order in the Klarna API
     *
     * @param bool $createIfNotExists
     * @param bool $updateItems
     *
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initKlarnaCheckout($createIfNotExists = true, $updateItems = true)
    {
        $klarnaCheckoutId = $this->getKlarnaQuote()->getKlarnaCheckoutId();
        $this->_klarnaCheckout = $this->getApiInstance($this->getQuote()->getStore())
                                      ->initKlarnaCheckout($klarnaCheckoutId, $createIfNotExists, $updateItems);

        $klarnaOrderId = $this->_klarnaCheckout->getOrderId();
        if (!$klarnaOrderId) {
            $klarnaOrderId = $this->_klarnaCheckout->getId();
        }
        $this->setKlarnaQuoteKlarnaCheckoutId($klarnaOrderId);

        if ($createIfNotExists || $updateItems) {
            $this->getKlarnaQuote()->setIsChanged(0);
            $this->klarnaQuoteRepository->save($this->getKlarnaQuote());
        }

        return $this->_klarnaCheckout;
    }

    /**
     * Get Klarnaquote object based off current checkout quote
     *
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getKlarnaQuote()
    {
        if ($this->_klarnaQuote !== null) {
            return $this->_klarnaQuote;
        }
        try {
            $this->_klarnaQuote = $this->klarnaQuoteRepository->getActiveByQuote($this->getQuote());
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Could not locate Klarna Quote by Active Magento Quote: ' . $this->getQuote()->getId());
            $this->logger->warning($e);
            $this->_klarnaQuote = $this->_createNewKlarnaQuote();
        }

        return $this->_klarnaQuote;
    }

    /**
     * Set Klarnaquote object
     *
     * @param QuoteInterface $klarnaQuote
     *
     * @return $this
     */
    public function setKlarnaQuote($klarnaQuote)
    {
        $this->_klarnaQuote = $klarnaQuote;

        return $this;
    }

    /**
     * Get api instance
     *
     * @param Store $store
     * @return ApiInterface
     * @throws \RuntimeException
     * @throws \Klarna\Core\Exception
     */
    public function getApiInstance($store = null)
    {
        return $this->apiHelper->getApiInstance($store);
    }

    /**
     * Set the Klarna checkout id
     *
     * @param string $klarnaCheckoutId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setKlarnaQuoteKlarnaCheckoutId($klarnaCheckoutId)
    {
        $klarnaCheckoutId = trim($klarnaCheckoutId);

        if ('' === $klarnaCheckoutId) {
            return $this;
        }

        $klarnaQuote = $this->getKlarnaQuote();

        if (!$klarnaQuote->getId()) {
            $klarnaQuote = $this->_createNewKlarnaQuote($klarnaCheckoutId);
        }
        if ($klarnaQuote->getKlarnaCheckoutId() === null) {
            $klarnaQuote->setKlarnaCheckoutId($klarnaCheckoutId);
            $this->klarnaQuoteRepository->save($klarnaQuote);
        }
        if ($klarnaQuote->getKlarnaCheckoutId() !== $klarnaCheckoutId) {
            $klarnaQuote->setIsActive(0);
            $this->klarnaQuoteRepository->save($klarnaQuote);

            $klarnaQuote = $this->_createNewKlarnaQuote($klarnaCheckoutId);
        }

        $this->setKlarnaQuote($klarnaQuote);

        return $this;
    }

    /**
     * Create a new klarna quote object
     *
     * @param string $klarnaCheckoutId
     *
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function _createNewKlarnaQuote($klarnaCheckoutId = null)
    {
        $data = [
            'klarna_checkout_id' => $klarnaCheckoutId,
            'is_active'          => 1,
            'quote_id'           => $this->getQuote()->getId(),
        ];
        /** @var Quote $klarnaQuote */
        $klarnaQuote = $this->klarnaQuoteRepository->create();
        $klarnaQuote->setData($data);
        $this->klarnaQuoteRepository->save($klarnaQuote);

        return $klarnaQuote;
    }

    /**
     * Update state of cart to Klarna
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateKlarnaTotals()
    {
        $this->_initKlarnaCheckout(false);

        return $this;
    }

    /**
     * Get customer address by identifier
     *
     * @param   int $addressId
     *
     * @return  \Magento\Customer\Model\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAddress($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }

        return $address;
    }

    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Klarna\Core\Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveOrder()
    {
        $this->validate();
        $this->_initKlarnaCheckout(false, false);

        if ($this->getCheckoutMethod() === self::METHOD_REGISTER) {
            $this->_prepareNewCustomerQuote();
        }

        $this->eventManager->dispatch(
            'kco_checkout_save_order_before',
            [
                'checkout' => $this
            ]
        );

        /** @var \Magento\Quote\Model\QuoteManagement $service */
        $service = $this->objManager->create('Magento\Quote\Api\CartManagementInterface');
        /** @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objManager->create('Magento\Sales\Api\OrderRepositoryInterface');
        $orderId = $service->placeOrder($this->getQuote()->getId());
        /** @var \Magento\Sales\Model\Order $order */
        $order = $orderRepository->get($orderId);
        if (($merchantCheckboxMethod = $this->configHelper->getCheckoutConfig(
            'merchant_checkbox',
            $this->getQuote()->getStore()
        )) !== -1
        ) {
            $this->dispatchMerchantCheckboxMethod(
                $merchantCheckboxMethod,
                [
                    'quote'        => $this->getQuote(),
                    'order'        => $order,
                    'klarna_quote' => $this->getKlarnaQuote(),
                    'checked'      => (bool)$this->getKlarnaCheckout()
                                                 ->getData('merchant_requested/additional_checkbox')
                ]
            );
        }

        if ($order) {
            $this->eventManager->dispatch(
                'checkout_type_kco_save_order_after',
                [
                    'order' => $order,
                    'quote' => $this->getQuote()
                ]
            );
        }

        return $order;
    }

    /**
     * Validate quote state to be integrated with klarna checkout process
     *
     * @throws KlarnaException
     */
    public function validate()
    {
        $quote = $this->getQuote();
        if ($quote->getIsMultiShipping()) {
            throw new KlarnaException(__('Invalid checkout type.'));
        }

        if ($quote->getCheckoutMethod() == self::METHOD_GUEST && !$this->isAllowedGuestCheckout($quote)) {
            throw new KlarnaException(__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
        }
    }

    /**
     * Get quote checkout method
     *
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
            return $this->getQuote()->getCheckoutMethod();
        }
        if (!$this->getQuote()->getCheckoutMethod()) {
            if ($this->isAllowedGuestCheckout($this->getQuote())) {
                $this->getQuote()->setCheckoutMethod(self::METHOD_GUEST);
            } else {
                $this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
            }
        }

        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function _prepareGuestQuote()
    {
        $quote = $this->getQuote();
        if ($quote->getCustomerId()) {
            // Don't reset existing customer on quote
            return $this;
        }
        $quote->setCustomerId(null)
              ->setCustomerEmail($quote->getBillingAddress()->getEmail())
              ->setCustomerIsGuest(true)
              ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);

        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function _prepareNewCustomerQuote()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $quote->getCustomer();
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        if ($quote->getCustomerId()) {
            // If customer is already logged in/exists, stop here
            return;
        }
        /** @var \Magento\Framework\DataObject\Copy $copyObj */
        $copyObj = $this->objManager->get('Magento\Framework\DataObject\Copy');
        $copyObj->copyFieldsetToTarget('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $quote->setCustomer($customer)
              ->setCustomerId(true);
    }

    /**
     * Dispatch the merchant checkbox method
     *
     * This should be called before order creation
     *
     * @param string $code
     * @param array  $args
     *
     * @return mixed
     */
    public function dispatchMerchantCheckboxMethod($code, $args = [])
    {
        if (!$code) {
            return null;
        }

        $methodConfig = $this->configHelper->getMerchantCheckboxMethodConfig($code);
        $this->eventManager->dispatch(
            'kco_' . $methodConfig->getSaveEvent(),
            $args
        );

        return $this;
    }

    /**
     * Get current Klarna checkout object
     *
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getKlarnaCheckout()
    {
        if (is_null($this->_klarnaCheckout)) {
            $this->_initKlarnaCheckout(false, false);
        }

        return $this->_klarnaCheckout;
    }

    /**
     * Get last order increment id by order id
     *
     * @return string
     */
    public function getLastOrderId()
    {
        $orderId = false;
        $order = $this->getCheckout()->getLastRealOrder();
        if ($order->getId()) {
            $orderId = $order->getIncrementId();
        }
        return $orderId;
    }

    /**
     * Get frontend checkout session object
     *
     * @return CheckoutSession
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Update KCO checkout session address
     *
     * @param DataObject $klarnaAddressData
     * @param string     $type
     *
     * @throws KlarnaException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     */
    public function updateCheckoutAddress(
        $klarnaAddressData,
        $type = Address::TYPE_BILLING
    ) {
        $country = strtoupper($klarnaAddressData->getCountry());
        $countryDirectory = $this->countryCollection->addCountryCodeFilter($country)->getFirstItem();
        $address1 = $klarnaAddressData->hasStreetName()
            ? $klarnaAddressData->getStreetName() . ' ' . $klarnaAddressData->getStreetNumber()
            : $klarnaAddressData->getStreetAddress();
        if ($klarnaAddressData->hasHouseExtension()) {
            $address1 .= ' ' . $klarnaAddressData->getHouseExtension();
        }
        $streetData = [
            $address1,
            $klarnaAddressData->getData('street_address2')
        ];
        $streetData = array_filter($streetData);
        $data = [
            'lastname'      => $klarnaAddressData->getFamilyName(),
            'firstname'     => $klarnaAddressData->getGivenName(),
            'email'         => $klarnaAddressData->getEmail(),
            'company'       => $klarnaAddressData->getCareOf(),
            'prefix'        => $klarnaAddressData->getTitle(),
            'street'        => $streetData,
            'postcode'      => $klarnaAddressData->getPostalCode(),
            'city'          => $klarnaAddressData->getCity(),
            'region'        => $klarnaAddressData->getRegion(),
            'telephone'     => $klarnaAddressData->getPhone(),
            'country_id'    => $countryDirectory->getId(),
            'same_as_other' => $klarnaAddressData->getSameAsOther() ? 1 : 0
        ];

        if ($klarnaAddressData->hasCustomerDob()) {
            $data['dob'] = $klarnaAddressData->getCustomerDob();
        }

        if ($klarnaAddressData->hasCustomerGender()) {
            $data['gender'] = $klarnaAddressData->getCustomerGender();
        }

        $dataObject = new DataObject($data);

        $this->eventManager->dispatch(
            'klarna_kco_update_checkout_address',
            [
                'data_object'         => $dataObject,
                'klarna_address_data' => $klarnaAddressData,
                'address_type'        => $type,
            ]
        );

        if (Address::TYPE_BILLING === $type) {
            $this->saveBilling($data, 0);
        } else {
            $this->saveShipping($data, 0);
        }
    }

    /**
     * Save billing address information to quote
     *
     * @param array $data
     * @param int   $customerAddressId
     * @return $this
     * @throws \InvalidArgumentException
     * @throws KlarnaException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            throw new KlarnaException(__('Invalid billing details'));
        }

        $address = $this->getQuote()->getBillingAddress();
        /** @var $addressForm \Magento\Customer\Model\Form */
        $addressForm = $this->objManager->create('\Magento\Customer\Model\Form');
        $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest($this->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = $this->addressRegistry->retrieve($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() !== $this->getQuote()->getCustomerId()) {
                    throw new KlarnaException(__('Customer Address is not valid.'));
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $address->setCustomerId($customerAddressId->getCustomerId());
                $addressForm->setEntity($address);
                $addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true && $this->isNotNordicDach()) {
                    throw new KlarnaException(sprintf('%s', implode("\n", $addressErrors)));
                }
            }
        } else {
            $addressForm->setEntity($address);
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true && $this->isNotNordicDach()) {
                throw new KlarnaException(sprintf('%s', implode("\n", array_values($addressErrors))));
            }

            $addressForm->compactData($addressData);
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), null);
                }
            }
            $address->setCustomerAddressId(null);
            $address->setSaveInAddressBook(0);
        }

        // set email for newly created user
        if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
            $address->setEmail($this->getQuote()->getCustomerEmail());
        }

        // validate billing address
        $validateRes = $address->validate();
        if ($validateRes !== true && $this->isNotNordicDach()) {
            throw new KlarnaException(sprintf('%s', implode("\n", $validateRes)));
        }

        $this->_validateCustomerData($data);

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), $this->getQuote()->getWebsite()->getId())) {
                throw new KlarnaException(__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'));
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using options
             */
            $usingCase = isset($data['same_as_other']) ? (int)$data['same_as_other'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = ['customer_address_id'];

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey])
                            && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->unsetData('region_id');
                    $shipping->addData($billing->getData())
                             ->setSameAsBilling(1)
                             ->setSaveInAddressBook(0);
                    $this->saveShippingMethod($shippingMethod);
                    break;
            }
        }

        return $this;
    }

    /**
     * Determine if request is an AJAX request or not
     *
     * @return mixed
     */
    protected function isAjax()
    {
        //TODO: Figure out a better way to detect this
        return $this->objManager->get('Magento\Framework\App\Request\Http')->isAjax();
    }

    /**
     * Validate customer data and set some its data for further usage in quote
     * Will return either true or array with error messages
     *
     * @param array $data
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    protected function _validateCustomerData(array $data)
    {
        if ($this->getQuote()->getCustomerId()) {
            return $this; // Already logged-in
        }
        $customer = $this->getCustomerSession()->getCustomer();

        // Handle for DOB due to bug in M2 versions < 2.2
        if (isset($data['dob'])) {
            $customer->setDob($data['dob']);
            unset($data['dob']);
        }

        /** @var $customerForm \Magento\Customer\Model\Form */
        $customerForm = $this->objManager->create('\Magento\Customer\Model\Form');
        $customerForm->setFormCode('customer_account_create')
                     ->setEntity($customer)
                     ->setIsAjaxRequest($this->isAjax());

        /* @var $customer \Magento\Customer\Model\Customer */
        $customerRequest = $customerForm->prepareRequest($data);
        $customerData = $customerForm->extractData($customerRequest);

        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true && $this->isNotNordicDach()) {
            throw new KlarnaException(sprintf('%s', implode("\n", $customerErrors)));
        }

        $customerForm->compactData($customerData);

        // set NOT LOGGED IN group id explicitly,
        // otherwise copyFieldset('customer_account', 'to_quote') will fill it with default group id value
        $customer->setGroupId(Group::NOT_LOGGED_IN_ID);

        // copy customer/guest email to address
        $quote = $this->getQuote();
        $quote->getBillingAddress()->setEmail($customer->getEmail());

        // copy customer data to quote
        /** @var \Magento\Framework\DataObject\Copy $copyObj */
        $copyObj = $this->objManager->get('Magento\Framework\DataObject\Copy');
        $copyObj->copyFieldsetToTarget('customer_account', 'to_quote', $customer, $quote);

        return $this;
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int    $websiteId
     *
     * @return false|\Magento\Customer\Model\Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        try {
            $customer = $this->customerRegistry->retrieveByEmail($email, $websiteId);
            if ($customer->getId()) {
                return $customer;
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return false;
    }

    /**
     * Save checkout shipping address
     *
     * @param array $data
     * @param int   $customerAddressId
     *
     * @return $this
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws KlarnaException
     */
    public function saveShipping($data, $customerAddressId)
    {
        if (0 === count($data)) {
            throw new KlarnaException(__('Invalid billing details'));
        }

        $address = $this->getQuote()->getShippingAddress();

        /** @var $addressForm \Magento\Customer\Model\Form */
        $addressForm = $this->objManager->create('\Magento\Customer\Model\Form');
        $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest($this->isAjax());

        if ($customerAddressId !== null && $customerAddressId > 0) {
            $customerAddress = $this->addressRegistry->retrieve($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() !== $this->getQuote()->getCustomerId()) {
                    throw new KlarnaException(__('Customer Address is not valid.'));
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true && $this->isNotNordicDach()) {
                    throw new KlarnaException(sprintf('%s', implode("\n", $addressErrors)));
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true && $this->isNotNordicDach()) {
                throw new KlarnaException(sprintf('%s', implode("\n", $addressErrors)));
            }
            $addressForm->compactData($addressData);
            // unset shipping address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), null);
                }
            }

            $address->setCustomerAddressId(null);
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            $address->setSameAsBilling(empty($data['same_as_other']) ? 0 : 1);
        }

        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate()) !== true && $this->isNotNordicDach()) {
            throw new KlarnaException(sprintf('%s', implode("\n", $validateRes)));
        }

        return $this;
    }

    /**
     * Check to make sure API version is not Nortic or DACH
     *
     * @return bool
     */
    protected function isNotNordicDach()
    {
        $apiVersion = $this->configHelper->getApiConfig(
            'api_version',
            $this->getQuote()->getStore()
        );
        return !in_array($apiVersion, ['nortic', 'dach']);
    }
}
