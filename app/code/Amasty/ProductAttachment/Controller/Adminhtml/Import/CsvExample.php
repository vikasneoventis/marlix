<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Downloadable\Helper\Download as DownloadHelper;

class CsvExample extends Adminhtml\Import
{

    /**
     * @return \Amasty\ProductAttachment\Helper\File
     */
    protected function getFileHelper()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\File');
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     * @return void
     */
    protected function _processDownload($resource, $resourceType)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');
        $helper->setResource($resource, $resourceType);

        $fileName = $helper->getFilename();
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

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                 ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $helper->output();
    }

    public function execute()
    {
        $resource = $this->getFileHelper()->getPathToCsvExample();
        $resourceType = DownloadHelper::LINK_TYPE_FILE;

        try {
            $this->_processDownload($resource, $resourceType);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
        }
    }
}
