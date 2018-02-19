<?php

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class ProductActions
 */
class ProductActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    protected $context;

    protected $_dataHelper;

    protected $storeManagerInterface;


    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        Data $dataHelper,
        StoreManagerInterface $storeManagerInterface,
        array $data = []
        ) {
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
        $this->_dataHelper = $dataHelper;
        if(!$this->_dataHelper->isEnable() || $this->_dataHelper->getProductGridPreviewColumn() == 'actions' || !$this->_authorization->isAllowed('Bss_AdminPreview::config_section')){
            unset($data);
            $data = array();
        }
        $this->storeManagerInterface = $storeManagerInterface;
        $this->context = $context;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($dataSource['data']['items'] as &$item) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['entity_id']);
                if($storeId) $product->setStoreId($storeId);
                if($product->getVisibility() != 1 && $product->getStatus() == 1){
                    $item[$this->getData('name')] = $this->prepareItem($item,$storeId);
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
    protected function prepareItem($item, $storeId)
    {   
        $url = $this->getProductUrl($item['entity_id'],$storeId);
        return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$url.'&quot;)">'.'Preview'.'</a>';
    }

    public function getProductUrl($product_id,$storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $objectManager->get('Magento\Framework\Url');
        return $url->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $storeId]);
    }

}
