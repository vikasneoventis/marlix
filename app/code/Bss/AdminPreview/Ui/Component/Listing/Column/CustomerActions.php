<?php

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;

/**
 * Class CustomerActions
 */
class CustomerActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    protected $_dataHelper;

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
        array $data = []
        ) {
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
        $this->_dataHelper = $dataHelper;
        if(!$this->_dataHelper->isEnable() || $this->_dataHelper->getCustomerGridLoginColumn() == 'actions' || !$this->_authorization->isAllowed('Bss_AdminPreview::login_button')){
            unset($data);
            $data = array();
        }
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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
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
    protected function prepareItem($item)
    {   
        $url = $this->urlBuilder->getUrl('adminpreview/customer/login',['customer_id' => $item['entity_id']]);
        return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$url.'&quot;)">'.'Login'.'</a>';
   }

}
