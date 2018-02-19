<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Model\ResourceModel\Revenue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Yosto\AdvancedReport\Model\ResourceModel\Revenue
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Yosto\AdvancedReport\Model\Revenue',
            'Yosto\AdvancedReport\Model\ResourceModel\Revenue'
        );
    }

    /**
     * Get today revenue
     *
     * @param $storeIds
     * @return $this
     */
    public function getTodayRevenue($storeIds, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';

        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
                'sum_orders_count' => 'sum(orders_count)',
                'sum_refunded_amount' => 'sum(total_refunded_amount)',
                'sum_shipping_amount' => 'sum(total_shipping_amount)',
            ]
        )->where("DATE(period) = DATE({$selectedDate})")
            ->where("order_status != 'canceled'")
            ->where("store_id in ({$storeIds})")
            ->group('period');
        return $this;
    }

    /**
     * Get Yesterday Revenue
     *
     * @param $storeIds
     * @return $this
     */
    public function getYesterdayRevenue($storeIds, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
                'sum_orders_count' => 'sum(orders_count)',
                'sum_refunded_amount' => 'sum(total_refunded_amount)',
                'sum_shipping_amount' => 'sum(total_shipping_amount)',
            ]
        )->where("DATE(period) = (DATE({$selectedDate}) - INTERVAL 1 DAY)")
            ->where("order_status != 'canceled'")
            ->where("store_id in ({$storeIds})")
            ->group('period');
        return $this;
    }

    /**
     * Get Last Days Revenue
     *
     * @param $storeIds
     * @param $days
     * @return $this
     */
    public function getLastDays($storeIds, $days, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
            ]
        )->where(new \Zend_Db_Expr(sprintf(
            "DATE(period) >= (DATE({$selectedDate}) - INTERVAL %d DAY)",
            $days
        )))
            ->where("store_id in({$storeIds})")
            ->where("order_status != 'canceled'");
        return $this;

    }

    /**
     * Get revenue of specified days
     *
     * @param $storeIds
     * @param $days
     * @return $this
     */
    public function getRevenueByDays($storeIds, $days, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
                'period' => 'period'
            ]
        )
            ->where("DATE(period) <= DATE({$selectedDate})")
            ->where(new \Zend_Db_Expr(sprintf(
                "DATE(period) >= (DATE({$selectedDate}) - INTERVAL %d DAY)",
                $days
            )))
            ->where("store_id in({$storeIds})")
            ->where("order_status != 'canceled'")
            ->group('period')
            ->order('period DESC');
        return $this;
    }

    /**
     * Get revenue by interval
     *
     * @param $storeIds
     * @param $startDay
     * @param $endDay
     * @return $this
     */
    public function getRevenueByInterval($storeIds, $startDay, $endDay, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
            ]
        )->where(new \Zend_Db_Expr(sprintf(
            "DATE(period) >= (DATE({$selectedDate}) - INTERVAL %d DAY)",
            $endDay
        )))
            ->where(new \Zend_Db_Expr(sprintf(
                "DATE(period) < (DATE({$selectedDate}) - INTERVAL %d DAY)",
                $startDay
            )))
            ->where("store_id in({$storeIds})")
            ->where("order_status != 'canceled'");
        return $this;
    }

    /**
     * Get revenue by specified weeeks
     *
     * @param $storeIds
     * @param $weeks
     * @return $this
     */
    public function getRevenueByLastWeeks($storeIds, $weeks, $today = null)
    {
        $selectedDate = $today != null ? $today : 'NOW()';
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns(
            [
                'revenue' => 'sum(total_revenue_amount)',
                'week_start' => "STR_TO_DATE(CONCAT(YEARWEEK(period,1), ' MONDAY'), '%X%V %W')",
            ]
        )->where("DATE(period) <= DATE({$selectedDate})")
            ->where(new \Zend_Db_Expr(sprintf(
                "period >=(DATE({$selectedDate}) - INTERVAL %s WEEK )",
                $weeks
            )))
            ->where("store_id in({$storeIds})")
            ->where("order_status != 'canceled'")
            ->group('WEEK(period,1)');
        return $this;
    }

}