<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Api\Data;

interface ProductAttachmentInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const FILE_PATH = 'file_path';
    const FILE_NAME = 'file_name';
    const FILE_URL = 'file_url';
    const FILE_SIZE = 'file_size';
    const FILE_TYPE = 'file_type';

    const FILE_TYPE_FILE = 'file';
    const FILE_TYPE_URL = 'url';

    const CUSTOMER_GROUP = 'customer_group';
    const STORE_CONFIG = 'store_config';

    const CONTENT = 'content';
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @return string
     */
    //public function getFilePath();

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @return string
     */
    public function getFileUrl();

    /**
     * @return int
     */
    public function getFileSize();

    /**
     * enum("file", "url")
     * @return string
     */
    public function getFileType();

    /**
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentCustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentStoreConfigInterface[]
     */
    public function getStoreConfigs();

    /**
     * @return \Amasty\ProductAttachment\Api\Data\FileContentInterface
     */
    public function getContent();


    /**
     * @param $id
     *
     * @return ProductAttachmentInterface
     */
    public function setId($id);



    /**
     * @param $productId
     *
     * @return ProductAttachmentInterface
     */
    public function setProductId($productId);

    /**
     * @param $filePath
     *
     * @return ProductAttachmentInterface
     */
    //public function setFilePath($filePath);

    /**
     * @param $fileName
     *
     * @return ProductAttachmentInterface
     */
    public function setFileName($fileName);

    /**
     * @param $fileUrl
     *
     * @return ProductAttachmentInterface
     */
    public function setFileUrl($fileUrl);

    /**
     * @param $fileSize
     *
     * @return ProductAttachmentInterface
     */
    public function setFileSize($fileSize);

    /**
     * enum("file", "url")
     * @param string $fileType
     *
     * @return ProductAttachmentInterface
     */
    public function setFileType($fileType);

    /**
     * @param ProductAttachmentCustomerGroupInterface $customerGroups
     *
     * @return ProductAttachmentInterface
     */
    public function setCustomerGroups($customerGroups);

    /**
     * @param \Amasty\ProductAttachment\Api\Data\ProductAttachmentStoreConfigInterface[] $storeConfigs
     *
     * @return ProductAttachmentInterface
     */
    public function setStoreConfigs($storeConfigs);

    /**
     * @param \Amasty\ProductAttachment\Api\Data\FileContentInterface $content
     *
     * @return ProductAttachmentInterface
     */
    public function setContent($content);

}
