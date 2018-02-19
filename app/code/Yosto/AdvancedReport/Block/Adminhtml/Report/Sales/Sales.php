<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Block\Adminhtml\Report\Sales;

/**
 * Class Sales
 * @package Yosto\AdvancedReport\Block\Adminhtml\Report\Sales
 */
class Sales extends \Magento\Reports\Block\Adminhtml\Sales\Sales
{
    /**
     * Override parent construct to adjust params
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Yosto_AdvancedReport';
        $this->_controller = 'adminhtml_report';
        $this->_headerText = __('Total Ordered Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

}