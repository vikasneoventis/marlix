<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Model\ResourceModel\Sales\Bestsellers;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as BestsellersColleciton;
/**
 * Class Collection
 * @package Yosto\AdvancedReport\Model\ResourceModel\Sales\Bestsellers
 */
class Collection extends  BestsellersColleciton
{
    /**
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();

        //if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();
            $cols[$this->getOrderedField()] = 'SUM(' . $this->getOrderedField() . ')';
            if ($this->_from || $this->_to) {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
                $select->from($mainTable, $cols);
            } else {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
                $select->from($mainTable, $cols);
            }

            //exclude removed products
            $select->where(new \Zend_Db_Expr($mainTable . '.product_id IS NOT NULL'))->group(
                'product_id'
            )->order(
                $this->getOrderedField() . ' ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                $this->_ratingLimit
            );

            return $this;
        }

        if ('year' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } elseif ('month' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('monthly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
            $select->from($mainTable, $this->_getSelectedColumns());
        }
        if (!$this->isTotals()) {
            $select->group(['product_id']);
        }
        $select->where('rating_pos <= ?', $this->_ratingLimit);

        return $this;
    }
}