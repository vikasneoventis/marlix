<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report\Customer;

/**
 * Class Totals
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report\Customer
 */
class Totals extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Yosto_AdvancedReport';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Yosto_AdvancedReport';
        $this->_controller = 'adminhtml_report_customer_group';
        $this->_headerText = __('Customers by Orders Total');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}