<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 2/21/17
 * Time: 2:38 PM
 */

namespace SM\CustomSale\Plugin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class FilterCustomSaleProduct
 *
 * @package SM\CustomSale\Plugin
 */
class FilterCustomSaleProduct {

    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;

    /**
     * FilterCustomSaleProduct constructor.
     *
     * @param \SM\CustomSale\Helper\Data $customSaleHelper
     */
    public function __construct(\SM\CustomSale\Helper\Data $customSaleHelper) {
        $this->customSaleHelper = $customSaleHelper;
    }

    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
     * @param                                                              $result
     *
     * @return mixed
     */
    public function afterGetCollection(\Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject, $result) {
        return $result->addFieldToFilter('entity_id', ['neq' => $this->customSaleHelper->getCustomSaleId()]);
    }
}