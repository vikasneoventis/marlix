<?php
namespace BoostMyShop\BarcodeLabel\Block\Product\Edit\Tab;

class BarcodeLabel extends \Magento\Backend\Block\Template
{
    protected $_template = 'Product/Edit/Tab/BarcodeLabel.phtml';
    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    public function getLabelUrl()
    {
        return $this->getUrl('barcodelabel/product/label', ['id' => $this->getProductId()]);
    }

    public function getPrintUrl()
    {
        return $this->getUrl('barcodelabel/product/printLabel', ['id' => $this->getProductId(), 'count' => 'param_qty']);
    }

    public function getProductId()
    {
        if ($this->getData('product_id'))
            return $this->getData('product_id');
        else
        {
            return $this->_coreRegistry->registry('current_product')->getId();
        }
    }

}