<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Model\ResourceModel\Customer\Group;

use Magento\Reports\Model\ResourceModel\Order\Collection as OrderCollection;

class Collection extends OrderCollection
{
    /**
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->_calculateTotalsLive()
            ->joinGroupCode()
            ->addOrdersCount()
            ->addAttributeToFilter(
                'main_table.created_at',
                ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
            )->groupByGroupId();
        return $this;
    }

    /**
     * Join group id
     *
     * @return $this
     */
    public function groupByGroupId()
    {
        $this->getSelect()->where('main_table.customer_group_id IS NOT NULL')
            ->group('main_table.customer_group_id');
        return $this;
    }

    /**
     * Join customer_group_code
     *
     * @param string $alias
     * @return $this
     */
    public function joinGroupCode($alias = 'customer_group_code')
    {
        $fields = ['customergroup.customer_group_code'];
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns([$alias => $fieldConcat]);
        return $this;
    }

    /**
     * @param \int[] $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', ['in' => (array)$storeIds]);
            $this->addSumAvgTotals(1)->orderByTotalAmount();
        } else {
            $this->addSumAvgTotals()->orderByTotalAmount();
        }

        return $this;
    }

    /**
     * Join table before filter
     */
    public function _renderFiltersBefore()
    {
        $shippingAddressJoinTable = $this->getTable('customer_group');
        $this->getSelect()->join(
            $shippingAddressJoinTable . ' as customergroup',
            'main_table.customer_group_id = customergroup.customer_group_id',
            ['customer_group_id']
        );
        parent::_renderFiltersBefore();
    }

    /**
     * Set date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset()->_joinFields($fromDate, $toDate);
        return $this;
    }
}