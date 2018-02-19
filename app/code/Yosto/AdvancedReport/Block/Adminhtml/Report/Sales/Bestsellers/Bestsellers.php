<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report\Sales\Bestsellers;

/**
 * Class Bestsellers
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report\Sales\Bestsellers
 */
class Bestsellers extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'report/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Yosto_AdvancedReport';
        $this->_controller = 'adminhtml_report_sales_bestsellers';
        $this->_headerText = __('Products Bestsellers Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/bestsellers', ['_current' => true]);
    }
}