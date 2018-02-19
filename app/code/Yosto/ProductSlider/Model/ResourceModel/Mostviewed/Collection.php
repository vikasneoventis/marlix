<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ProductSlider\Model\ResourceModel\Mostviewed;
use Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection as MostViewedCollection;

/**
 * Class Collection
 * @package Yosto\ProductSlider\Model\ResourceModel\Mostviewed
 */
class Collection extends MostViewedCollection
{
    /**
     * @var int
     */
    protected $_ratingLimit = 5;

    /**
     * @param $ratingLimit
     * @return $this
     */
    public function setRatingLimit($ratingLimit)
    {
        $this->_ratingLimit = $ratingLimit;
        return  $this;
    }
}