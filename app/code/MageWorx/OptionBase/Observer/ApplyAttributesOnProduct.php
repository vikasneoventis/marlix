<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionValueCollection;
use \MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use \MageWorx\OptionBase\Model\Entity\Product as ProductEntity;
use \MageWorx\OptionBase\Helper\Data as Helper;

class ApplyAttributesOnProduct implements ObserverInterface
{
    /**
     * @var OptionValueCollection
     */
    protected $optionValueCollection;

    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var ProductEntity
     */
    protected $productEntity;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Product options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Product ID
     *
     * @var integer|null
     */
    protected $productId = null;

    /**
     * @param OptionValueCollection $optionValueCollection
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param Product $productModel
     * @param ProductEntity $productEntity
     * @param Helper $helper
     */
    public function __construct(
        OptionValueCollection $optionValueCollection,
        ProductAttributes $productAttributes,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        Product $productModel,
        ProductEntity $productEntity,
        Helper $helper
    ) {
        $this->optionValueCollection = $optionValueCollection;
        $this->productAttributes = $productAttributes;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->productModel = $productModel;
        $this->productEntity = $productEntity;
        $this->helper = $helper;
    }

    /**
     * Save option value description
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product) {
            return;
        }

        $this->initProductId($observer);
        $this->initOptions($observer);

        $this->productEntity->setDataObject($product);

        $attributes = $this->productAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->productEntity);
        }

        $attributes = $this->optionAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->productEntity, $this->options);
        }

        $attributes = $this->optionValueAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->productEntity, $this->options);
        }
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    protected function initProductId($observer)
    {
        $this->productId = $observer->getEvent()->getProduct()->getId();
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    protected function initOptions($observer)
    {
        $currentOptions = $observer->getProduct()->getOptions();
        $savedOptions = $this->productModel->load($observer->getProduct()->getId())->getOptions();

        $currentOptions = $this->helper->beatifyOptions($currentOptions);
        $savedOptions = $this->helper->beatifyOptions($savedOptions);

        $this->options = $this->mergeArrays($currentOptions, $savedOptions);
    }

    /**
     * Merge current and saved arrays
     *
     * @param array $current
     * @param array $saved
     * @return $this
     */
    protected function mergeArrays($current, $saved)
    {
        foreach ($current as $currentOption) {
            if (!empty($currentOption['is_delete'])) {
                continue;
            }
            $currentOptionId = $currentOption['option_id'];
            $currentOptionRecordId = isset($currentOption['record_id']) ? $currentOption['record_id'] : $currentOption['option_id'];

            $currentOptionAttributes = [];
            $optionAttributes = $this->optionAttributes->getData();
            foreach ($optionAttributes as $optionAttribute) {
                $currentOptionAttributes[] = $optionAttribute->getName();
            }

            // set data to option $saved
            $savedOptionKey = $this->helper->searchArray('option_id', $currentOptionId, $saved);
            if ($savedOptionKey === null) {
                continue;
            }
            $saved[$savedOptionKey]['record_id'] = $currentOptionRecordId;
            foreach ($currentOptionAttributes as $currentOptionAttribute) {
                if (!isset($currentOption[$currentOptionAttribute])) {
                    continue;
                }
                $saved[$savedOptionKey][$currentOptionAttribute] = $currentOption[$currentOptionAttribute];
            }

            $currentValues = isset($currentOption['values']) ? $currentOption['values'] : [];
            foreach ($currentValues as $currentValue) {
                $currentValueSortOrder = $currentValue['sort_order'];
                $currentValueRecordId = isset($currentValue['record_id']) ? $currentValue['record_id'] : $currentValue['option_id'];

                $currentValueAttributes = [];
                $valueAttributes = $this->optionValueAttributes->getData();
                foreach ($valueAttributes as $valueAttribute) {
                    $currentValueAttributes[] = $valueAttribute->getName();
                }

                // set data to option $saved
                $savedValueKey = $this->helper->searchArray('sort_order', $currentValueSortOrder, $saved[$savedOptionKey]['values']);
                if ($savedValueKey === null) {
                    continue;
                }

                $optionValue = &$saved[$savedOptionKey]['values'][$savedValueKey];
                $optionValue['record_id'] = $currentValueRecordId;
                $optionValue['mageworx_option_id'] = $saved[$savedOptionKey]['mageworx_option_id'];
                foreach ($currentValueAttributes as $currentValueAttribute) {
                    if (!isset($currentValue[$currentValueAttribute])) {
                        continue;
                    }
                    $optionValue[$currentValueAttribute] = $currentValue[$currentValueAttribute];
                }
            }
        }

        return $saved;
    }
}
