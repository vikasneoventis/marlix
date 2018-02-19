<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Controller\Klarna;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Traits\CommonController;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Checkout\Controller\Index\Index as BaseAction;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class Action extends BaseAction
{

    use CommonController;

    /**
     * @var Kco
     */
    protected $kco;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * Action constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context
     * @param Session                                            $customerSession
     * @param CustomerRepositoryInterface                        $customerRepository
     * @param AccountManagementInterface                         $accountManagement
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface       $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory              $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface         $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory         $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory       $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory    $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory
     * @param LoggerInterface                                    $logger
     * @param Kco                                                $kco
     * @param ConfigHelper                                       $configHelper
     * @param CheckoutSession                                    $checkoutSession
     * @param Escaper                                            $escaper
     * @param CustomerMetadataInterface                          $customerMetadata
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Kco $kco,
        ConfigHelper $configHelper,
        CheckoutSession $checkoutSession,
        Escaper $escaper,
        CustomerMetadataInterface $customerMetadata
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
        $this->logger = $logger;
        $this->kco = $kco;
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->escaper = $escaper;
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getQuote()->hasItems()
            || $this->getQuote()->getHasError()
            || $this->getQuote()->getIsMultiShipping()
        ) {
            return true;
        }

        $action = $this->getRequest()->getActionName();
        $ignoredExpiredActions = new DataObject(['index']);

        $this->_eventManager->dispatch(
            'checkout_kco_ignored_expired_action',
            [
                'ignored_expired_actions' => $ignoredExpiredActions
            ]
        );

        if ($this->getCheckoutSession()->getCartWasUpdated(true)
            && !in_array($action, $ignoredExpiredActions->toArray())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get current customer quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Get the checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get the response for the order summary
     *
     * @param array $result
     * @return ResultInterface
     */
    protected function getSummaryResponse(array $result = [])
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $result['update_section'] = [
                'name' => 'checkout_summary',
                'html' => $this->getSummaryHtml()
            ];

            $resultObject = new DataObject($result);
            $this->_eventManager->dispatch(
                'kco_summary_response',
                [
                    'controller'    => $this,
                    'result_object' => $resultObject
                ]
            );
            $response = $this->resultJsonFactory->create();
            $response->setData($resultObject->toArray());
            return $response;
        }
        return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
    }

    /**
     * Get the html of the checkout details summary
     */
    public function getSummaryHtml()
    {
        $layout = $this->layoutFactory->create();
        $update = $layout->getUpdate();
        $update->load('checkout_klarna_summary');
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getOutput();

        $this->_translateInline->processResponseBody($output);

        return $output;
    }

    /**
     * Escape html entities
     *
     * @param array|string $text
     * @return array|string
     */
    protected function escapeHtml($text)
    {
        return $this->escaper->escapeHtml($text);
    }

    /**
     * Update addresses on an order using api data
     *
     * @param DataObject $checkout
     * @return $this
     * @throws \Exception
     */
    protected function _updateOrderAddresses(DataObject $checkout)
    {
        if ($checkout->hasCustomer() || $checkout->hasBillingAddress() || $checkout->hasShippingAddress()) {
            try {
                $updateErrors = [];
                $billingAddress = new DataObject($checkout->getBillingAddress());
                $shippingAddress = new DataObject($checkout->getShippingAddress());
                $sameAsOther = false;

                if ($checkout->hasBillingAddress() && $checkout->hasShippingAddress()) {
                    $sameAsOther = $checkout->getShippingAddress() == $checkout->getBillingAddress();
                }

                if ($checkout->hasCustomer()) {
                    $customer = $checkout->getCustomer();
                    $customer = new DataObject($customer);
                    if ($customer->hasDateOfBirth()) {
                        $dob = $customer->getDateOfBirth();
                        //TODO: Set date based on locale but convert to DATE_MEDIUM See https://stash.int.klarna.net/projects/MAGE/repos/m1-kco/commits/5c4be357585ce68ae849807426bc9b5634d7700b
                        $billingAddress->setCustomerDob($dob);
                    }
                    if ($customer->hasGender()) {
                        $maleId = false;
                        $femaleId = false;
                        /** @var \Magento\Customer\Api\Data\OptionInterface[] $options */
                        $options = $this->_getAttribute('gender')->getOptions();

                        foreach ($options as $option) {
                            switch (strtolower($option->getLabel())) {
                                case 'male':
                                    $maleId = $option->getValue();
                                    break;
                                case 'female':
                                    $femaleId = $option->getValue();
                                    break;
                            }
                        }
                        switch ($customer->getGender()) {
                            case 'male':
                                if (false !== $maleId) {
                                    $billingAddress->setCustomerGender($maleId);
                                }
                                break;
                            case 'female':
                                if (false !== $femaleId) {
                                    $billingAddress->setCustomerGender($femaleId);
                                }
                                break;
                        }
                    }
                }

                if ($checkout->hasBillingAddress()) {
                    $billingAddress->setSameAsOther($sameAsOther);

                    $this->updateCustomerOnQuote($this->getKco()->getQuote(), $billingAddress);

                    // Update billing address
                    try {
                        $this->_updateOrderAddress($billingAddress, Address::TYPE_BILLING);
                    } catch (KlarnaException $e) {
                        $updateErrors[] = $e->getMessage();
                    }
                }

                if ($checkout->hasShippingAddress() & !$sameAsOther) {
                    // Update shipping address
                    try {
                        $this->_updateOrderAddress($shippingAddress, Address::TYPE_SHIPPING);
                    } catch (KlarnaException $e) {
                        $updateErrors[] = $e->getMessage();
                    }
                }

                if (!empty($updateErrors)) {
                    $prettyErrors = implode("\n", $updateErrors);
                    $prettyJson = json_encode($checkout->toArray(), JSON_PRETTY_PRINT);

                    $this->log("$prettyErrors\n$prettyJson\n", LogLevel::ALERT);

                    if ($this->getRequest()->isAjax()) {
                        $this->sendBadAddressRequestResponse($updateErrors);
                    }

                    throw new KlarnaException('Shipping address update error');
                }

                $this->getKco()->checkShippingMethod($this->getKco()->getQuote());
            } catch (KlarnaException $e) {
                $this->log($e, LogLevel::ERROR);
                throw $e;
            } catch (\Exception $e) {
                $this->log($e, LogLevel::ERROR);
            }
        }

        return $this;
    }

    /**
     * Update customer info on quote for guest users
     *
     * @param CartInterface $quote
     * @param DataObject    $billingAddress
     */
    protected function updateCustomerOnQuote(CartInterface $quote, DataObject $billingAddress)
    {
        if ($quote->getCustomerId()) {
            // Don't update if customer is logged in
            return;
        }
        $quote->setCustomerEmail($billingAddress->getEmail());
        $quote->setCustomerFirstname($billingAddress->getGivenName());
        $quote->setCustomerLastname($billingAddress->getFamilyName());
    }

    /**
     * Get kco checkout model
     *
     * @return \Klarna\Kco\Model\Checkout\Type\Kco
     */
    public function getKco()
    {
        return $this->kco;
    }

    /**
     * Update quote address using address details from api call
     *
     * @param DataObject $klarnaAddressData
     * @param string     $type
     *
     * @throws KlarnaException
     */
    protected function _updateOrderAddress(DataObject $klarnaAddressData, $type = Address::TYPE_BILLING)
    {
        $this->getKco()->updateCheckoutAddress($klarnaAddressData, $type);
    }

    /**
     * Wrapper around logger
     *
     * @param string $message
     * @param string $level
     * @return null
     */
    protected function log($message, $level = LogLevel::INFO)
    {
        return $this->logger->log($level, $message);
    }
    /**
     * Retrieve customer attribute instance
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     */
    protected function _getAttribute($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
