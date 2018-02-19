<?php

namespace Bss\AdminPreview\Ui\Component\Edit\Category;

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
    protected $storeManagerInterface;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
        )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
        $this->request = $request;
        $this->_coreRegistry = $registry;
        $this->storeManagerInterface = $storeManagerInterface;

    }

    public function getButtonData()
    {
        $storeId = $this->request->getParam('store');
        $category_id = $this->request->getParam('id');
        $category = $this->_coreRegistry->registry('current_category');
        if($this->_dataHelper->isEnable($storeId) && $this->_authorization->isAllowed('Bss_AdminPreview::config_section') && $category && $category->getIsActive() && $category->getUrlKey()){
            return [
            'label' => __('Preview'),
            'on_click' => sprintf("window.open('%s')", $this->getProductUrl($category_id,$storeId)),
            'class' => '',
            'sort_order' => 10
            ];
        }
    }

    public function getProductUrl($category_id,$storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/category', ['category_id' => $category_id, 'store' => $storeId]);
    }
}
