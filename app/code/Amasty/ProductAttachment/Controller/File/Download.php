<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\File;

use Amasty\ProductAttachment\Controller;
use Magento\Downloadable\Helper\Download as DownloadHelper;

class Download extends Controller\File
{

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    protected function createFileModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\File');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\Stat
     */
    protected function createStatFileModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\Stat');
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
     * @param \Amasty\ProductAttachment\Model\File $fileModel
     * @return void
     */
    protected function _processDownload($resource, $resourceType, $fileModel)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');
        $helper->setResource($resource, $resourceType);

        $contentType = $this->getContentType($helper);

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
                 ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileModel->getFileName());
        }
        $this->getResponse()
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0'); //somewhy there is the second declaration needed

        $this->saveStat($fileModel->getData());

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $helper->output();
    }

    public function execute()
    {
        $fileId = $this->getRequest()->getParam('file_id', 0);
        $productId = $this->getRequest()->getParam('product_id', 0);
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $customerId = $this->customerSession->getCustomerId();
        $storeId= $this->storeManager->getStore()->getId();

        $fileModel = $this->getFileModel()->loadByIdAndCustomerGroupIdAndOrdered($productId, $storeId, $customerId, $customerGroupId, $fileId);
        if ($fileModel->getId()) {
            $resource = '';
            $resourceType = '';

            if ($fileModel->getFileType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $fileModel->getFileUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($fileModel->getFileType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->getFilePath(
                    $this->getFileModel()->getBasePath(),
                    $fileModel->getFilePath()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType, $fileModel);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * @param \Magento\Downloadable\Helper\Download $helper
     *
     * @return string
     */
    protected function getContentType($helper)
    {
        /**
         * @var \Amasty\ProductAttachment\Helper\Config $configHelper
         */
        $configHelper = $this->_objectManager->get('Amasty\ProductAttachment\Helper\Config');

        return $configHelper->getDetectMime()
            ? $helper->getContentType() : 'application/octet-stream';
    }

    protected function saveStat($data)
    {
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }

        $customerId = $this->customerSession->getCustomerId();
        $storeId = $this->storeManager->getStore()->getId();
        $this->createStatFileModel()
            ->setData($data)
            ->setCustomerId($customerId)
            ->setStoreId($storeId)
            ->save();
    }

}
