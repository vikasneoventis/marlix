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
use \MageWorx\OptionBase\Model\Entity\Group as GroupEntity;
use \Magento\Framework\Model\AbstractModel as Group;
use \MageWorx\OptionBase\Helper\Data as Helper;

class ApplyAttributesOnGroup implements ObserverInterface
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
     * @var GroupEntity
     */
    protected $groupEntity;

    /**
     * @var Group
     */
    protected $groupModel;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Group options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Group ID
     *
     * @var integer|null
     */
    protected $groupId = null;

    /**
     * @param OptionValueCollection $optionValueCollection
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param Group $groupModel
     * @param GroupEntity $groupEntity
     * @param Helper $helper
     */
    public function __construct(
        OptionValueCollection $optionValueCollection,
        ProductAttributes $productAttributes,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        Group $groupModel,
        GroupEntity $groupEntity,
        Helper $helper
    ) {
        $this->optionValueCollection = $optionValueCollection;
        $this->productAttributes = $productAttributes;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->groupEntity = $groupEntity;
        $this->groupModel = $groupModel;
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
        $this->initGroupId($observer);
        $this->initOptions($observer);

        $group = $observer->getGroup();
        $options = $group->getData('options');
        if (empty($options)) {
            return;
        }

        $this->groupEntity->setDataObject($group);

        $attributes = $this->productAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->groupEntity);
        }

        $attributes = $this->optionAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->groupEntity, $this->options);
        }

        $attributes = $this->optionValueAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->groupEntity, $this->options);
        }
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    protected function initGroupId($observer)
    {
        $this->groupId = $observer->getGroup()->getGroupId();
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    protected function initOptions($observer)
    {
        $currentOptions = $observer->getGroup()->getData('options');
        $savedOptions = $this->groupModel->load($this->groupId)->getOptions();

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
            $currentOptionSortOrder = $currentOption['sort_order'];
            $currentOptionRecordId = $currentOption['record_id'];

            $currentOptionAttributes = [];
            $optionAttributes = $this->optionAttributes->getData();
            foreach ($optionAttributes as $optionAttribute) {
                $currentOptionAttributes[] = $optionAttribute->getName();
            }

            // set data to option $saved
            $savedOptionKey = $this->helper->searchArray('sort_order', $currentOptionSortOrder, $saved);
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
                $currentValueRecordId = $currentValue['record_id'];

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
                $saved[$savedOptionKey]['values'][$savedValueKey]['record_id'] = $currentValueRecordId;
                $saved[$savedOptionKey]['values'][$savedValueKey]['mageworx_option_id'] = $saved[$savedOptionKey]['mageworx_option_id'];

                foreach ($currentValueAttributes as $currentValueAttribute) {
                    if (!isset($currentValue[$currentValueAttribute])) {
                        continue;
                    }
                    $saved[$savedOptionKey]['values'][$savedValueKey][$currentValueAttribute] = $currentValue[$currentValueAttribute];
                }
            }
        }

        return $saved;
    }
}
