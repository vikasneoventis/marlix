<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template\Context;
use Yosto\AdvancedReport\Model\ResourceModel\Revenue\CollectionFactory;
use Yosto\AdvancedReport\Helper\Data;

/**
 * Class Revenue
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report
 */
class Revenue extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var
     */
    protected $_dataHelper;

    /**
     * @var
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory,
        Data $dataHelper
    )
    {
        parent::__construct($context);
        $this->_resourceConnection = $resourceConnection;
        $this->_collectionFactory = $collectionFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Get today revenue
     *
     * @return array
     */
    public function getTodayRevenue()
    {
        $storeIds = implode(',', $this->_getStoreIds());

        $selectedDay = $this->getRequest()->getParam('day');
        $collection = $this->_collectionFactory->create();
        $todayRevenue = $collection->getTodayRevenue($storeIds, $selectedDay)->getData();
        $collection = $this->_collectionFactory->create();
        $yesterdayRevenue = $collection->getYesterdayRevenue($storeIds, $selectedDay)->getData();

        if ($todayRevenue === null || count($todayRevenue) == 0) {
            return [
                'revenue' => $this->formatPrice(0),
                'revenue_rate' => 0,
                'orders_rate' => 0,
                'sum_refunded_amount' => $this->formatPrice(0),
                'refunded_rate' => 0,
                'sum_shipping_amount' => $this->formatPrice(0),
                'shipping_rate' => 0,
                'average_revenue' => $this->formatPrice(0),
                'order_count' => 0,
                'average_revenue_rate' => 0
            ];
        } else {
            if ($yesterdayRevenue === null || count($yesterdayRevenue) == 0) {
                return [
                    'revenue' => $this->formatPrice($todayRevenue[0]['revenue']),
                    'revenue_rate' => 0,
                    'orders_rate' => 0,
                    'sum_refunded_amount' => $this->formatPrice($todayRevenue[0]['sum_refunded_amount']),
                    'refunded_rate' => 0,
                    'sum_shipping_amount' => $this->formatPrice($todayRevenue[0]['sum_shipping_amount']),
                    'shipping_rate' => 0,
                    'average_revenue' => $this->formatPrice($todayRevenue[0]['revenue'] / $todayRevenue[0]['sum_orders_count']),
                    'order_count' => $todayRevenue[0]['sum_orders_count'],
                    'average_revenue_rate' => 0
                ];
            } else {
                return [
                    'revenue' => $this->formatPrice($todayRevenue[0]['revenue']),
                    'revenue_rate' => number_format(
                        ($todayRevenue[0]['revenue'] / $yesterdayRevenue[0]['revenue'] - 1) * 100,
                        2
                    ),
                    'orders_rate' => number_format(
                        ($todayRevenue[0]['sum_orders_count'] / $yesterdayRevenue[0]['sum_orders_count'] - 1) * 100,
                        2
                    ),
                    'sum_refunded_amount' => $this->formatPrice($todayRevenue[0]['sum_refunded_amount']),
                    'refunded_rate' => $yesterdayRevenue[0]['sum_refunded_amount'] == 0
                        ? 0
                        : number_format
                        (
                            ($todayRevenue[0]['sum_refunded_amount'] / $yesterdayRevenue[0]['sum_refunded_amount'] - 1) * 100,
                            2
                        ),
                    'sum_shipping_amount' => $this->formatPrice($todayRevenue[0]['sum_shipping_amount']),
                    'shipping_rate' => $yesterdayRevenue[0]['sum_shipping_amount'] == 0
                        ? 0
                        : number_format
                        (
                            ($todayRevenue[0]['sum_shipping_amount'] / $yesterdayRevenue[0]['sum_shipping_amount'] - 1) * 100,
                            2
                        ),
                    'average_revenue' => $this->formatPrice($todayRevenue[0]['revenue'] / $todayRevenue[0]['sum_orders_count']),
                    'order_count' => $todayRevenue[0]['sum_orders_count'],
                    'average_revenue_rate' => number_format((
                            ($todayRevenue[0]['revenue'] / $todayRevenue[0]['sum_orders_count'])
                            / ($yesterdayRevenue[0]['revenue'] / $yesterdayRevenue[0]['sum_orders_count']) - 1) * 100,
                        2
                    ),
                ];
            }
        }
    }

    /**
     * Get revenue by days or interval
     *
     * @return array
     */
    public function getRevenueLastDays()
    {
        $selectedDay = $this->getRequest()->getParam('day');

        $storeIds = implode(',', $this->_getStoreIds());
        $days = $this->_dataHelper->getNumberOfDays();

        $collection = $this->_collectionFactory->create();
        $lastSevenDays = $collection->getLastDays($storeIds, 7, $selectedDay)->getData();
        $collection = $this->_collectionFactory->create();
        $lastThirtyDays = $collection->getLastDays($storeIds, 30, $selectedDay)->getData();
        $collection = $this->_collectionFactory->create();
        $betweenLastSevenAndFourteen = $collection->getRevenueByInterval($storeIds, 7, 14, $selectedDay)->getData();
        $collection = $this->_collectionFactory->create();
        $betweenLastThirtyAndSixty = $collection->getRevenueByInterval($storeIds, 30, 60, $selectedDay)->getData();
        $collection = $this->_collectionFactory->create();
        $revenueByDays = $collection->getRevenueByDays($storeIds, $days, $selectedDay)->getData();

        return [
            'sevenDays' => $lastSevenDays[0]['revenue'] !== null
                ? $this->formatPrice($lastSevenDays[0]['revenue']) : $this->formatPrice(0),
            'thirtyDays' => $lastThirtyDays[0]['revenue'] !== null
                ? $this->formatPrice($lastThirtyDays[0]['revenue']) : $this->formatPrice(0),
            'seven_fourteen_rate' => ($lastSevenDays[0]['revenue'] !== null && $betweenLastSevenAndFourteen[0]['revenue'] !== null)
                ? number_format(
                    ($lastSevenDays[0]['revenue'] / $betweenLastSevenAndFourteen[0]['revenue'] - 1) * 100,
                    2
                )
                : 0,
            'thirty_sixty_rate' => ($lastThirtyDays[0]['revenue'] !== null && $betweenLastThirtyAndSixty[0]['revenue'] !== null)
                ? number_format(
                    ($lastThirtyDays[0]['revenue'] / $betweenLastThirtyAndSixty[0]['revenue'] - 1) * 100,
                    2
                )
                : 0,
            'revenue_by_days' => $this->calculateChartForDays($revenueByDays, $days, $selectedDay),
        ];

    }

    /**
     * Get revenue by weeks
     *
     * @return array
     */
    public function getRevenueByLastFourWeeks()
    {
        $selectedDay = $this->getRequest()->getParam('day');
        $storeIds = implode(',', $this->_getStoreIds());
        $weeks = $this->_dataHelper->getNumberOfWeek();
        $collection = $this->_collectionFactory->create();
        $revenueByLastFourWeeks = $collection->getRevenueByLastWeeks($storeIds, $weeks, $selectedDay)->getData();
        return $this->calculateChartForWeek($revenueByLastFourWeeks, $weeks, $selectedDay);
    }

    /**
     * Convert data for chart
     *
     * @param $today
     * @param $collection
     * @param $duration
     * @return array
     */
    public function calculateChartForDays($collection, $duration, $today = null)
    {
        $date = date('Y-m-d');
        if ($today != null) {
            $date = date('Y-m-d', strtotime($today));
        }
        $data = [];
        foreach ($collection as $item) {
            $data[$item['period']] = $item['revenue'];
        }

        for ($i = 0; $i < $duration; $i++) {
            $pastday = date('Y-m-d', strtotime($date . ' ' . -1 * $i . ' day'));
            if (!array_key_exists($pastday, $data)) {
                $data[$pastday] = 0;
            }
        }
        ksort($data);
        return $data;
    }

    /**
     * Convert data for chart
     *
     * @param $today
     * @param $collection
     * @param int $duration
     * @return array
     */
    public function calculateChartForWeek($collection, $duration = 8, $today = null)
    {
        $day = date('w');
        if ($today != null) {
            $day = date('w', strtotime($today));
        }
        $data = [];
        foreach ($collection as $item) {
            $data[$item['week_start']] = $item['revenue'];
        }
        for ($i = 0; $i < $duration; $i++) {
            $pastWeekStart = null;
            if ($today != null) {
                $pastWeekStart = date('Y-m-d', strtotime($today . ' - ' . ($day - 1 + $i * 7) . ' days'));
            } else {
                $pastWeekStart = date('Y-m-d', strtotime(' - ' . ($day - 1 + $i * 7) . ' days'));
            }

            if (!array_key_exists($pastWeekStart, $data)) {
                $data[$pastWeekStart] = 0;
            }
        }
        ksort($data);
        return $data;
    }

    /**
     * Get allowed store ids array intersected with selected scope in store switcher
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        $filterData = $this->getFilterData();
        if ($filterData) {
            $storeIds = explode(',', $filterData->getData('store_ids'));
        } else {
            $storeIds = [];
        }
        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys($this->_storeManager->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);

        return $storeIds;
    }

    /**
     * Format price
     *
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice;
    }
}