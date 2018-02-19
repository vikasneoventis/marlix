<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class ModifyPriceConfiguration.
 * This observer add stock message to options which type are select|multiselect
 *
 * @package MageWorx\OptionInventory\Observer
 */
class UpdateOptionsMessages implements ObserverInterface
{
    /**
     * Option Value Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    protected $valueCollection;

    /**
     * OptionInventory Helper Data
     *
     * @var \MageWorx\OptionInventory\Helper\Data
     */
    protected $helperData;

    /**
     * OptionInventory Stock Data
     *
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * Product custom option model
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $optionModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * UpdateOptionsMessages constructor.
     *
     * @param \MageWorx\OptionInventory\Helper\Data $helperData
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     * @param \Magento\Catalog\Model\Product\Option $optionModel
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \MageWorx\OptionInventory\Helper\Data $helperData,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper,
        \Magento\Catalog\Model\Product\Option $optionModel,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helperData = $helperData;
        $this->stockHelper = $stockHelper;
        $this->optionModel = $optionModel;
        $this->objectManager = $objectManager;
    }

    /**
     * @param EventObserver $observer
     * @return mixed
     */
    public function execute(EventObserver $observer)
    {
        $configObj = $observer->getEvent()->getData('configObj');
        $options = $configObj->getData('config');
        $optionValuesId = $this->stockHelper->getOptionValuesId($options);
        $optionValuesCollection = $this->loadOptionValues($optionValuesId);
        $optionCollection = $this->optionModel
            ->getCollection()
            ->addFieldToFilter(
                'option_id',
                ['in' => array_keys($options)]
            );

        foreach ($options as $optionId => $values) {
            $option = $optionCollection->getItemById($optionId);
            if (!is_object($option)) {
                continue;
            }

            if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                foreach ($values as $valueId => $valueData) {
                    $value = $this->getValueById($optionValuesCollection, $valueId);
                    $stockMessage = $this->stockHelper->getStockMessage($value, $option->getProductId());
                    $options[$optionId][$valueId]['stockMessage'] = $stockMessage;
                }
            }
        }

        $configObj->setData('config', $options);

        return $configObj;
    }

    /**
     * Retrieve options values by ids.
     * If OptionLink module is enabled this method will return data
     * taking into account products linked by SKU to options.
     *
     * @param int $valuesId
     * @return array
     */
    protected function loadOptionValues($valuesId)
    {
        $valueCollection = $this->objectManager
            ->get('Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory')
            ->create();

        $valueCollection
            ->addTitleToResult(1)
            ->addPriceToResult(1);

        $valueCollection->getSelect()
            ->where('main_table.option_type_id IN (?)', $valuesId);

        $options = $valueCollection
            ->load()
            ->getData();

        return $options;
    }

    /**
     * Retrieve option value by id.
     *
     * @param array $values
     * @param int $valueId
     * @return \Magento\Framework\DataObject
     */
    protected function getValueById($values, $valueId)
    {
        foreach ($values as $value) {
            if ($value['option_type_id'] == $valueId) {
                $obj = new \Magento\Framework\DataObject($value);
                return $obj;
            }
        }
    }
}
