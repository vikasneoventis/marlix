<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report\Sales\Bestsellers;

use Magento\Framework\View\Element\Template;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Reports\Model\Grouped\CollectionFactory;
use Magento\Reports\Helper\Data as ReportsHelper;

/**
 * Get data for chart
 * Class Chart
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report\Sales\Bestsellers
 */
class Chart extends Template
{
    /**
     * @var Factory
     */
    protected $_resourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ReportsHelper
     */
    protected $_reportsData;

    /**
     * @var
     */
    protected $_collection;

    /**
     * @var string
     */
    protected $_columnGroupBy = 'product_id';

    /**
     * @var null
     */
    protected $_aggregatedColumns = null;

    /**
     * @var
     */
    protected $_countSubTotals;

    /**
     * @var
     */
    protected $_subTotals;

    /**
     * @var
     */
    protected $_varTotals;


    /**
     * @var array
     */
    protected $_groupedColumn = [];

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Factory $resourceFactory
     * @param CollectionFactory $collectionFactory
     * @param ReportsHelper $reportsData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $resourceFactory,
        CollectionFactory $collectionFactory,
        ReportsHelper $reportsData,
        array $data = []
    )
    {
        $this->_resourceFactory = $resourceFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_reportsData = $reportsData;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        if (isset($this->_columnGroupBy)) {
            $this->isColumnGrouped($this->_columnGroupBy, true);
        }
    }

    /**
     * Returns custom collection resource
     *
     * @return string
     */
    public function getResourceCollectionName()
    {
        return 'Yosto\AdvancedReport\Model\ResourceModel\Sales\Bestsellers\Collection';
    }

    /**
     * @param $column
     * @param null $value
     * @return $this|bool
     */
    public function isColumnGrouped($column, $value = null)
    {
        if (null === $value) {
            if (is_object($column)) {
                return in_array($column->getIndex(), $this->_groupedColumn);
            }
            return in_array($column, $this->_groupedColumn);
        }
        $this->_groupedColumn[] = $column;
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->setCollection($this->_collectionFactory->create());
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    protected function _getAggregatedColumns()
    {
        if ($this->_aggregatedColumns === null) {
            foreach ($this->getColumns() as $column) {
                if (!is_array($this->_aggregatedColumns)) {
                    $this->_aggregatedColumns = [];
                }
                if ($column->hasTotal()) {
                    $this->_aggregatedColumns[$column->getId()] = "{$column->getTotal()}({$column->getIndex()})";
                }
            }
        }
        return $this->_aggregatedColumns;
    }

    /**
     * Get allowed store ids array intersected
     * with selected scope in store switcher
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
        // And then array_intersect with post data
        // for prevent unauthorized stores reports
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
     * @return $this|\Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();

        $from = $filterData->getData('from');

        $to = $filterData->getData('to');

        /**
         * Set default value for "from" and "to".
         * From: 2001-01-01
         * To: today
         */
        if ($from == null && $to == null) {
            $from = date("Y-m-d", strtotime('- 30 days'));
            $to = date("Y-m-d");
            $filterData->setData('period_type', 'day');
            $filterData->setData('from', $from);
            $filterData->setData('to', $to);

        }

        /*if ($filterData->getData('from') == null
            || $filterData->getData('to') == null
        ) {
            $this->_logger->debug('no input filter');
            return $this->getCollection();
        }*/

        $storeIds = $this->_getStoreIds();

        $orderStatuses = $filterData->getData('order_statuses');
        if (is_array($orderStatuses)) {
            if (count($orderStatuses) == 1 && strpos($orderStatuses[0], ',') !== false) {
                $filterData->setData('order_statuses', explode(',', $orderStatuses[0]));
            }
        }

        $resourceCollection = $this->_resourceFactory->create(
            $this->getResourceCollectionName()
        )->setPeriod(
            $filterData->getData('period_type')
        )->setDateRange(
            $filterData->getData('from', null),
            $filterData->getData('to', null)
        )->addStoreFilter(
            $storeIds
        );

        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);

        if ($filterData->getData('show_empty_rows', false)) {
            $this->_reportsData->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('from', null),
                $filterData->getData('to', null),
                $filterData->getData('period_type')
            );
        }
        if ($this->getCountTotals()) {
            $totalsCollection = $this->_resourceFactory->create(
                $this->getResourceCollectionName()
            )->setPeriod(
                $filterData->getData('period_type')
            )->setDateRange(
                $filterData->getData('from', null),
                $filterData->getData('to', null)
            )->addStoreFilter(
                $storeIds
            )->isTotals(
                false
            );

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);
        $this->_logger->debug('Go into prepare collection');
        return $this->getCollection();
    }


    /**
     * StoreIds setter
     *
     * @param array $storeIds
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @param $collection
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
    }

    /**
     * @param $collection
     * @param $filterData
     * @return $this
     */
    protected function _addOrderStatusFilter($collection, $filterData)
    {
        $collection->addOrderStatusFilter($filterData->getData('order_statuses'));
        return $this;
    }

    /**
     * @param $collection
     * @param $filterData
     * @return $this
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountTotals()
    {
        if (!$this->getTotals()) {
            $filterData = $this->getFilterData();
            $totalsCollection = $this->_resourceFactory->create(
                $this->getResourceCollectionName()
            )->setPeriod(
                $filterData->getData('period_type')
            )->setDateRange(
                $filterData->getData('from', null),
                $filterData->getData('to', null)
            )->addStoreFilter(
                $this->_getStoreIds()
            )->isTotals(
                false
            );

            $this->_addOrderStatusFilter($totalsCollection, $filterData);

            if ($totalsCollection->load()->getSize() < 1 || !$filterData->getData('from')) {
                $this->setTotals(new \Magento\Framework\DataObject());
                $this->setCountTotals(false);
            } else {
                foreach ($totalsCollection->getItems() as $item) {
                    $this->setTotals($item);
                    break;
                }
            }
        }
        return $this->_countSubTotals;
    }

    /**
     * @return array
     */
    public function getSubTotals()
    {
        $filterData = $this->getFilterData();
        $subTotalsCollection = $this->_resourceFactory->create(
            $this->getResourceCollectionName()
        )->setPeriod(
            $filterData->getData('period_type')
        )->setDateRange(
            $filterData->getData('from', null),
            $filterData->getData('to', null)
        )->addStoreFilter(
            $this->_getStoreIds()
        )->setIsSubTotals(
            false
        );

        $this->_addOrderStatusFilter($subTotalsCollection, $filterData);
        $this->_addCustomFilter($subTotalsCollection, $filterData);

        $this->setSubTotals($subTotalsCollection->getItems());
        return $this->_subTotals;
    }

    /**
     * @param bool|true $flag
     * @return $this
     */
    public function setCountSubTotals($flag = true)
    {
        $this->_countSubTotals = $flag;
        return $this;
    }

    /**
     * Return count subtotals
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCountSubTotals()
    {
        return $this->_countSubTotals;
    }

    /**
     * Set subtotal items
     *
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     */
    public function setSubTotals(array $items)
    {
        $this->_subtotals = $items;
        return $this;
    }

    /**
     * Set count totals
     *
     * @param bool $count
     * @return $this
     */
    public function setCountTotals($count = true)
    {
        $this->_countTotals = $count;
        return $this;
    }

    public function setTotals(\Magento\Framework\DataObject $totals)
    {
        $this->_varTotals = $totals;
    }

    /**
     * Retrieve totals
     *
     * @return \Magento\Framework\DataObject
     */
    public function getTotals()
    {
        return $this->_varTotals;
    }

    /**
     * return data for chart
     *
     * @return $this|\Magento\Backend\Block\Widget\Grid|Chart
     */
    public function getChartCollection()
    {
        return $this->_prepareCollection();

    }
}