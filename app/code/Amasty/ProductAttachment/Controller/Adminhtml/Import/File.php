<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Framework\Controller\ResultFactory;

class File extends Adminhtml\Import
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
            $result = $this->uploadFile($type);

            $message = sprintf('File was uploaded successfully. New File Name: %s', $result['file']);
            $result = ['success' => __($message)];

        } catch (\Exception $e) {
            $result = ['errors' => [$e->getMessage()], 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function uploadFile($fileId)
    {
        return $this->getUploader()->uploadFileToFtpFolder($fileId);
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Uploader
     */
    public function getUploader()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\Uploader');
    }
}
