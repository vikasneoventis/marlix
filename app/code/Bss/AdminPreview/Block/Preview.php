<?php

namespace Bss\AdminPreview\Block;

class Preview extends \Magento\Framework\View\Element\Template
{
    protected $_request;
    protected $_coreRegistry = null;
    protected $layoutFactory;
    protected $_cookieManager;
    protected $backendHelper;
    protected $page;
    protected $customerSession;
    protected $_dataHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Cms\Model\Page $page,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_layout = $context->getLayout();
        $this->_request = $context->getRequest();
        $this->_coreRegistry = $registry;
        $this->layoutFactory = $layoutFactory;
        $this->storeManager = $context->getStoreManager();
        $this->_cookieManager = $cookieManager;
        $this->backendHelper = $backendHelper;
        $this->page = $page;
        $this->_dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
    }

    public function getEditLink()
    {
        if($this->_dataHelper->isEnable()){
            $adminLogged = $this->_cookieManager->getCookie('adminLogged');
            if($this->_request->getFullActionName() == 'catalog_product_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('product')){
                $product = $this->_coreRegistry->registry('current_product');
                $product_id = $product->getId();
                $storeId = $this->storeManager->getStore()->getId();
                $url = $this->backendHelper->getUrl('catalog/product/edit', ['id' => $product_id,'store' => $storeId]);
                $type = 'product';  
            }
            if($this->_request->getFullActionName() == 'catalog_category_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('category')){
                $category = $this->_coreRegistry->registry('current_category');
                $category_id = $category->getId();
                $storeId = $this->storeManager->getStore()->getId();
                $url = $this->backendHelper->getUrl('catalog/category/edit', ['id' => $category_id,'store' => $storeId]);    
                $type = 'category';
            }
            if(($this->_request->getModuleName() == 'customer' || $this->_request->getFullActionName() == 'sales_order_history' || $this->_request->getFullActionName() == 'downloadable_customer_products' || $this->_request->getFullActionName() == 'newsletter_manage_index' || $this->_request->getFullActionName() == 'vault_cards_listaction' || $this->_request->getFullActionName() == 'review_customer_index' || $this->_request->getFullActionName() == 'paypal_billing_agreement_index' || $this->_request->getFullActionName() == 'wishlist_index_index') && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('customer')){
                if($this->customerSession->isLoggedIn()){
                    $customerId = $this->customerSession->getId();
                    $url = $this->backendHelper->getUrl('customer/index/edit', ['id' => $customerId]);
                    $type = 'customer';
                }      
            }
            if($this->_request->getModuleName() == 'cms' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('cms')){
                $pageId = $this->page->getId();
                $url = $this->backendHelper->getUrl('cms/page/edit', ['page_id' => $pageId]);
                $type = 'cms';      
            }
            if(isset($url) && $url && isset($type) && $type){
                $link = array();
                $url = $this->removeSID($url);
                $link['url'] = $url;
                $link['type'] = $type;
                return $link;
            }else{
                return;
            }
            
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
