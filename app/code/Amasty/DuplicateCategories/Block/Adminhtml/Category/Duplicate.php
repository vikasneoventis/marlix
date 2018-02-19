<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */


namespace Amasty\DuplicateCategories\Block\Adminhtml\Category;

class Duplicate extends \Magento\Backend\Block\Widget
{
    /**
     * Category ID to duplicate
     *
     * @var integer
     */
    protected $_categoryId;
    public $_helper;

    /**
     * Define template
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\DuplicateCategories\Helper\Data $helper,
        $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Preparing block layout
     *
     * @return Duplicate
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Back'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('catalog/category') . '\'',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Duplicate'),
                'class' => 'primary',
                'onclick' => 'confirmCategoryDuplicate();',
            ]
        );
        $this->setTemplate('Amasty_DuplicateCategories::category/duplicate.phtml');

        $this->setChild('parent_category_select', $this->getLayout()->createBlock('Amasty\DuplicateCategories\Block\Adminhtml\Category\Duplicate\Categories'));

        return parent::_prepareLayout();
    }

    public function getParentCategorySelectHtml()
    {
        return $this->getChildHtml('parent_category_select');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('id' => $this->getCategoryId()));
    }
    
    public function getCategoryId()
    {
        if (!$this->_categoryId) {
            $this->_categoryId = $this->getRequest()->getParam('id');
        }
        return $this->_categoryId;
    }
}
