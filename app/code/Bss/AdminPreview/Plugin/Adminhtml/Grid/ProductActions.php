<?php
namespace Bss\AdminPreview\Plugin\Adminhtml\Grid;

class ProductActions
{
    protected $context;
    protected $urlBuilder;
    protected $_dataHelper;
    protected $_authorization;
    protected $storeManager;  

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager; 
    }
    public function afterPrepareDataSource(
        \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject,
        array $dataSource
        ) {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            if($this->_dataHelper->isEnable($storeId) && $this->_dataHelper->getProductGridPreviewColumn() == 'actions' && $this->_authorization->isAllowed('Bss_AdminPreview::config_section')){
                foreach ($dataSource['data']['items'] as &$item) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['entity_id']);
                    if($storeId) $product->setStoreId($storeId);
                    $item[$subject->getData('name')] = $this->prepareItem($item,$storeId,$product,'preview');
                }
            }else{
                foreach ($dataSource['data']['items'] as &$item) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['entity_id']);
                    $item[$subject->getData('name')] = $this->prepareItem($item,$storeId,$product);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem($item, $storeId, $product, $type = null)
    {   
        if($type == 'preview'){
            $urlBackend = $this->getProductUrlBackend($item['entity_id'],$storeId);
            $html = '';
            $html .= '<ul style="list-style:none"><li>'.'<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="'.$urlBackend.'">'.'Edit'.'</a></li>';
            if($product->getVisibility() != 1 && $product->getStatus() == 1){
                $urlFrontend = $this->getProductUrl($item['entity_id'],$storeId);
                $html .= '<li><a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$urlFrontend.'&quot;)">'.'Preview'.'</a></li>';
            }
            $html .= '</ul>';
            return $html;
        }else{
            $urlBackend = $this->getProductUrlBackend($item['entity_id'],$storeId);
            return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="'.$urlBackend.'">'.'Edit'.'</a></li>';
        }
        
    }

    public function getProductUrl($product_id,$storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $storeId]);
    }

    public function getProductUrlBackend($product_id,$storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $this->urlBuilder->getUrl('catalog/product/edit',['id' => $product_id, 'store' => $storeId]);
        return $url;
    }

}