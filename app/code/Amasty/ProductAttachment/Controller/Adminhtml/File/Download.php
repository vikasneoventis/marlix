<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\File;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Downloadable\Helper\Download as DownloadHelper;

class Download extends Adminhtml\File
{

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    protected function createFileModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\File');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    protected function getFileModel()
    {
        return $this->_objectManager->get('Amasty\ProductAttachment\Model\File');
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     * @param        $fileModel
     */
    protected function _processDownload($resource, $resourceType, $fileModel)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');
        $helper->setResource($resource, $resourceType);

        $contentType = 'application/octet-stream';

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        $this->getResponse()
             ->setHeader('Content-Disposition', 'attachment ; filename=' . $fileModel->getFileName());

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $helper->output();
    }

    public function execute()
    {
        $fileId = $this->getRequest()->getParam('id', 0);

        /** @var \Magento\Downloadable\Model\Link $fileModel */
        $fileModel = $this->getFileModel()->load($fileId);
        if ($fileModel->getId()) {

            $resource = $this->_objectManager->get(
                'Magento\Downloadable\Helper\File'
            )->getFilePath(
                $this->getFileModel()->getBasePath(),
                $fileModel->getFilePath()
            );
            $resourceType = DownloadHelper::LINK_TYPE_FILE;
            try {
                $this->_processDownload($resource, $resourceType, $fileModel);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
