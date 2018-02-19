<?php
namespace Bss\AdminPreview\Plugin\Adminhtml\Edit;

class ButtonList
{

    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
        )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authorization = $objectManager->get('Magento\Framework\AuthorizationInterface');
        $helper = $objectManager->get('Bss\AdminPreview\Helper\Data');
        $storeId = $subject->getRequest()->getParam('store');
        if($helper->isEnable($storeId) && $authorization->isAllowed('Bss_AdminPreview::config_section')){
            if($subject->getRequest()->getFullActionName() == 'cms_page_edit'){
                $page_id = $subject->getRequest()->getParam('page_id');
                if($page_id){
                    $buttonList->add(
                        'bss_cms_preview',
                        [
                        'label' => __('Preview'),
                        'onclick' => 'window.open(\'' . $this->getCmsPageUrl($page_id) . '\')',
                        'class' => 'ship'
                        ]
                        ); 
                } 
            }
            if($subject->getRequest()->getFullActionName() == 'catalog_category_edit'){
                $cat_id = $subject->getRequest()->getParam('id');
                if($cat_id){
                    $buttonList->add(
                        'bss_category_preview',
                        [
                        'label' => __('Preview'),
                        'onclick' => 'window.open(\'' . $this->getCategoryUrl($subject,$cat_id) . '\')',
                        'class' => 'ship'
                        ]
                        ); 
                } 
            }
        }

        return $buttonList;
    }

    public function getCmsPageUrl($page_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $page = $objectManager->create('\Magento\Cms\Model\Page')->load($page_id);
        $store_id = $page->getStoreId()[0];
        $storeCode = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore($store_id)->getCode();
        $identifier = $page->getIdentifier();
        $urlManager = $objectManager->get('Magento\Framework\Url');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $url = $storeManager->getStore()->getBaseUrl().$identifier.'?___store='.$storeCode;
        return $url;
    }

    public function getCategoryUrl($subject,$cat_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $subject->getRequest()->getParam('store');
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/category', ['cat_id' => $cat_id, 'store' => $store]);
    }

}
