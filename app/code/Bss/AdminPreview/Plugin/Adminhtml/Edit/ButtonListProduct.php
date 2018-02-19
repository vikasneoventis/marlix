<?php
namespace Bss\AdminPreview\Plugin\Adminhtml\Edit;

class ButtonListProduct
{
    public function beforeGetBackButtonHtml(\Magento\Catalog\Block\Adminhtml\Product\Edit $subject){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authorization = $objectManager->get('Magento\Framework\AuthorizationInterface');
        $helper = $objectManager->get('Bss\AdminPreview\Helper\Data');
        $storeId = $subject->getRequest()->getParam('store');
        if($helper->isEnable($storeId) && $authorization->isAllowed('Bss_AdminPreview::config_section')){
            $product_id = $subject->getRequest()->getParam('id');
            $subject->getToolbar()->addChild(
                'bss_preview_product',
                'Magento\Backend\Block\Widget\Button',
                [
                'label' => __('Preview'), 
                'class' => 'action-back',
                'onclick' => 'window.open(\'' . $this->getProductUrl($subject,$product_id) . '\')',
                ]
                );
        }
    }

    public function afterGetBackButtonHtml(\Magento\Catalog\Block\Adminhtml\Product\Edit $subject){
        return $subject->getToolbar()->getChildHtml('bss_preview_product');
    }

    public function getProductUrl($subject,$product_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $subject->getRequest()->getParam('store');
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $store]);
    }
}