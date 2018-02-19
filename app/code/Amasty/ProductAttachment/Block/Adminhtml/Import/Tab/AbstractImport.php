<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Import\Tab;

abstract class AbstractImport extends \Magento\Backend\Block\Widget\Tab
{

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        \Amasty\ProductAttachment\Model\Import $importModel,
        \Magento\Framework\File\Size $size,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->fileHelper = $fileHelper;
        $this->fields = $importModel->getFields();
        $this->fileSize = $size;
    }

    abstract public function getImportId();

    abstract public function getImportType();

    abstract public function getImportUrl();

    abstract public function getImportLabel();

    public function getCsvExampleUrl()
    {
        return $this->getUrl('amfile/import/csvExample', ['_secure' => true]);
    }

    public function getAbsolutePathToFtpFolder()
    {
        return $this->fileHelper->getAbsolutePathToFtpFolder();
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return int
     */
    public function getMaxFileSize() {

        return $this->fileSize->getMaxFileSize();
    }

}
