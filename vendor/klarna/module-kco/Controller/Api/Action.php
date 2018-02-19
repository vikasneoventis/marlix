<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Controller\Api;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Traits\CommonController;
use Klarna\Kco\Model\QuoteRepository;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Framework\App\Action\Action as BaseAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Abstract base class
 *
 * @package Klarna\Kco\Controller\Api
 */
abstract class Action extends BaseAction
{
    use CommonController;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var MageQuoteRepository
     */
    protected $mageQuoteRepository;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Kco
     */
    protected $kco;

    /**
     * Action constructor.
     *
     * @param Context             $context
     * @param LoggerInterface     $logger
     * @param PageFactory         $resultPageFactory
     * @param JsonFactory         $resultJsonFactory
     * @param JsonHelper          $jsonHelper
     * @param QuoteRepository     $quoteRepository
     * @param MageQuoteRepository $mageQuoteRepository
     * @param ConfigHelper        $configHelper
     * @param Kco                 $kco
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        JsonHelper $jsonHelper,
        QuoteRepository $quoteRepository,
        MageQuoteRepository $mageQuoteRepository,
        ConfigHelper $configHelper,
        Kco $kco
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
        $this->quoteRepository = $quoteRepository;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->configHelper = $configHelper;
        $this->kco = $kco;
    }
}
