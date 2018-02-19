<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;
use Magento\Reports\Controller\Adminhtml\Report\Sales\Sales as ReportsSales;

/**
 * Class Sales
 * @package Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales
 */
class Sales extends ReportsSales
{
    /**
     * Override parent execute to init params fro sales.chart.
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()->_setActiveMenu(
            'Yosto_AdvancedReport::sales'
        )->_addBreadcrumb(
            __('Sales Report'),
            __('Sales Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sales Report'));
        $chartBlock = $this->_view->getLayout()->getBlock('sales.chart');
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_sales.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$chartBlock, $gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_AdvancedReport::sales_reports');
    }
}