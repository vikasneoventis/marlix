<?php

namespace Bss\AdminPreview\Controller\Preview;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        )
    {
        $this->context = $context;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $storeId = $this->getRequest()->getParam('store');
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        if($storeId){
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $productUrl = strtok($product->setStoreId($storeId)->getUrlInStore(),'?').'?___store='.$storeCode;
        }else{
            $storeCode = $this->storeManager->getStore('0')->getCode();
            $storeId = '0';
            $productUrl = strtok($product->setStoreId($storeId)->getUrlInStore(),'?').'?___store='.$storeCode;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($productUrl);
        return $resultRedirect;

    }

}
