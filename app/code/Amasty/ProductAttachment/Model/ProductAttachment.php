<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Model;


use Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface;
use Amasty\ProductAttachment\Api\Data\ProductAttachmentCustomerGroupInterface;
use Amasty\ProductAttachment\Api\Data\ProductAttachmentStoreConfigInterface;

class ProductAttachment extends File implements ProductAttachmentInterface
{
    /**
     * @var ProductAttachmentCustomerGroupFactory
     */
    protected $productAttachmentCustomerGroupFactory;
    /**
     * @var ProductAttachmentStoreConfigFactory
     */
    protected $productAttachmentStoreConfigFactory;

    /**
     * @var FileContentFactory
     */
    protected $productAttachmentFileContentFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\ProductAttachment\Helper\File $fileHelper, Icon $iconModel,
        \Magento\Backend\Model\UrlFactory $urlFactory,
        \Magento\Framework\UrlInterface $urlFrontendBuilder,
        \Amasty\ProductAttachment\Model\ProductAttachmentCustomerGroupFactory $productAttachmentCustomerGroupFactory,
        \Amasty\ProductAttachment\Model\ProductAttachmentStoreConfigFactory $productAttachmentStoreConfigFactory,
        \Amasty\ProductAttachment\Model\FileContentFactory $productAttachmentFileContentFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productAttachmentCustomerGroupFactory = $productAttachmentCustomerGroupFactory;
        $this->productAttachmentStoreConfigFactory = $productAttachmentStoreConfigFactory;
        $this->productAttachmentFileContentFactory = $productAttachmentFileContentFactory;
        parent::__construct(
            $context, $registry, $fileHelper, $iconModel, $urlFactory,
            $urlFrontendBuilder, $resource, $resourceCollection, $data
        );
    }


    public function getCustomerGroups()
    {
        $data = parent::getData(self::CUSTOMER_GROUP);
        if(is_null($data)) {
            $this->loadCustomerGroups();
        }
        return parent::getData(self::CUSTOMER_GROUP);
    }

    public function setCustomerGroups($customerGroups)
    {
        return $this->setData(self::CUSTOMER_GROUP, $customerGroups);
    }

    public function getStoreConfigs()
    {
        $data = parent::getData(self::STORE_CONFIG);
        if(is_null($data)) {
            $this->loadStoreConfigs();
        }
        return parent::getData(self::STORE_CONFIG);
    }

    public function setStoreConfigs($storeConfigs)
    {
        return $this->setData(self::STORE_CONFIG, $storeConfigs);
    }


    /**
     * @return int|null
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return parent::getData(self::FILE_PATH);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return parent::getData(self::FILE_NAME);
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        return parent::getData(self::FILE_URL);
    }

    /**
     * @return string
     */
    public function getFileType()
    {
        return parent::getData(self::FILE_TYPE);
    }

    public function getContent()
    {
        $content = parent::getData(self::CONTENT);
        if(is_null($content)) {
            $this->loadFileContent();
        }
        return parent::getData(self::CONTENT);
    }

    public function setProductId($productId)
    {
        return parent::setData(self::PRODUCT_ID, $productId);
    }

    /*public function setFilePath($filePath)
    {
        return parent::setData(self::FILE_PATH, $filePath);
    }*/

    public function setFileName($fileName)
    {
        return parent::setData(self::FILE_NAME, $fileName);
    }

    public function setFileUrl($fileUrl)
    {
        return parent::setData(self::FILE_URL, $fileUrl);
    }

    public function setFileSize($fileSize)
    {
        return parent::setData(self::FILE_SIZE, $fileSize);
    }

    public function setFileType($fileType)
    {
        if(!in_array($fileType, [self::FILE_TYPE_FILE, self::FILE_TYPE_URL])){
            $fileType = self::FILE_TYPE_FILE;
        }
        return parent::setData(self::FILE_TYPE, $fileType);
    }

    public function setContent($content)
    {
        return parent::setData(self::CONTENT, $content);
    }


    protected function loadCustomerGroups()
    {
        $db = $this->getResource()->getConnection();

        $table = $this->getResource()->getTable('amasty_file_customer_group');
        $select = $db->select()->from($table, ['store_id', 'customer_group_id'])->where('file_id', $this->getId())->order('id ASC');
        $data = $db->fetchAssoc($select);
        $listCustomerGroups = [];
        foreach($data as $customerGroupData) {
            $productAttachmentCustomerGroup = $this->productAttachmentCustomerGroupFactory->create();
            $listCustomerGroups[] = $productAttachmentCustomerGroup->setData($customerGroupData);
        }

        $this->setData(self::CUSTOMER_GROUP, $listCustomerGroups);
    }

    protected function saveCustomerGroups()
    {
        /** @var ProductAttachmentCustomerGroupInterface[] $data */
        $data = $this->getData(self::CUSTOMER_GROUP);

        $db = $this->getResource()->getConnection();
        $db->delete(
            $this->getResource()->getTable('amasty_file_customer_group'),
            sprintf('file_id = %d', $this->getId())
        );

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $customerGroup) {
                $fileCustomerGroupData = [
                    'store_id' => $customerGroup->getStoreId(),
                    'customer_group_id' => $customerGroup->getCustomerGroupId(),
                    'file_id' => $this->getId()
                ];
                $db->insertOnDuplicate(
                    $this->getResource()->getTable('amasty_file_customer_group'),
                    $fileCustomerGroupData
                );
            }
        }
    }

    protected function loadStoreConfigs()
    {
        $db = $this->getResource()->getConnection();

        $table = $this->getResource()->getTable('amasty_file_store');
        $select = $db->select()->from($table, ['store_id', 'label', 'is_visible', 'position', 'show_for_ordered', 'customer_group_is_default'])->where('file_id', $this->getId())->order('id ASC');
        $data = $db->fetchAssoc($select);
        $listStoreConfigs = [];
        foreach($data as $storeConfigData) {
            $productAttachmentStoreConfig = $this->productAttachmentStoreConfigFactory->create();
            $listStoreConfigs[] = $productAttachmentStoreConfig->setData($storeConfigData);
        }

        $this->setData(self::STORE_CONFIG, $listStoreConfigs);
    }

    protected function saveStoreConfigs()
    {
        /** @var ProductAttachmentStoreConfigInterface[] $data */
        $data = $this->getData(self::STORE_CONFIG);

        $db = $this->getResource()->getConnection();
        $db->delete(
            $this->getResource()->getTable('amasty_file_store'),
            sprintf('file_id = %d', $this->getId())
        );

        if (is_array($data) && count($data) > 0) {
            $isNeedToSaveStoreConfig0 = $this->isObjectNew();
            foreach ($data as $storeConfig) {
                $storeConfigData = $storeConfig->getData();
                $storeConfigData['file_id'] = $this->getId();
                if($storeConfig->getStoreId() == 0) {
                    $isNeedToSaveStoreConfig0 = false;
                }
                $db->insertOnDuplicate(
                    $this->getResource()->getTable('amasty_file_store'),
                    $storeConfigData
                );
            }

            if($isNeedToSaveStoreConfig0) {
                $storeConfigData = [
                    'file_id' => $this->getId(),
                    'store_id' => 0,
                    'position' => 0,
                ];
                $db->insertOnDuplicate(
                    $this->getResource()->getTable('amasty_file_store'),
                    $storeConfigData
                );
                $storeConfigs = $this->getStoreConfigs();
                $storeConfigs[] = $productAttachmentStoreConfig = $this->productAttachmentStoreConfigFactory->create()->setData($storeConfigData);
                $this->setData(self::STORE_CONFIG, $storeConfigs);
            }
        }
    }

    protected function loadFileContent()
    {
        $fileContent = $this->productAttachmentFileContentFactory->create();
        $fileContent->loadFile($this->getFilePath());
        $this->setData(self::CONTENT, $fileContent);
    }

    protected function saveFileContent()
    {
        if($this->getFileType() == self::FILE_TYPE_FILE) {
            $this->getContent()->saveFile();
            $this->setData(self::FILE_PATH,'./'.$this->getContent()->getName());
            $this->setData(self::FILE_SIZE, $this->getFileSize());
        }
    }

    public function afterLoad()
    {
        $this->loadCustomerGroups();
        $this->loadStoreConfigs();
        $this->loadFileContent();
        return parent::afterLoad();
    }

    public function beforeSave()
    {
        parent::beforeSave();

        $origFileName = pathinfo($this->getOrigData(self::FILE_PATH), PATHINFO_BASENAME);
        if($origFileName != $this->getContent()->getName()) {
            $this->saveFileContent();
        }

        return $this;
    }

    public function afterSave()
    {
        if($this->getOrigData(self::CUSTOMER_GROUP) != $this->getCustomerGroups()) {
            $this->saveCustomerGroups();
        }
        if($this->getOrigData(self::STORE_CONFIG) != $this->getStoreConfigs()) {
            $this->saveStoreConfigs();
        }
        if($this->getOrigData(self::FILE_PATH) != $this->getFilePath()) {
            $this->deleteFileIfUnused($this->getOrigData(self::FILE_PATH));
        }
        return parent::afterSave();
    }
}
