<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Catalog\Controller\Adminhtml\Product
{

    public function execute()
    {
        $fileModel = $this->createFileModel();
        try {
            $type = $this->getRequest()->getParam('type');
            $storeId = $this->getRequest()->getParam('store', 0);
            $productId = $this->getRequest()->getParam('product_id');

            $resultUpload = $this->uploadFile($type);
            $configHelper = $this->getConfigHelper();

            $fileData = [
                'id'             => 0,
                'product_id'     => $productId,
                'file_type'      => 'file',
                'file_path'      => $resultUpload['file'],
                'file_name'      => $resultUpload['name'],
                'file_size'      => $resultUpload['size'],
                'store'          => [
                    'position'         => 10,
                    'label'            => $resultUpload['name'],
                    'is_visible'       => 1,
                    'show_for_ordered' => $configHelper->getShowOrderedDefault(
                    ),
                ],
                'customer_group' => $configHelper->getCustomerGroupsDefault(),
            ];

            $fileModel->saveProductAttachment($fileData, $storeId);
            $result = ['success'    => 1,
                       'label'      => $resultUpload['name'],
                       'url'        => $fileModel->getDownloadUrlBackend(),
                       'product_id' => $productId
            ];
        } catch (\Exception $e) {
            $this->rollbackCreateFile($fileModel);
            $result = ['error'     => $e->getMessage(),
                       'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @param \Amasty\ProductAttachment\Model\File $fileModel
     */
    protected function rollbackCreateFile($fileModel)
    {
        if ($fileModel->isObjectNew()) {
            $fileModel->delete();
        }
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Uploader
     */
    public function getUploader()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\Uploader');
    }

    protected function uploadFile($fileId)
    {
        return $this->getUploader()->uploadFileToAttachFolder($fileId);
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Config
     */
    protected function getConfigHelper()
    {
        return $this->_objectManager->get('Amasty\ProductAttachment\Helper\Config');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    public function createFileModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\File');
    }
}
