<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection as OptionCollection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionValueCollection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class CollectQuoteItemCost implements ObserverInterface
{

    /**
     * @var OptionValueCollection
     */
    protected $optionValueCollection;

    /**
     * @var OptionCollection
     */
    protected $optionCollection;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * CollectQuoteItemCost constructor.
     * @param OptionValueCollection $optionValueCollection
     * @param OptionCollection $optionCollection
     * @param Helper $helper
     */
    public function __construct(
        OptionValueCollection $optionValueCollection,
        OptionCollection $optionCollection,
        Helper $helper
    ) {
        $this->optionValueCollection = $optionValueCollection;
        $this->optionCollection = $optionCollection;
        $this->helper = $helper;
    }

    /**
     * Add product to shopping cart action
     * Processing: weight, cost, absolute weight, absolute cost
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$this->validateItem($quoteItem)) {
            return $this;
        }

        /** @var \Magento\Framework\DataObject $buyRequest */
        $buyRequest = $quoteItem->getBuyRequest();
        /** @var array $options */
        $options = $buyRequest->getOptions();
        /** @var Product $product */
        $product = $quoteItem->getProduct();
        /** @var int|float > 0.0001 $qty */
        $qty = $this->getOriginalQtyFromBuyRequest($buyRequest);
        $originalCost = $product->getData('cost');
        $cost = 0;
        $originalWeight = $product->getData('weight');
        $weight = 0;

        $optionsItems = $this->getProductOptions($product);
        $values = $this->getValuesCollection(array_keys($options));

        foreach ($options as $optionId => $optionValue) {
            $optionCost = 0;
            $optionWeight = 0;

            $option = isset($optionsItems[$optionId]) ? $optionsItems[$optionId] : null;
            if (!$option) {
                continue;
            }

            $optionValues = $this->prepareOptionValues($optionValue);
            foreach ($optionValues as $valueId) {
                $value = isset($values[$valueId]) ? $values[$valueId] : null;
                if (!$value) {
                    continue;
                }
                $optionCost += $value->getData(Helper::KEY_COST);
                $optionWeight += $value->getData(Helper::KEY_WEIGHT);
            }

            if ($option->getData(Helper::KEY_ONE_TIME) && $this->helper->isOneTimeEnabled()) {
                $optionCost = $optionCost / $qty;
                $optionWeight = $optionWeight / $qty;
            }

            $cost += $optionCost;
            $weight += $optionWeight;
        }

        if ($this->isAbsoluteCostEnabled($product)) {
            $originalCost = 0;
        }

        if ($this->isAbsoluteWeightEnabled($product)) {
            $originalWeight = 0;
        }

        $resultCost = $originalCost + $cost;
        $resultCost = (float)$resultCost;

        if ($this->helper->isCostEnabled()) {
            $quoteItem->setBaseCost($resultCost);
        }

        if ($this->isWeightEnabled($product)) {
            $resultWeight = $originalWeight + $weight;
            $resultWeight = (float)$resultWeight;
            $resultRowWeight = $resultWeight * $qty;
            $quoteItem->setWeight($resultWeight);
            $quoteItem->setRowWeight($resultRowWeight);
        }

        return $this;
    }

    /**
     * Get all options values
     *
     * @param $optionIds
     * @return array
     */
    protected function getValuesCollection($optionIds)
    {
        $this->optionValueCollection->addOptionToFilter($optionIds);
        $values = $this->optionValueCollection->getItems();

        return $values;
    }

    /**
     * Get all product options
     *
     * @param Product $product
     * @return array
     */
    protected function getProductOptions(Product $product)
    {
        $this->optionCollection->addProductToFilter($product);
        $optionsItems = $this->optionCollection->getItems();

        return $optionsItems;
    }

    /**
     * Prepare values: explode them by ',' delimiter
     *
     * @param $optionValue
     * @return array
     */
    protected function prepareOptionValues($optionValue)
    {
        if (!is_array($optionValue)) {
            $optionValues = explode(',', $optionValue);
        } else {
            $optionValues = $optionValue;
        }

        return $optionValues;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return bool
     */
    protected function validateItem(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        if (!$this->helper->isWeightEnabled() && !$this->helper->isCostEnabled()) {
            return false;
        }

        if ($quoteItem->getParentItem() || $quoteItem->getChildren()) {
            return false;
        }

        if (!$quoteItem->getOptions()) {
            return false;
        }

        $buyRequest = $quoteItem->getBuyRequest();
        if (!$buyRequest || !$buyRequest->getOptions()) {
            return false;
        }

        return true;
    }

    /**
     * Get original item qty from the buy request
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @return float|int
     */
    protected function getOriginalQtyFromBuyRequest(\Magento\Framework\DataObject $buyRequest)
    {
        return $buyRequest->getOriginalQty() && $buyRequest->getOriginalQty() > 0.0001 ?
            (float)$buyRequest->getOriginalQty() :
            1;
    }

    /**
     * Is absolute cost enabled and enabled for specified product
     *
     * @param Product $product
     * @return bool
     */
    protected function isAbsoluteCostEnabled(Product $product)
    {
        return $product->getData(Helper::KEY_ABSOLUTE_COST) == Helper::ABSOLUTE_COST_TRUE &&
            $this->helper->isAbsoluteCostEnabled();
    }

    /**
     * Is absolute weight enabled and enabled for specified product
     *
     * @param Product $product
     * @return bool
     */
    protected function isAbsoluteWeightEnabled(Product $product)
    {
        return $product->getData(Helper::KEY_ABSOLUTE_WEIGHT) == Helper::ABSOLUTE_WEIGHT_TRUE &&
            $this->helper->isAbsoluteWeightEnabled();
    }

    /**
     * Is weight enabled and enabled for specified product
     *
     * @param Product $product
     * @return bool
     */
    protected function isWeightEnabled(Product $product)
    {
        return $product->getTypeInstance()->hasWeight() && $this->helper->isWeightEnabled();
    }
}
