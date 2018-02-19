<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Plugin;

use \MageWorx\OptionDependency\Model\Config;
use \Magento\Catalog\Model\Product\Type\AbstractType;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class ValidateDependenciesCartCheckout
{
    /**
     * @var Config
     */
    protected $modelConfig;

    /**
     * @var \MageWorx\OptionBase\Helper\Data
     */
    protected $baseHelper;

    /**
     * @param Config $modelConfig
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        Config $modelConfig,
        BaseHelper $baseHelper
    ) {
        $this->modelConfig = $modelConfig;
        $this->baseHelper = $baseHelper;
    }

    /**
     * Populate сheck if product can be bought dependent options
     *
     * @param AbstractType $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCheckProductBuyState(AbstractType $subject, \Closure $proceed, $product)
    {
        $infoBuyRequest = $product->getCustomOption('info_buyRequest');
        if ($this->baseHelper->checkModuleVersion('102.0.0')) {
            $value = json_decode($infoBuyRequest->getValue(), true);
        } else {
            $value = unserialize($infoBuyRequest->getValue());
        }
        $frontOptions = isset($value['options']) ? $value['options'] : [];

        if (!$product->getSkipCheckRequiredOption() && $product->getHasOptions()) {
            $options = $product->getProductOptionsCollection();
            foreach ($options as $option) {
                if ($option->getIsRequire() && !$this->isDependentHide($option, $frontOptions)) {
                    $customOption = $product->getCustomOption($subject::OPTION_PREFIX . $option->getId());
                    if (!$customOption || strlen($customOption->getValue()) == 0) {
                        $product->setSkipCheckRequiredOption(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product has required options.')
                        );
                    }
                }
            }
        }

        return [$product];
    }

    /**
     * Check if dependent option hidden
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param array $frontOptions
     * @return bool
     */
    protected function isDependentHide($option, $frontOptions)
    {
        if (!$option->getIsRequire()) {
            return true;
        }

        $allProductOptions = $this->modelConfig->allProductOptions($option->getProductId());
        $selectedValues = $this->modelConfig
            ->convertToMageworxId(
                'value',
                $this->getSelectedValues($frontOptions)
            );
        $optionParents = $this->modelConfig
            ->getOptionParents(
                $option->getProductId()
            );

        $optionMageworxId = $allProductOptions[$option->getId()];

        // 1. If object not exist in parentDependencies then it is not dependent
        // and return true.
        if (!in_array($optionMageworxId, array_keys($optionParents))) {
            return true;
        }

        $parentSelected = false;
        $parents = $optionParents[$optionMageworxId];

        // 2. If any of parents are selected - return true
        foreach ($parents as $parentValueId) {
            if (in_array($parentValueId, $selectedValues)) {
                $parentSelected = true;
                break;
            }
        }

        // current option is hidden if no one parent are selected
        if (!$parentSelected) {
            return true;
        }

        return false;
    }

    /**
     * Get selected values
     * @param array $frontOptions
     * @return array
     */
    protected function getSelectedValues($frontOptions)
    {
        $result = [];

        foreach ($frontOptions as $optionId => $values) {
            if (!is_array($values) && !is_numeric($values)) {
                continue;
            }

            if (is_numeric($values)) {
                $values = [$values];
            }

            $result = array_merge($result, $values);
        }

        return $result;
    }

    /**
     * Get selected options
     * @param array $frontOptions
     * @return array
     */
    protected function getSelectedOptions($frontOptions)
    {
        $result = [];

        foreach ($frontOptions as $optionId => $values) {
            $result[] = $optionId;
        }

        return $result;
    }
}
