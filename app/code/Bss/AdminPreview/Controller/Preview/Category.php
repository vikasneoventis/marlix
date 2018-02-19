<?php

namespace Bss\AdminPreview\Controller\Preview;

use Magento\Framework\Controller\ResultFactory;

class Category extends \Magento\Framework\App\Action\Action
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
        $catId = $this->getRequest()->getParam('category_id');
        $storeId = $this->getRequest()->getParam('store');
        $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($catId);
        if($storeId){
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $categoryUrl = strtok($category->setStoreId($storeId)->getUrl(),'?').'?___store='.$storeCode;
        }else{  
            $storeCode = $this->storeManager->getStore('0')->getCode();
            $storeId = '0';
            $categoryUrl = strtok($category->setStoreId($storeId)->getUrl(),'?').'?___store='.$storeCode;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($categoryUrl);
        return $resultRedirect;

    }

}
