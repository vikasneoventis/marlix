<?php

namespace Bss\AdminPreview\Ui\Component\Edit\Product;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

/**
 * Class Preview
 */
class Preview extends Generic
{
    /**
     * @return array
     */

    protected $context;
    protected $urlBuilder;
    protected $_dataHelper;
    protected $_authorization;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
        )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
        $this->request = $request;
        $this->_coreRegistry = $registry;

    }

    public function getButtonData()
    {
        $storeId = $this->request->getParam('store');
        $product_id = $this->request->getParam('id');
        $product = $this->_coreRegistry->registry('current_product');
        if($this->_dataHelper->isEnable($storeId) && $this->_authorization->isAllowed('Bss_AdminPreview::config_section') && $product->getVisibility() != 1 && $product->getStatus() == 1){
            return [
            'label' => __('Preview'),
            'on_click' => sprintf("window.open('%s')", $this->getProductUrl($product_id,$storeId)),
            'class' => '',
            'sort_order' => 10
            ];
        }
    }

    public function getProductUrl($product_id,$storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $storeId]);
    }
}
