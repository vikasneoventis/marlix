<?php

namespace Bss\AdminPreview\Plugin\Adminhtml;

class BeforeBlockGetContent
{
    protected $context;
    protected $urlBuilder;
    protected $_dataHelper;
    protected $_authorization;
    protected $storeManager;  
    protected $_cookieManager;
    protected $cookieMetadataFactory;
    protected $backendHelper;
    protected $layoutFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Backend\Helper\Data $backendHelper
        )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->layoutFactory = $layoutFactory;
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager; 
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->backendHelper = $backendHelper;
    }
    public function afterGetContent(
        \Magento\Cms\Model\Block $subject, $result
        ) {
        $adminLogged = $this->_cookieManager->getCookie('adminLogged');
        if($adminLogged && $this->_dataHelper->isEnable() && $this->_dataHelper->showLinkFrontend('staticblock')){
            $blockId = $subject->getId();
            $blockUrl = $this->backendHelper->getUrl('cms/block/edit', ['block_id' => $blockId]);
            $blockUrl = $this->removeSID($blockUrl);
            $urlHtml = $this->layoutFactory->create()->createBlock('Bss\AdminPreview\Block\Preview')->assign('url', $blockUrl)->setTemplate('Bss_AdminPreview::frontend_preview_staticblock.phtml')->toHtml();
            return $urlHtml .$result;
        }else{
            return $result;
        }
    }

    private function removeSID($url)
    {
        $parsed = parse_url($url);
        $base_url = strtok($url, '?');
        if (isset($parsed['query'])) {

            $query = $parsed['query'];
            parse_str($query, $params);
            unset($params['SID']);
            if(!empty($params)){
                $new_query = http_build_query($params);
                $url = $base_url.'?'.$new_query;
            }else{
                $url = $base_url;
            }
        }
        return $url;
    }
}