<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Reports\Controller\Adminhtml\Report\Sales as MagentoReportSales;

/**
 * Class ExportBestsellersExcel
 * @package Yosto\AdvancedReport\Controller\Adminhtml\Report\Sales
 */
class ExportBestsellersExcel extends MagentoReportSales
{
    /**
     * Export bestsellers report grid to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'bestsellers.xml';
        $grid = $this->_view->getLayout()->createBlock('Yosto\AdvancedReport\Block\Adminhtml\Report\Sales\Bestsellers\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
