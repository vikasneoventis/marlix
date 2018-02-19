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
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Psr\Log\LoggerInterface;

/**
 * Create a proper address from data
 *
 * @package Klarna\Kco\Controller\Api
 */
class RetrieveAddress extends Action
{
    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var CollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @var Region
     */
    protected $region;

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
     * @param RegionFactory       $regionFactory
     * @param CollectionFactory   $regionCollectionFactory
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
        RegionFactory $regionFactory,
        CollectionFactory $regionCollectionFactory
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
        $this->regionFactory = $regionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->region = $this->regionFactory->create();
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
            return $this->sendBadRequestResponse($e->getMessage(), 500);
        }
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->getRegionId($data['region_name'], $data['country_id']);
        }
        if (!isset($data['region_name'])) {
            $data['region_name'] = $this->getRegionName($data['region_id']);
        }
        $jsonResponse = $this->resultJsonFactory->create();
        $jsonResponse->setData($data);
        return $jsonResponse;
    }

    /**
     * Get the region id for given region name and country id
     *
     * @param string $regionName
     * @param string $countryId
     * @return int
     */
    protected function getRegionId($regionName, $countryId)
    {
        /** @var \Magento\Directory\Model\Region $region */
        $this->region = $this->regionFactory->create()->loadByName($regionName, $countryId);
        return $this->region->getId();
    }

    /**
     * Get region name for given ID
     *
     * @param int $regionId
     * @return string
     */
    protected function getRegionName($regionId)
    {
        if ($this->region->getId() !== $regionId) {
            $this->region = $this->lookupRegionById($regionId);
        }
        return $this->region->getName();
    }

    /**
     * Lookup region by ID
     *
     * @param int $regionId
     * @return \Magento\Framework\DataObject
     */
    protected function lookupRegionById($regionId)
    {
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', $regionId);
        $regionCollection->setPageSize(1);
        $regionCollection->setCurPage(1);
        return $regionCollection->getFirstItem();
    }
}
