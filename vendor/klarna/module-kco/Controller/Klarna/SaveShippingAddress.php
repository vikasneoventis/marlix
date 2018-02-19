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

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;

/**
 * Save shipping address
 *
 * This method is used when backend callbacks are not supported in the Klarna market
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class SaveShippingAddress extends Action
{
    /**
     * SaveShippingAddress constructor.
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
     * @param JsonHelper                                         $jsonHelper
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
        CustomerMetadataInterface $customerMetadata,
        JsonHelper $jsonHelper
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
            $resultJsonFactory,
            $logger,
            $kco,
            $configHelper,
            $checkoutSession,
            $escaper,
            $customerMetadata
        );
        $this->jsonHelper = $jsonHelper;
    }

    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        $result = [];
        $quote = $this->getQuote();

        $body = $this->getRequest()->getContent();
        try {
            $data = $this->jsonHelper->jsonDecode($body);
        } catch (\Exception $e) {
            return $this->sendBadRequestResponse($e->getMessage(), 500);
        }

        try {
            $addressData = new DataObject($data);

            // Update quote details
            $this->updateCustomerOnQuote($quote, $addressData);

            // Update billing address
            $this->_updateOrderAddress($addressData, Address::TYPE_BILLING);

            $quote->collectTotals();
            $this->getKco()->updateKlarnaTotals();
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $result['redirect_url'] = $this->configHelper->getFailureUrl();
            $result['error_message'] = $e->getMessage();
            $result['error_code'] = $e->getCode();
            $result['error_line'] = $e->getFile() . ':' . $e->getLine();
        }

        return $this->getSummaryResponse($result);
    }
}
