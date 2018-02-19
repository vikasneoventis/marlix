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
use Klarna\Kco\Model\Checkout\Type\Kco;
use Klarna\Kco\Model\QuoteRepository;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Psr\Log\LoggerInterface;

/**
 * Lookup country id from Klarna country code
 *
 * @package Klarna\Kco\Controller\Api
 */
class CountryLookup extends Action
{
    /**
     * @var CountryCollection
     */
    protected $countryCollection;

    /**
     * RetrieveAddress constructor.
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
     * @param CountryCollection   $countryCollection
     * @internal param RegionFactory $regionFactory
     * @internal param CollectionFactory $regionCollectionFactory
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
        Kco $kco,
        CountryCollection $countryCollection
    ) {
        parent::__construct(
            $context,
            $logger,
            $resultPageFactory,
            $resultJsonFactory,
            $jsonHelper,
            $quoteRepository,
            $mageQuoteRepository,
            $configHelper,
            $kco
        );
        $this->countryCollection = $countryCollection;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $body = $this->getRequest()->getContent();
        try {
            $data = $this->jsonHelper->jsonDecode($body);
        } catch (\Exception $e) {
            return $this->sendBadRequestResponse($e->getTraceAsString(), 500);
        }
        $data['country_id'] = $this->countryCollection->addCountryCodeFilter($data['country'])->getFirstItem()
                                                      ->getCountryId();
        $jsonResponse = $this->resultJsonFactory->create();
        $jsonResponse->setData($data);
        return $jsonResponse;
    }
}
