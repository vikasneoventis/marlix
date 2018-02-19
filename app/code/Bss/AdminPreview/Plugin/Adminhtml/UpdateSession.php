<?php

namespace Bss\AdminPreview\Plugin\Adminhtml;

class UpdateSession
{
	protected $context;
	protected $urlBuilder;
	protected $_dataHelper;
	protected $_authorization;
	protected $storeManager;  
	protected $_cookieManager;
	protected $cookieMetadataFactory;

	public function __construct(
		\Magento\Framework\View\Element\UiComponent\ContextInterface $context,
		\Magento\Framework\UrlInterface $urlBuilder,
		\Bss\AdminPreview\Helper\Data $dataHelper,
		\Magento\Framework\AuthorizationInterface $authorization,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
		\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
		)
	{
		$this->context = $context;
		$this->urlBuilder = $urlBuilder;
		$this->_dataHelper = $dataHelper;
		$this->_authorization = $authorization;
		$this->storeManager = $storeManager; 
		$this->_cookieManager = $cookieManager;
		$this->cookieMetadataFactory = $cookieMetadataFactory;
	}
	public function afterProlong(
		\Magento\Backend\Model\Auth\Session $subject
		) {
		$cookieValue = $this->_cookieManager->getCookie('admin');
		if($cookieValue){
			$lifetime = $this->_dataHelper->getSessionTimeout();
			$metadata = $this->cookieMetadataFactory
			->createPublicCookieMetadata()
			->setPath('/')
			->setDuration($lifetime);

			$this->_cookieManager->setPublicCookie(
				'adminLogged',
				'1',
				$metadata
				);
		}
	}
}