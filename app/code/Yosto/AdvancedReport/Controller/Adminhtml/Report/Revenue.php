<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Controller\Adminhtml\Report;

use Magento\Reports\Model\Flag;
use Magento\Reports\Controller\Adminhtml\Report\Sales\Sales;

class Revenue extends Sales
{
    /**
     * Override parent execute to init chart block
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()->_setActiveMenu(
            'Yosto_AdvancedReport::revenue'
        )->_addBreadcrumb(
            __('Sales Report'),
            __('Sales Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Revenue Report'));
        $chartBlock = $this->_view->getLayout()->getBlock('yosto.advanced.report.revenue');

        $this->_initReportAction([$chartBlock]);

        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_AdvancedReport::revenue');
    }

}