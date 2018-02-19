<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Model\ResourceModel\Sales\Geolocation;

use Magento\Reports\Model\ResourceModel\Customer\Totals\Collection as TotalsCollection;

/**
 * Class Collection
 * @package Yosto\AdvancedReport\Model\ResourceModel\Sales\Geolocation
 */
class Collection extends TotalsCollection
{
    /**
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->_calculateTotalsLive()
            ->joinCountryId()
            ->groupByCountry()
            ->addOrdersCount()
            ->addAttributeToFilter(
                'main_table.created_at',
                ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
            );
        return $this;
    }

    /**
     * Group by country code
     *
     * @return $this
     */
    public function groupByCountry()
    {
        $this->getSelect()->where('address.country_id IS NOT NULL')->group('address.country_id');
        return $this;
    }

    /**
     * Join country id
     *
     * @param string $alias
     * @return $this
     */
    public function joinCountryId($alias = 'country')
    {
        $fields = ['address.country_id'];
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns([$alias => $fieldConcat]);
        return $this;
    }

    /**
     * Join sales_order_address table before filter.
     */
    public function _renderFiltersBefore()
    {
        $shippingAddressJoinTable = $this->getTable('sales_order_address');
        $this->getSelect()->join(
            $shippingAddressJoinTable . ' as address',
            'main_table.billing_address_id = address.entity_id',
            ['country_id']
        );
        parent::_renderFiltersBefore();
    }
}