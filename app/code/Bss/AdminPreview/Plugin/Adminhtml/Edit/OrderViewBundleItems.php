<?php
namespace Bss\AdminPreview\Plugin\Adminhtml\Edit;

class OrderViewBundleItems
{
    public function afterGetSelectionAttributes(\Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer $subject, $result){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authorization = $objectManager->get('Magento\Framework\AuthorizationInterface');
        $helper = $objectManager->get('Bss\AdminPreview\Helper\Data');
        $storeId = $subject->getRequest()->getParam('store');
        if($helper->isEnable($storeId) && $authorization->isAllowed('Bss_AdminPreview::config_section')){
            $item = $subject->getItem();
            if($item->getProductType() == 'bundle'){
                $storeId = $item->getStoreId();
                $productId = $item->getProductId();
                $url = $helper->getProductUrl($productId,$storeId,null,1);
                if(is_array($result)){
                    foreach($result as $key => $value) {
                        if($key == 'option_label'){
                            $result[$key] = '<a target="_blank" href="'.$url.'">'.$value.'</a>';
                        }
                    }
                    return $result;
                }
            }
        }
        return $result;
    }
}