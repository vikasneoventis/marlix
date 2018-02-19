<?php

namespace BoostMyShop\BarcodeLabel\Block\System\Config\System;

class Content extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_items;

    /**
     * @var string
     */
    protected $_template = 'System/Config/Content.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\BarcodeLabel\Model\Label\Items $items,
        array $data = []
    ) {
        $this->_items = $items;
        parent::__construct($context, $data);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getFieldName($code, $field)
    {
        return 'groups[label_layout][fields][content_'.$code.'_'.$field.'][value]';
    }


    public function getItems()
    {
        return $this->_items->getDisplayableItems();
    }



}
