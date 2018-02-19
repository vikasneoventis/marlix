<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

/**
 * Class File
 *
 * @package Amasty\ProductAttachment\Model
 */
class File extends \Magento\Framework\Model\AbstractModel
{

    const NEW_FILE_STATUS = 'new';
    const OLD_FILE_STATUS = 'old';

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlFrontendBuilder;
    /**
     * @var Icon
     */
    protected $iconModel;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        Icon $iconModel,
        \Magento\Backend\Model\UrlFactory $urlFactory,
        \Magento\Framework\UrlInterface $urlFrontendBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context, $registry, $resource, $resourceCollection, $data
        );

        $this->iconModel = $iconModel;
        $this->fileHelper = $fileHelper;
        
        $this->urlFactory = $urlFactory;
        $this->urlFrontendBuilder = $urlFrontendBuilder;

    }



    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\ProductAttachment\Model\ResourceModel\File');
    }

    /**
     * @param array $fileData
     * @param int   $storeId
     *
     * @return array
     */
    public function saveProductAttachment($fileData, $storeId)
    {
        $this->_loadFileAndDeleteId($fileData);
        $this->_addFileData($fileData);

        if ($this->getIsDelete() == 1) {
            $this->delete();
        } else {
            $uploadData = $this->getData('file/0');
            if (!empty($uploadData)
                && $uploadData['status'] == static::NEW_FILE_STATUS
            ) {
                $newFilePath = $this->moveFileFromTmpToBase(
                    $uploadData['file']
                );
                $this->setFilePath($newFilePath);
                $this->setFileSize($uploadData['size']);
            }

            $this->_saveFileData();

            if ($storeId != 0 && $this->isObjectNew()) {
                $this->_saveFileStoreData(0);
            }
            $this->_saveFileStoreData($storeId);
            $this->_saveFileCustomersGroupData($storeId);

        }

        return $this;
    }

    public function moveFileFromTmpToBase($fileReference)
    {
        $result = $this->fileHelper->moveFileFromTo(
            $fileReference,
            $this->getBaseTmpPath(),
            $this->getBasePath()
        );
        return $result;
    }

    public function copyFileFromFtpToBaseIfNotCopied($fileReference)
    {

        $key = 'copy_'.$fileReference;
        if (!$this->_registry->registry($key)) {
            $result = $this->copyFileFromFtpToBase($fileReference);
            $this->_registry->register($key, $result);
        } else {
            $result = $this->_registry->registry($key);
        }
        return $result;
    }

    public function copyFileFromFtpToBase($fileReference)
    {
        $result = $this->fileHelper->copyFileFromTo(
            $fileReference,
            $this->getBaseFtpPath(),
            $this->getBasePath()
        );
        return $result;
    }

    protected function _loadFileAndDeleteId(&$fileData)
    {
        if (!isset($fileData['id']))
            return;

        $fileId = $fileData['id'];
        if ($fileId > 0) {
            $this->load($fileId);
        }
        unset($fileData['id']);
    }

    protected function _addFileData($fileData)
    {
        $this->addData($fileData);
    }

    protected function _saveFileData()
    {
        $this->save();
    }

    protected function _saveFileStoreData($storeId)
    {
        $fileStoreData = $this->getStore();

        if (!$fileStoreData) {
            $fileStoreData = $this->getData();

            $fileStoreData = array_intersect_key($fileStoreData, array_flip([
                'label', 'is_visible', 'position', 'show_for_ordered'
            ]));
        }

        $fileStoreData['store_id'] = $storeId;
        $fileStoreData['file_id'] = $this->getId();

        if ($storeId != 0) {
            // TODO use null values instead of this...
            foreach ($fileStoreData as $key => $value) {
                if ($value && (0 === strpos($key, 'use_default_'))) {
                    $fieldName = substr($key, 12);

                    if ($fieldName == 'label') {
                        $defaultValue = '';
                    }
                    else {
                        $defaultValue = -1;
                    }

                    $this->setData($fieldName, $defaultValue);
                }
            }
            $fileStoreData['customer_group_is_default'] = (bool)$this->getData('use_default_customer_group');
        }

        $resource = $this->getResource();

        $resource->getConnection()->insertOnDuplicate(
            $resource->getTable('amasty_file_store'),
            $fileStoreData
        );
    }

    protected function _saveFileCustomersGroupData($storeId)
    {
        $fileCustomersGroupData = $this->getCustomerGroup();
        $resource = $this->getResource();

        $resource->getConnection()->delete(
            $resource->getTable('amasty_file_customer_group'),
            sprintf('file_id = %d AND store_id = %d', $this->getId(), $storeId)
        );
        if (is_array($fileCustomersGroupData)) {
            foreach ($fileCustomersGroupData as $customerGroupId) {
                $fileCustomerGroupData = [];

                $fileCustomerGroupData['customer_group_id'] = $customerGroupId;
                $fileCustomerGroupData['store_id'] = $storeId;
                $fileCustomerGroupData['file_id'] = $this->getId();

                $resource->getConnection()->insertOnDuplicate(
                    $resource->getTable('amasty_file_customer_group'),
                    $fileCustomerGroupData
                );
            }
        }
    }

    protected function _parseFileDateFromJson($json)
    {
        $data = json_decode($json, true);

        return empty($data)
            ? []
            : [
                'file_path' => $data[0]['file'],
                'file_size' => $data[0]['size'],
                'file_name' => $data[0]['name'],
                'file_status' => $data[0]['status'],
            ];
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        $fileExtension = $this->getFileExtension();

        return $this->iconModel->getIconUrlByExtension($fileExtension);

    }

    /**
     * @return mixed|string
     */
    public function getFileExtension()
    {
        $filePath = $this->getFileUrl() ?: $this->getFilePath();

        return $this->getFileExtensionByFilePath($filePath);
    }

    /**
     * @param $filePath
     *
     * @return mixed|string
     */
    public function getFileExtensionByFilePath($filePath)
    {
        return $this->fileHelper->getFileExtension(
            $this->getBasePath(), $filePath
        );
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        $fileSize = 0;
        if ($this->getFilePath() && $this->getData('file_size')) {
            $fileSize = $this->getData('file_size');
        } elseif ($this->getFilePath()) {
            $fileSize = $this->fileHelper->getFileSize(
                $this->getBasePath(), $this->getFilePath()
            );
        } elseif ($this->getFileUrl()) {
            //magento has not method to get size of filt by url
            // we can't use functions fopen and stream_get_meta_data
            $fileSize = 0;
        }
        return $fileSize;
    }

    /**
     * @return string
     */
    public function getFullFileSize()
    {
        if ($this->getData('full_size') !== null) {
            return $this->getData('full_size');
        }

        $fileSize = $this->getFileSize();
        if ($fileSize >= pow(2, 20)) {
            $fullFileSize = sprintf('%.2f MB', $fileSize / pow(2, 20));
        } elseif ($fileSize >= pow(2, 10)) {
            $fullFileSize = sprintf('%.2f KB', $fileSize / pow(2, 10));
        } else {
            $fullFileSize = sprintf('%.2f B', $fileSize);
        }
        $this->setData('full_size', $fullFileSize);
        return $fullFileSize;
    }

    public function loadByIdAndCustomerGroupIdAndOrdered($productId, $storeId, $customerId, $customerGroupId, $fileId)
    {

        /**
         * @var ResourceModel\File\Collection $collection
         */
        $collection = $this->getCollection();

        return $collection->loadByIdAndCustomerGroupIdAndOrdered(
            $productId, $storeId, $customerId, $customerGroupId,
            $fileId
        );

    }

    public function saveProductAttachmentFromCsv($csvData, $storeId)
    {
        $this->addData($csvData);

        if (!isset($csvData['file_url'])) {
            $newFilePath = $this->copyFileFromFtpToBaseIfNotCopied(
                $csvData['file_name']
            );
            $this->setFilePath($newFilePath);
            $this->setFileSize($this->getFileSize());
        }

        $this->_saveFileData();

        if ($storeId != 0 && $this->isObjectNew()) {
            $this->_saveFileStoreData(0);
        }
        $this->_saveFileStoreData($storeId);
        $this->_saveFileCustomersGroupData($storeId);
    }

    /**
     * @param $productIds
     * @param $storeId
     *
     * @return array
     */
    public function getFilesProductGrid($productIds, $storeId)
    {
        if ($this->hasData('files_product_grid')) {
            return $this->getData('files_product_grid');
        }
        /**
         *@var ResourceModel\File\Collection $collection
         */
        $collection = $this->getCollection();
        $collection->getFilesAdminByProductIds($productIds, $storeId);
        $result = $collection->toArray(['file_id', 'product_id', 'label', 'file_name']);
        $items = $result['items'];

        $attachmentFiles = [];
        foreach ($items as $item) {
            $fileDownloadUrl = $this->getDownloadUrlBackend($item['file_id']);
            $item['url'] = $fileDownloadUrl;
            $item['increment'] = array_key_exists(
                $item['product_id'], $attachmentFiles)
            ? count($attachmentFiles[$item['product_id']]) : 0;
            
            $attachmentFiles[$item['product_id']][] = $item;
        }

        $this->setData('files_product_grid', $attachmentFiles);
        return $attachmentFiles;
    }

    /**
     * @param null $fileId
     * @param null $productId
     *
     * @return string
     */
    public function getDownloadUrlFrontend($fileId = null, $productId = null)
    {
        $urlBuilder = $this->urlFrontendBuilder;
        $params = [
            'file_id' => $fileId ?: $this->getId(),
            'product_id' => $productId ?: $this->getProductId(),
        ];
        return $this->getDownloadUrl($urlBuilder, $params);
    }

    /**
     * @param null $fileId
     *
     * @return string
     */
    public function getDownloadUrlBackend($fileId = null)
    {
        $urlBuilder = $this->urlFactory->create();
        $params = [
            'id'    => $fileId ?: $this->getId(),
            '_secure'    => true,
        ];
        return $this->getDownloadUrl($urlBuilder, $params);
    }

    /**
     * @param \Magento\Framework\UrlInterface|\Magento\Framework\UrlInterface $urlBuilder
     * @param array $params
     *
     * @return mixed
     */
    protected function getDownloadUrl($urlBuilder, $params)
    {
        return $urlBuilder->getUrl('amfile/file/download', $params);
    }

    /**
     * @return $this
     */
    public function afterDeleteCommit()
    {
        $this->deleteFileIfUnused();
        return parent::afterDeleteCommit();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteFileIfUnused($filePath = null)
    {
        if(is_null($filePath)) {
            $filePath = $this->getFilePath();
        }

        $count = $this->getCollection()->addFieldToFilter('file_path', $filePath)->count();
        if ($filePath && $count <= 0) {
           $this->fileHelper->deleteFile($this->getBasePath(), $filePath);
        }
    }

    /**
     * @return string
     */
    public function getBaseTmpPath()
    {
        return $this->fileHelper->getPathToAttachTmpFolder();
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->fileHelper->getPathToAttachFolder();
    }

    /**
     * @return string
     */
    public function getBaseFtpPath()
    {
        return $this->fileHelper->getPathToFtpFolder();
    }

}
