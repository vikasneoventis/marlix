<?php

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;
/**
 * Class Address
 */
class OrderedItems extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;
    protected $resultPageFactory;
    protected $layoutFactory;
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
        PageFactory $resultPageFactory,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        LayoutFactory $layoutFactory,
        array $data = []
        ) {
        $this->_authorization = $authorization;
        $this->_dataHelper = $dataHelper;
        if(!$this->_dataHelper->isEnable() || !$this->_authorization->isAllowed('Bss_AdminPreview::config_section')){
            unset($data);
            $data = array();
        }
        $this->escaper = $escaper;
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
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
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item,$storeId);
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
    protected function prepareItem(array $item, $storeId)
    {   
        return $this->layoutFactory->create()->createBlock('Bss\AdminPreview\Block\Adminhtml\OrderedItems')->assign('order', $item)->setTemplate('Bss_AdminPreview::ordereditems.phtml')->toHtml();
    }
}
