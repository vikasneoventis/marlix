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
use Klarna\Kco\Helper\Checkout;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;

/**
 * Checkout page
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class Index extends Action
{
    /**
     * @var Checkout
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context               $context
     * @param Session                                             $customerSession
     * @param CustomerRepositoryInterface                         $customerRepository
     * @param AccountManagementInterface                          $accountManagement
     * @param \Magento\Framework\Registry                         $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface        $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator      $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory               $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface          $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory        $resultLayoutFactory
     * @param RawFactory                                          $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory
     * @param LoggerInterface                                     $logger
     * @param Kco                                                 $kco
     * @param ConfigHelper                                        $configHelper
     * @param CheckoutSession                                     $checkoutSession
     * @param Escaper                                             $escaper
     * @param CustomerMetadataInterface                           $customerMetadata
     * @param Checkout                                            $checkoutHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
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
        Checkout $checkoutHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
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
        $this->checkoutHelper = $checkoutHelper;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->checkoutHelper->kcoEnabled()) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $resultPage = parent::execute();
        if ($resultPage instanceof Redirect) {
            return $resultPage;
        }
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
}
