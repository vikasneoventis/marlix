<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use MageWorx\OptionInventory\Helper\Stock as StockHelper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value\CollectionFactory as OptionValueCollectionFactory;

/**
 * StockProvider model.
 * @package MageWorx\OptionInventory\Model
 */
class StockProvider
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * OptionInventory Stock helper
     *
     * @var StockHelper
     */
    protected $stockHelper;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \MageWorx\OptionBase\Helper\Data
     */
    protected $baseHelper;

    /**
     * @var OptionValueCollectionFactory
     */
    protected $optionValueCollectionFactory;

    /**
     * StockProvider constructor.
     *
     * @param ObjectManager $objectManager
     * @param StockHelper $stockHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param BaseHelper $baseHelper
     * @param OptionValueCollectionFactory $optionValueCollectionFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        StockHelper $stockHelper,
        DataObjectFactory $dataObjectFactory,
        BaseHelper $baseHelper,
        OptionValueCollectionFactory $optionValueCollectionFactory
    ) {

        $this->objectManager = $objectManager;
        $this->stockHelper = $stockHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->baseHelper = $baseHelper;
        $this->optionValueCollectionFactory = $optionValueCollectionFactory;
    }

    /**
     * Retrieve Original option values data
     *
     * @param array $requestedData Options array
     * @return array
     */
    public function getOriginData($requestedData)
    {
        $originalData = [];

        $valuesId = array_keys($requestedData);
        /** @var \MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value\Collection $valuesCollection */
        $valuesCollection = $this->optionValueCollectionFactory->create();
        $valuesCollection->getValuesByOption($valuesId);

        foreach ($valuesCollection as $value) {
            $originalData[$value->getId()] = $value;
        }

        return $originalData;
    }

    /**
     * Retrieve Requested option values data
     *
     * @param array $items Quote items array
     * @param array $cart Option array retrieved from POST
     * @return array
     */
    public function getRequestedData($items, $cart)
    {
        $requestedData = [];

        $items = !is_array($items) ? [$items] : $items;

        foreach ($items as $item) {
            $itemRequestedData = $this->getItemData($item, $cart);

            foreach ($itemRequestedData as $valueId => $valueData) {
                if (isset($requestedData[$valueId])) {
                    $value = $requestedData[$valueId];
                    $value->setQty($value->getQty() + $valueData->getQty());
                } else {
                    $requestedData[$valueId] = $valueData;
                }
            }
        }

        return $requestedData;
    }

    /**
     * Retrieve item option values data
     *
     * @param \Magento\Quote\Model\Quote\Item $item Quote item
     * @param array $cart Option array retrieved from POST
     * @return array
     */
    public function getItemData($item, $cart = [])
    {
        $requestedData = [];

        $itemInfo = $this->getItemInfo($item);
        $itemOptions = isset($itemInfo['options']) ? $itemInfo['options'] : [];
        $tempOptionValueData = [];
        foreach ($itemOptions as $optionId => $values) {
            $productOption = $item->getProduct()->getOptionById($optionId);

            // skip if no option by $optionId
            if (!$productOption) {
                continue;
            }

            // skip non-selectable options
            if (empty($productOption->getValues())) {
                continue;
            }

            // Options with multiple values
            if (is_array($values)) {
                foreach ($values as $value) {
                    $isManageStock = $productOption->getValues()[$value]->getManageStock();

                    if (!$isManageStock) {
                        continue;
                    }

                    $tempOptionValueData[$value] = [
                        'option_id' => $optionId
                    ];
                }
            } else { // One-valued options
                if (!isset($productOption->getValues()[$values])) {
                    continue;
                }

                $isManageStock = $productOption->getValues()[$values]->getManageStock();

                if (!$isManageStock) {
                    continue;
                }

                $tempOptionValueData[$values] = [
                    'option_id' => $optionId
                ];
            }
        }

        foreach ($tempOptionValueData as $valueId => $valueData) {
            $qty = $this->baseHelper->getOptionValueQty($valueId, $valueData, $item, $cart);
            $requestedData[$valueId] = $this->dataObjectFactory->create(
                [
                    'data' => [
                        'id' => $valueId,
                        'qty' => $qty
                    ]
                ]
            );
        }

        return $requestedData;
    }

    /**
     * Retrieve item info
     *
     * @param \Magento\Quote\Model\Quote\Item $item Quote Item
     * @return mixed
     */
    protected function getItemInfo($item)
    {
        $itemOptions = $item->getOptionsByCode();

        // check if this item is simple related to the configurable product
        if (isset($itemOptions['parent_product_id'])) {
            return [];
        }

        $itemInfoBuyRequest = $itemOptions['info_buyRequest'];
        $itemDataSerialized = $itemInfoBuyRequest->getData('value');
        if ($this->baseHelper->checkModuleVersion('102.0.0')) {
            $itemData = json_decode($itemDataSerialized, true);
        } else {
            $itemData = unserialize($itemDataSerialized);
        }

        return $itemData;
    }
}
