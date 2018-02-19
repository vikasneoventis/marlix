<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Block\Adminhtml\Report;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Yosto\TwilioSMSNotification\Model\ResourceModel\CommonLog\CollectionFactory;

/**
 * Class Dashboard
 * @package Yosto\TwilioSMSNotification\Block\Adminhtml\Report
 */
class Dashboard extends Template
{

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * Dashboard constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        DateTime $date,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
        parent::__construct($context, $data);
    }

    public function getAllTimeReport()
    {
        return $this->_collectionFactory->create()->allTimeReport();
    }

    public function countAllMessage()
    {
        return $this->_collectionFactory->create()->countAllMessage()->getFirstItem();
    }

    public function getTodayReport()
    {
        return $this->_collectionFactory->create()->todayReport();
    }

    public function countTodayMessage()
    {
        return $this->_collectionFactory->create()->countTodayMessage()->getFirstItem();
    }

    public function getLastMonthReport()
    {
        return $this->_collectionFactory->create()->lastMonthReport();
    }

    public function countLastMonthMessage()
    {
        return $this->_collectionFactory->create()->countLastMonthMessage()->getFirstItem();
    }

    public function getLastTwelveMonthReport()
    {
        return $this->_collectionFactory->create()->lastTwelveMonthReport();
    }
    public function countTwelveMonthMessage()
    {
        return $this->_collectionFactory->create()->countTwelveMonthMessage()->getFirstItem();
    }

    public function getEachMonthInLastTwelveReport()
    {
        return $this->_collectionFactory->create()->eachMonthInLastTwelveReport();
    }
}