<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */


namespace Amasty\DuplicateCategories\Block\Adminhtml\Category\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;

class DuplicateButton extends AbstractCategory implements ButtonProviderInterface
{
    public function getButtonData()
    {
        $category = $this->getCategory();
        $categoryId = (int)$category->getId();

        if ($categoryId && !in_array($categoryId, $this->getRootIds()) && $category->isDeleteable()) {
            return [
                'id' => 'duplicate_button',
                'label' => __('Duplicate Category'),
                'on_click' => "categoryDuplicate('" . $this->getDuplicateUrl(['id' => $this->getCategory()->getId()]) . "')",
                'sort_order' => 10,
                'class' => 'add'
            ];
        }

        return [];
    }

    public function getDuplicateUrl(array $args = [])
    {
        $defaultUrlParams = $this->getDefaultUrlParams();
        $params = array_merge($defaultUrlParams, $args);
        $params['store'] = 0;
        return $this->getUrl('amdupcat/duplicatecategory/duplicate', $params);
    }

    /**
     * @return array
     */
    protected function getDefaultUrlParams()
    {
        return ['_current' => true, '_query' => ['isAjax' => null]];
    }
}
