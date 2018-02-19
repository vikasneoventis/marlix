<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Plugin;

use \MageWorx\OptionDependency\Model\Config;
use \Magento\Catalog\Model\Product\Option\Type\DefaultType;
use \MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

class ValidateDependenciesAddToCart
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $modelConfig;

    /**
     * @param Config $modelConfig
     */
    public function __construct(
        Config $modelConfig,
        OptionBaseHelper $helper
    ) {
        $this->modelConfig = $modelConfig;
        $this->helper = $helper;
    }

    /**
     * Validate dependencies
     * @param DefaultType $subject
     * @param array $values
     * @return array
     */
    public function beforeValidateUserValue(DefaultType $subject, $values)
    {
        $option = $subject->getOption();

        if (!$option->getIsRequire()) {
            return [$values];
        }

        $productId = $this->helper->isEnterprise() ?
            $subject->getProduct()->getRowId() :
            $subject->getProduct()->getId();

        $allProductOptions = $this->modelConfig->allProductOptions($productId);
        $selectedValues = $this->modelConfig
            ->convertToMageworxId(
                'value',
                $this->getSelectedValues($values)
            );
        $optionParents = $this->modelConfig
            ->getOptionParents(
                $productId
            );

        $optionMageworxId = $allProductOptions[$option->getId()];

        // 1. If object not exist in parentDependencies then it is not dependent
        // and return true.
        if (!in_array($optionMageworxId, array_keys($optionParents))) {
            return [$values];
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

        // if option is required and hidden (parent value not selected) - set IsRequire = false
        if (!$parentSelected) {
            $option->setIsRequire(false);
        }

        return [$values];
    }

    /**
     * Get selected values
     * @param array|null $frontOptions
     * @return array
     */
    protected function getSelectedValues($frontOptions)
    {
        $result = [];

        if (!is_array($frontOptions) || !$frontOptions) {
            return $result;
        }

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
