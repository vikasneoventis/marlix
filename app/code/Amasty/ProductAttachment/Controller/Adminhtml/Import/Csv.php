<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Framework\Controller\ResultFactory;

class Csv extends Adminhtml\Import
{

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        try {
            $uploadedFileData = $this->uploadCsv($type);
            $result = $this->getImportModel()->importFromCsv($uploadedFileData['path'], $uploadedFileData['file']);
        } catch (\Exception $e) {
            $result = ['errors' => [$e->getMessage()], 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function uploadCsv($fileId)
    {
        return $this->getUploader()->uploadFileToCsvFolder($fileId);
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Uploader
     */
    public function getUploader()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\Uploader');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\Import
     */
    public function getImportModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\Import');
    }
}
