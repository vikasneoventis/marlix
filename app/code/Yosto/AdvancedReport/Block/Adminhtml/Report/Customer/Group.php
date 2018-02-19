<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory;
use Yosto\AdvancedReport\Model\ResourceModel\Customer\Group\CollectionFactory;

/**
 * Block for data customer group report
 *
 * Class Group
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report\Customer
 */
class Group extends Template
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $_resourceFactory;
    protected $_collectionFactory;
    protected $_collection;
    protected $_varNameFilter = 'filter';
    protected $_saveParametersInSession;
    protected $_backendSession;
    protected $_filters = [];
    protected $_defaultFilters = ['report_from' => '', 'report_to' => '', 'report_period' => 'day'];
    protected $_defaultFilter = [];

    /**
     * @param Template\Context $context
     * @param Factory $resourceFactory
     * @param CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Factory $resourceFactory,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_resourceFactory = $resourceFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return collection for group chart
     *
     * @return $this
     */
    public function getCollection()
    {
        $this->_prepareCollection();

        $reportFrom = $this->getFilter('report_from');

        $reportTo = $this->getFilter('report_to');
        /**
         * Set default value for "from" and "to".
         * From: 2001-01-01
         * To: today
         */
        if($this->getFilter('report_from')==null && $this->getFilter('report_to')==null){
            $reportTo = date("Y-m-d");
            $reportFrom = date("Y-m-d",strtotime('20010101'));
        }

        $from = $this->_localeDate->scopeDate(
            null,
            $reportFrom,
            false
        );
        $to = $this->_localeDate->scopeDate(
            null,
            $reportTo,
            false
        );

        $collection = $this->_collectionFactory->create()
            ->setDateRange(
                $from,
                $this->getFilter('report_to') ? $to->format('Y-m-d 23:59:59') : $to
            )->addFieldToFilter('store_id', ['in' => $this->_getAllowedStoreIds()])
            ->addExpressionFieldToSelect(
                'orders_sum_amount',
                'SUM(
                {{main_table.base_subtotal}}
                - IFNULL({{main_table.base_subtotal_canceled}}, 0)
                - IFNULL({{main_table.base_subtotal_refunded}}, 0)
                - ABS({{main_table.base_discount_amount}})
                - IFNULL({{main_table.base_discount_canceled}}, 0)
                )',
                [
                    'main_table.base_subtotal' => 'main_table.base_subtotal',
                    'main_table.base_subtotal_canceled' => 'main_table.base_subtotal_canceled',
                    'main_table.base_subtotal_refunded' => 'main_table.base_subtotal_refunded',
                    'main_table.base_discount_amount' => 'main_table.base_discount_amount',
                    'main_table.base_discount_canceled' => 'main_table.base_discount_canceled'
                ]
            );
        return $collection;
    }

    /**
     * Prepare filter params for collection
     */
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (null === $filter) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = [];
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);

            if (!isset($data['report_from'])) {
                // getting all reports from 2001 year
                $date = (new \DateTime())->setTimestamp(mktime(0, 0, 0, 1, 1, 2001));
                $data['report_from'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            if (!isset($data['report_to'])) {
                // getting all reports from 2001 year
                $date = new \DateTime();
                $data['report_to'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (0 !== sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }
    }


    /**
     * @return string
     */
    public function getVarNameFilter()
    {
        return $this->_varNameFilter;
    }

    /**
     * Get param, set param to backend session
     *
     * @param $paramName
     * @param null $default
     * @return mixed|null
     */
    public function getParam($paramName, $default = null)
    {
        $sessionParamName = $this->getId() . $paramName;
        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            if ($this->_saveParametersInSession) {
                $this->_backendSession->setData($sessionParamName, $param);
            }
            return $param;
        } elseif ($this->_saveParametersInSession && ($param = $this->_backendSession->getData($sessionParamName))) {
            return $param;
        }

        return $default;
    }

    /**
     * @param $name
     * @return string
     */
    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return $this->getRequest()->getParam($name)
                ? htmlspecialchars($this->getRequest()->getParam($name))
                : '';
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    /**
     * @param $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            $this->setFilter($name, $data[$name]);
        }
        return $this;
    }

    /**
     * Get store ids to filter
     *
     * @return array
     */
    protected function _getAllowedStoreIds()
    {
        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = [];
        if ($this->getRequest()->getParam('store')) {
            $storeIds = [$this->getParam('store')];
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager
                ->getWebsite($this->getRequest()->getParam('website'))
                ->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $storeIds = $this->_storeManager->getGroup(
                $this->getRequest()->getParam('group')
            )->getStoreIds();
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
}