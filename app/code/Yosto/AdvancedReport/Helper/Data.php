<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Helper;

/**
 * Class Data
 * @package Yosto\AdvancedReport\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get number of days to show on report
     *
     * @param null $storeId
     * @return mixed
     */
    public function getNumberOfDays($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'reports/advanced_report/revenue/days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get number of weeks to show on report
     *
     * @param null $storeId
     * @return mixed
     */
    public function getNumberOfWeek($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'reports/advanced_report/revenue/weeks',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}