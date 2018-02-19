<?php

namespace Bss\AdminPreview\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

  const XML_PATH_ENABLED = 'bss_adminpreview/general/enable';
  const XML_PATH_PRODUCT_GRID_PREVIEW_COLUMN = 'bss_adminpreview/general/product_grid_preview_column';
  const XML_PATH_CUSTOMER_GRID_LOGIN_COLUMN = 'bss_adminpreview/general/customer_grid_login_column';
  const XML_PATH_DSIABLE_PAGE_CACHE = 'bss_adminpreview/general/disable_page_cache';
  const XML_PATH_PROUDCT_GRID_COLUMNS = 'bss_adminpreview/general/product_grid_columns';
  const XML_PATH_PROUDCT_PREVIEW_TYPE_LINK = 'bss_adminpreview/general/product_preview_type_link';
  const XML_PATH_EDIT_LINKS_FRONTEND_FOR = 'bss_adminpreview/general/backend_edit_links';
  const XML_PATH_SESSION_TIMEOUT = 'admin/security/session_lifetime';


  protected $_scopeConfig;
  protected $product;
  protected $imageHelper;
  protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\Gallery\Model\Category $category
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
      \Magento\Framework\App\Helper\Context $context,
      \Magento\Catalog\Model\Product $product,
      \Magento\Catalog\Helper\Image $imageHelper
      )
    {
      $this->product = $product;
      parent::__construct($context);
      $this->_scopeConfig = $context->getScopeConfig();
      $this->imageHelper = $imageHelper;
      $this->urlBuilder = $context->getUrlBuilder();
    }

    public function isEnable($store = null)
    {
      return $this->_scopeConfig->getValue(
        self::XML_PATH_ENABLED,
        ScopeInterface::SCOPE_STORE
        );
    }

    public function getProductGridPreviewColumn()
    {
      return $this->_scopeConfig->getValue(
       self::XML_PATH_PRODUCT_GRID_PREVIEW_COLUMN,
       ScopeInterface::SCOPE_STORE
       );
    }

    public function getCustomerGridLoginColumn()
    {
      return $this->_scopeConfig->getValue(
       self::XML_PATH_CUSTOMER_GRID_LOGIN_COLUMN,
       ScopeInterface::SCOPE_STORE
       );
    }

    public function isDisablePageCache()
    {
      return $this->_scopeConfig->getValue(
       self::XML_PATH_DSIABLE_PAGE_CACHE,
       ScopeInterface::SCOPE_STORE
       );
    }

    public function getProductLinkType()
    {
      return $this->_scopeConfig->getValue(
       self::XML_PATH_PROUDCT_PREVIEW_TYPE_LINK,
       ScopeInterface::SCOPE_STORE
       );
    }

    public function getSessionTimeout()
    {
      return $this->_scopeConfig->getValue(
       self::XML_PATH_SESSION_TIMEOUT,
       ScopeInterface::SCOPE_STORE
       );
    }

    public function getProductUrl($productId,$store,$parentId,$onlyLink = null){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $url = $objectManager->get('Magento\Framework\Url');
      $product = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId($store)->load($productId);
      $pLinkType = $this->getProductLinkType();
      if($parentId != null) $parentProduct = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId($store)->load($parentId);
      if($pLinkType == 'backend'){
        if($parentId != null){
          $productUrl = $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $parentId, 'store' => $store]);
          $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$productUrl.'&quot;)">'.$product->getName().'</a>';
        }else{
          $productUrl = $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $productId, 'store' => $store]);
          $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$productUrl.'&quot;)">'.$product->getName().'</a>';
        }      
      }else{ //frontend
        if($parentId != null && $parentProduct->getVisibility() != 1 && $parentProduct->getStatus() == 1){
          $productUrl = $url->getUrl('adminpreview/preview/index', ['product_id' => $parentId, 'store' => $store]);
          $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$productUrl.'&quot;)">'.$product->getName().'</a>';
        }elseif($product->getVisibility() != 1 && $product->getStatus() == 1){
          $productUrl = $url->getUrl('adminpreview/preview/index', ['product_id' => $productId, 'store' => $store]);
          $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$productUrl.'&quot;)">'.$product->getName().'</a>';
        }else{
          $productUrl = '';
          $name = $product->getName();
        }
      }
      if($onlyLink == 1) return $productUrl;
      return $name;      
    }

    public function getProductImage($productId,$store){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
      $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
      $image = '<img src="'.$imageHelper->getUrl().'" alt="'.$product->setStore($store)->getName().'"/>';
      return $image;
    }

    public function getProductSku($id){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $product = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
      return $product->getSku();
    }

    public function getColumnsTitle(){
      return $this->_scopeConfig->getValue(
        self::XML_PATH_PROUDCT_GRID_COLUMNS,
        ScopeInterface::SCOPE_STORE
        );
    }

    public function getEditLinksFrontendFor(){
      return $this->_scopeConfig->getValue(
        self::XML_PATH_EDIT_LINKS_FRONTEND_FOR,
        ScopeInterface::SCOPE_STORE
        );
    }
    public function showLinkFrontend($type){
      if($this->getEditLinksFrontendFor() && in_array($type,explode(',', $this->getEditLinksFrontendFor()))){
        return true;
      }
      return false;
    }
    
  }