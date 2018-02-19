<?php

namespace BoostMyShop\BarcodeLabel\Block\System\Config\System;

class Preview extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_label;
    protected $_productResourceModel;

    /**
     * @var string
     */
    protected $_template = 'System/Config/Preview.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\BarcodeLabel\Model\ResourceModel\Product $productResourceModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_productResourceModel = $productResourceModel;
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

    public function getBarcodeImageUrl()
    {
        return $this->getUrl('barcodelabel/product/label', ['id' => $this->getProductId()]);
    }

    protected function getProductId()
    {
        $productId = $this->_productResourceModel->getLastSimpleProductId();
        return $productId;
    }

}
