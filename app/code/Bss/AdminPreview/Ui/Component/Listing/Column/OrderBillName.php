<?php

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;
/**
 * Class Address
 */
class OrderBillName extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;
    protected $urlBuilder;
    protected $_authorization;
    protected $_dataHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        array $data = []
        ) {
        $this->_authorization = $authorization;
        $this->_dataHelper = $dataHelper;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
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
            if($this->_dataHelper->isEnable() && $this->_authorization->isAllowed('Bss_AdminPreview::config_section')){
                $storeId = $this->context->getFilterParam('store_id');
                foreach ($dataSource['data']['items'] as & $item) {
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
        $customerId = $item['customer_id'];
        if($customerId){
            $url = $this->urlBuilder->getUrl('customer/index/edit', ['id' => $customerId, 'store' => $storeId]);
            return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$url.'&quot;)">'.$this->escaper->escapeHtml(
                str_replace("\n", '<br/>', $item[$this->getData('name')])
                ).'</a>';
        }else{
           return $this->escaper->escapeHtml(
            str_replace("\n", '<br/>', $item[$this->getData('name')])
            );
       }
   }
}
