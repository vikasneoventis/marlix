<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Model\ResourceModel\CommonLog;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Yosto\TwilioSMSNotification\Model\ResourceModel\CommonLog
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init
        (
            'Yosto\TwilioSMSNotification\Model\CommonLog',
            'Yosto\TwilioSMSNotification\Model\ResourceModel\CommonLog'
        );
    }

    /**
     * @return $this
     */
    public function allTimeReport()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'category' => 'category',
            'total_count' => 'count(twiliosms_id)',
        ])
            ->group('category')
            ->order('total_count desc');
        return $this;
    }

    /**
     * @return $this
     */
    public function countAllMessage()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'total_count' => 'count(twiliosms_id)',
        ]);
        return $this;
    }

    /**
     * @return $this
     */
    public function todayReport()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'category' => 'category',
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= CURRENT_DATE")
            ->group('category')
            ->order('total_count desc');
        return $this;
    }

    /**
     * @return $this
     */
    public function countTodayMessage()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= CURRENT_DATE");
        return $this;
    }

    /**
     * @return $this
     */
    public function lastMonthReport()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'category' => 'category',
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= DATE_FORMAT( CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01' )")
            ->where("time < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )")
            ->group('category')
            ->order('total_count desc');
        return $this;
    }

    /**
     * @return $this
     */
    public function countLastMonthMessage()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= DATE_FORMAT( CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01' )")
            ->where("time < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )");
        return $this;
    }

    /**
     * @return $this
     */
    public function lastTwelveMonthReport()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'category' => 'category',
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= DATE_FORMAT( CURRENT_DATE - INTERVAL 12 MONTH, '%Y/%m/01' )")
            ->where("time < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )")
            ->group('category')
            ->order('total_count desc');
        return $this;
    }

    /**
     * @return $this
     */
    public function countTwelveMonthMessage()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= DATE_FORMAT( CURRENT_DATE - INTERVAL 12 MONTH, '%Y/%m/01' )")
            ->where("time < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )");
        return $this;
    }

    /**
     * @return $this
     */
    public function eachMonthInLastTwelveReport()
    {
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->columns([
            'month' => 'MONTH(time)',
            'year' => 'YEAR(time)',
            'total_count' => 'count(twiliosms_id)',
        ])
            ->where("time >= DATE_FORMAT( CURRENT_DATE - INTERVAL 12 MONTH, '%Y/%m/01' )")
            ->group('year')
            ->group('month');
        return $this;
    }
}