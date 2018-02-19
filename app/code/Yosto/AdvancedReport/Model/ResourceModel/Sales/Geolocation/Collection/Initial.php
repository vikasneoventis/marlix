<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Model\ResourceModel\Sales\Geolocation\Collection;
use Magento\Reports\Model\ResourceModel\Report\Collection;

class Initial extends Collection
{
    protected $_reportCollection = 'Yosto\AdvancedReport\Model\ResourceModel\Sales\Geolocation\Collection';

    /**
     * Set data to interval
     *
     * @return array|int
     */
    protected function _getIntervals()
    {
        if (!$this->_intervals) {
            $this->_intervals = [];
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }
            $dateStart = $this->_from;
            $dateEnd = $this->_to;
            $interval = $this->_getInterval($dateStart, $dateEnd);
            $this->_intervals[$interval['period']]= new \Magento\Framework\DataObject($interval);
        }
        return $this->_intervals;
    }

    protected function _getInterval(\DateTime $dateStart, \DateTime $dateEnd)
    {
        $interval = [
            'period' => $this->_localeDate->formatDateTime(
                $dateStart,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE
            ),
            'start' => $dateStart->format('Y-m-d 00:00:00'),
            'end' => $dateEnd->format('Y-m-d 23:59:59'),
        ];
        return $interval;
    }

    /**
     * Get data for reports
     *
     * @return array|int
     */
    public function getReports()
    {
        if (!$this->_reports) {
            $reports = [];
            foreach ($this->_getIntervals() as $index=>$interval) {
                $interval->setChildren($this->_getReport($interval->getStart(), $interval->getEnd()));
                if (count($interval->getChildren()) == 0) {
                    $interval->setIsEmpty(true);
                }
                $reports[] = $interval;
            }
            $this->_reports = $reports;
        }
        return $this->_reports;
    }

}