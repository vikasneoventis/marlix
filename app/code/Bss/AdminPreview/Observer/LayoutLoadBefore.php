<?php
namespace Bss\AdminPreview\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LayoutLoadBefore implements ObserverInterface
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
		\Magento\Framework\View\Element\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\View\LayoutFactory $layoutFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Model\Session $backendSession,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Cms\Model\Page $page,
		\Bss\AdminPreview\Helper\Data $dataHelper,
		\Magento\Customer\Model\Session $customerSession
		) {
		$this->_layout = $context->getLayout();
		$this->_request = $context->getRequest();
		$this->_coreRegistry = $registry;
		$this->layoutFactory = $layoutFactory;
		$this->storeManager = $storeManager;
		$this->_cookieManager = $cookieManager;
		$this->backendHelper = $backendHelper;
		$this->page = $page;
		$this->_dataHelper = $dataHelper;
		$this->customerSession = $customerSession;
	}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{	
		$layout = $observer->getData('layout');
		if($this->_dataHelper->isEnable()){
			$adminLogged = $this->_cookieManager->getCookie('adminLogged');
			if($this->_request->getFullActionName() == 'catalog_product_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('product')){	
				$layout->getUpdate()->addHandle('bss_adminpreview_editlink');
			}
			if($this->_request->getFullActionName() == 'catalog_category_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('category')){
				$layout->getUpdate()->addHandle('bss_adminpreview_editlink');      
			}
			if(($this->_request->getModuleName() == 'customer' || $this->_request->getFullActionName() == 'sales_order_history' || $this->_request->getFullActionName() == 'downloadable_customer_products' || $this->_request->getFullActionName() == 'newsletter_manage_index' || $this->_request->getFullActionName() == 'vault_cards_listaction' || $this->_request->getFullActionName() == 'review_customer_index' || $this->_request->getFullActionName() == 'paypal_billing_agreement_index' || $this->_request->getFullActionName() == 'wishlist_index_index') && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('customer')){
				if($this->customerSession->isLoggedIn()){
				$layout->getUpdate()->addHandle('bss_adminpreview_editlink');
				}      
			}
			if($this->_request->getModuleName() == 'cms' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('cms')){
				$layout->getUpdate()->addHandle('bss_adminpreview_editlink');        
			}
		}
	}

}