<?php
namespace Bss\AdminPreview\Plugin\Adminhtml\Grid;

class CustomerActions
{
    protected $context;
    protected $urlBuilder;
    protected $_dataHelper;
    protected $_authorization;
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization
        )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
    }
    public function afterPrepareDataSource(
        \Magento\Customer\Ui\Component\Listing\Column\Actions $subject,
        array $dataSource
        ) {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            if($this->_dataHelper->isEnable($storeId) && $this->_dataHelper->getCustomerGridLoginColumn() == 'actions' && $this->_authorization->isAllowed('Bss_AdminPreview::login_button')){
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')] = $this->prepareItem($item,'preview');
                }
            }else{
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')] = $this->prepareItem($item);
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
    protected function prepareItem($item, $type = null)
    {   
        if($type == 'preview'){
            $urlLogin = $this->urlBuilder->getUrl('adminpreview/customer/login',['customer_id' => $item['entity_id']]);
            $urlEdit = $this->urlBuilder->getUrl('customer/index/edit',['id' => $item['entity_id']]);
            $html = '';
            $html .= '<ul style="list-style:none"><li>'.'<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="'.$urlEdit.'">'.'Edit'.'</a></li>';
            $html .= '<li><a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;'.$urlLogin.'&quot;)">'.'Login'.'</a></li>';
            $html .= '</ul>';
            return $html;
        }else{
            $urlEdit = $this->urlBuilder->getUrl('customer/index/edit',['id' => $item['entity_id']]);
            return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="'.$urlEdit.'">'.'Edit'.'</a></li>';
        }
        
    }
}