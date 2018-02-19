<?php

namespace Bss\AdminPreview\Ui\Component\Edit\Cms;

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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeId = $this->request->getParam('store');
        $page_id = $this->request->getParam('page_id');
        $page = $objectManager->create('\Magento\Cms\Model\Page')->load($page_id);
        if($this->_dataHelper->isEnable($storeId) && $this->_authorization->isAllowed('Bss_AdminPreview::config_section') && $page && $page->isActive()){
            return [
            'label' => __('Preview'),
            'on_click' => sprintf("window.open('%s')",$this->getCmsPageUrl($page)),
            'class' => '',
            'sort_order' => 100
            ];
        }
    }

    public function getCmsPageUrl($page)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store_id = $page->getStoreId()[0];
        $storeCode = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore($store_id)->getCode();
        $identifier = $page->getIdentifier();
        $urlManager = $objectManager->get('Magento\Framework\Url');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $url = $storeManager->getStore()->getBaseUrl().$identifier.'?___store='.$storeCode;
        return $url;
    }
}
