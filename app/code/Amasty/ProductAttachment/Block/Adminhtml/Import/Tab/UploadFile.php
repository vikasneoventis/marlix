<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Import\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class UploadFile extends AbstractImport implements TabInterface
{

    public function getImportUrl()
    {
        return $this->getUrl('amfile/import/file');
    }

    public function getImportLabel()
    {
        return __('Upload File');
    }

    public function getImportId()
    {
        return 'amfile-import-file';
    }

    public function getImportType()
    {
        return 'import_file';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Upload Files');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Upload Files');
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