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

use Klarna\Core\Api\OrderRepositoryInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Order success action
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class Success extends Action
{
    /**
     * @var SuccessValidator
     */
    protected $successValidator;

    /**
     * @var OrderRepositoryInterface
     */
    protected $klarnaOrderRepository;

    /**
     * Success constructor.
     *
     * @param Context                     $context
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface  $accountManagement
     * @param Registry                    $coreRegistry
     * @param InlineInterface             $translateInline
     * @param Validator                   $formKeyValidator
     * @param ScopeConfigInterface        $scopeConfig
     * @param LayoutFactory               $layoutFactory
     * @param CartRepositoryInterface     $quoteRepository
     * @param PageFactory                 $resultPageFactory
     * @param ResultLayoutFactory         $resultLayoutFactory
     * @param RawFactory                  $resultRawFactory
     * @param JsonFactory                 $resultJsonFactory
     * @param LoggerInterface             $logger
     * @param Kco                         $kco
     * @param ConfigHelper                $configHelper
     * @param CheckoutSession             $checkoutSession
     * @param Escaper                     $escaper
     * @param CustomerMetadataInterface   $customerMetadata
     * @param SuccessValidator            $successValidator
     * @param OrderRepositoryInterface    $orderRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Registry $coreRegistry,
        InlineInterface $translateInline,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        LayoutFactory $layoutFactory,
        CartRepositoryInterface $quoteRepository,
        PageFactory $resultPageFactory,
        ResultLayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Kco $kco,
        ConfigHelper $configHelper,
        CheckoutSession $checkoutSession,
        Escaper $escaper,
        CustomerMetadataInterface $customerMetadata,
        SuccessValidator $successValidator,
        OrderRepositoryInterface $orderRepository
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
        $this->successValidator = $successValidator;
        $this->klarnaOrderRepository = $orderRepository;
    }

    public function execute()
    {
        $session = $this->getKco()->getCheckout();
        if (!$this->successValidator->isValid()) {
            return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
        }

        $quote = $session->getQuote();
        $order = $session->getLastRealOrder();
        $klarnaOrder = $this->klarnaOrderRepository->getByOrder($order);
        $this->_eventManager->dispatch(
            'kco_confirmation_create_order_success',
            [
                'quote'           => $quote,
                'order'           => $order,
                'klarna_order'    => $klarnaOrder,
                'klarna_order_id' => $klarnaOrder->getKlarnaOrderId(),
            ]
        );

        $session->clearQuote();
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_kco_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        // Fire off onepage success event also to handle for GA as well as anyone else that might be listening
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        return $resultPage;
    }
}
