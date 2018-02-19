<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;
use Magento\Reports\Controller\Adminhtml\Report\Sales\Bestsellers as SalesBeseller;

/**
 * Class Bestsellers
 * @package Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales
 */
class Bestsellers extends SalesBeseller
{
    /**
     * Override parent execute
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_BESTSELLERS_FLAG_CODE, 'bestsellers');

        $this->_initAction()->_setActiveMenu(
            'Yosto_AdvancedReport::bestsellers'
        )->_addBreadcrumb(
            __('Products Bestsellers Report'),
            __('Products Bestsellers Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Best Sellers Report'));
        $chartBlock = $this->_view->getLayout()->getBlock('yosto.advancedreport.sales.bestsellers');
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_sales_bestsellers.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$chartBlock,$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_AdvancedReport::bestsellers');
    }
}