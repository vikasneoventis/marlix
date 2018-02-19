<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Import\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class ImportCsv extends AbstractImport implements TabInterface
{

    public function getImportUrl()
    {
        return $this->getUrl('amfile/import/csv');
    }

    public function getImportLabel()
    {
        return __('Upload Csv File');
    }

    public function getImportId()
    {
        return 'amfile-import-csv';
    }

    public function getImportType()
    {
        return 'import_csv';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Import Csv');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Import Csv');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
