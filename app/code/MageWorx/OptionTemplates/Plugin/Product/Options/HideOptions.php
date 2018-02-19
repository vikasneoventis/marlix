<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionTemplates\Plugin\Product\Options;

use Magento\Framework\App\RequestInterface;

class HideOptions
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     *
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     *
     * @var GroupOptionCollectionFactory
     */
    protected $groupOptionCollectionFactory;

    /**
     *
     * @var \MageWorx\OptionTemplates\Helper\Data
     */
    protected $helperData;

    /**
     *
     * @param RequestInterface $request
     * @param \MageWorx\OptionTemplates\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
     * @param \MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\CollectionFactory $groupOptionCollectionFactory
     * @param \MageWorx\OptionTemplates\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\OptionTemplates\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\CollectionFactory $groupOptionCollectionFactory,
        \MageWorx\OptionTemplates\Helper\Data $helperData
    ) {
        $this->request = $request;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groupOptionCollectionFactory = $groupOptionCollectionFactory;
        $this->helperData = $helperData;
    }

    /**
     *  Add group select to product options page
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options $subject
     * @param string $result
     * @return string
     */
    public function afterGetOptions($subject, $result)
    {
        if (!($subject instanceof \Magento\Catalog\Block\Product\View\Options)) {
            return $result;
        }

        $product = $subject->getProduct();
        if ($this->out($product, $result)) {
            return $result;
        }

        if ($subject->getData('is_hide_options')) {
            return $subject->getData('no_hide_options');
        }

        $newResult = $this->removeDisabledOptions($product->getId(), $result);
        $subject->setData('is_hide_options', 1);
        $subject->setData('no_hide_options', $newResult);

        return $newResult;
    }

    /**
     * @param int $productId
     * @param array $result
     * @return array
     */
    protected function removeDisabledOptions($productId, $result)
    {
        /** @var \MageWorx\OptionTemplates\Model\ResourceModel\Group\Collection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();

        if (!$this->helperData->isHideAllOptions()) {
            return $result;
        }

        $groupCollection->addProductFilter($productId)
            ->addFieldToFilter('is_active', ['in' => '0']);
        $groupIds = $groupCollection->getAllIds();

        $groupOptionCollection = $this->groupOptionCollectionFactory->create();
        $groupOptionCollection->addGroupToFilter($groupIds);
        $groupOptionCollection->addProductOptionToResultFilter();

        $disabledProductOptions = [];

        foreach ($groupOptionCollection as $groupOption) {
            $partDisabledProductOptionsAsString = $groupOption->getProductOptions();
            if ($partDisabledProductOptionsAsString) {
                $rawPartDisabledProductOptions = explode(',', $partDisabledProductOptionsAsString);
                $partDisabledProductOptions = array_filter($rawPartDisabledProductOptions);
                $disabledProductOptions = array_merge($disabledProductOptions, $partDisabledProductOptions);
            }
        }

        foreach ($disabledProductOptions as $disabledOptionId) {
            if (!empty($result[$disabledOptionId])) {
                unset($result[$disabledOptionId]);
            }
        }

        return $result;
    }

    /**
     * Check if go out
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $result
     * @return boolean
     */
    protected function out($product, $result)
    {
        if (!in_array($this->request->getFullActionName(), $this->getAvailableActions())) {
            return true;
        }

        if (!$product) {
            return true;
        }

        if (!$product->getId()) {
            return true;
        }

        if (!$result) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getAvailableActions()
    {
        return ['catalog_product_view'];
    }
}
