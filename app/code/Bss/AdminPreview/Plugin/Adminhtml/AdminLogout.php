<?php

namespace Bss\AdminPreview\Plugin\Adminhtml;

class AdminLogout
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
    public function beforeExecute(
        \Magento\Backend\Controller\Adminhtml\Auth $subject
        ) {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDuration(0);
 
        $this->_cookieManager->setPublicCookie(
            'adminLogged',
            '0',
            $metadata
        );
    }
}