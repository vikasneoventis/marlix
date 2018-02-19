<?php

namespace Bss\AdminPreview\Controller\Preview;

use Magento\Framework\Controller\ResultFactory;

class AdminLogined extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_dataHelper;
    protected $cookieMetadataFactory;
    protected $_cookieManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
        )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function execute()
    {
        $lifeTime = $this->_dataHelper->getSessionTimeout();    
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDuration($lifeTime);
 
        $this->_cookieManager->setPublicCookie(
            'adminLogged',
            '1',
            $metadata
        );


        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());      
        return $resultRedirect;

    }

}
