<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Helper;

use \Magento\Framework\Exception\LocalizedException;

class Uploader extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $storageDatabase;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase

    ) {
        parent::__construct($context);

        $this->fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->storageDatabase = $storageDatabase;
    }

    /**
     * @param string $fileId
     *
     * @return array
     */
    public function uploadFileToAttachFolder($fileId)
    {
        $uploader = $this->getUploader($fileId);

        $destinationPath = $this->fileHelper->getPathToAttachFolder();

        $result = $this->uploadFile($uploader, $destinationPath);

        return $result;
    }

    /**
     * @param string $fileId
     *
     * @return array
     */
    public function uploadFileToAttachTmpFolder($fileId)
    {
        $uploader = $this->getUploader($fileId);

        $destinationPath = $this->fileHelper->getPathToAttachTmpFolder();

        $result = $this->uploadFile($uploader, $destinationPath);

        return $result;
    }

    /**
     * @param string $fileId
     *
     * @return array
     */
    public function uploadFileToCsvFolder($fileId)
    {
        $uploader = $this->getUploader($fileId);
        $uploader->setAllowedExtensions(['csv']);
        $destinationPath = $this->fileHelper->getPathToCsvFolder();

        $result = $this->uploadFile($uploader, $destinationPath);

        return $result;
    }

    /**
     * @param string $fileId
     *
     * @return array
     */
    public function uploadFileToFtpFolder($fileId)
    {
        $uploader = $this->getUploader($fileId);

        $destinationPath = $this->fileHelper->getPathToFtpFolder();

        $result = $this->uploadFile($uploader, $destinationPath);

        return $result;
    }

    /**
     * @param string $fileId
     *
     * @return array
     */
    public function uploadFileToIconFolder($fileId)
    {
        $uploader = $this->getUploader($fileId);

        $destinationPath = $this->fileHelper->getPathToIconFolder();
        $uploader->validateFile();
        $result = $this->uploadFile($uploader, $destinationPath);

        return $result;
    }

    /**
     * @param \Magento\MediaStorage\Model\File\Uploader $uploader
     * @param string $destinationPath
     *
     * @return array
     * @throws LocalizedException
     */
    public function uploadFile($uploader, $destinationPath)
    {

        $result = $this->fileHelper->uploadFromTmp($destinationPath, $uploader);

        if (!$result) {
            throw new LocalizedException(__('File can not be moved from temporary folder to the destination folder.'));
        }

        $result['tmp_name'] = $this->replacePathForWindows($result['tmp_name']);
        $result['path'] = $this->replacePathForWindows($result['path']);

        if (isset($result['file'])) {
            $relativePath = $this->fileHelper->getFilePath($destinationPath, $result['file']);
            $this->storageDatabase->saveFile($relativePath);
        }

        return $result;
    }

    public function validateFile($fileId)
    {
        $uploader = $this->getUploader($fileId);
        return $uploader->validateFile();
    }

    /**
     * @param string $fileId
     * @return \Magento\MediaStorage\Model\File\Uploader
     */
    protected function getUploader($fileId)
    {
        return $this->uploaderFactory->create(['fileId' => $fileId]);
    }

    protected function replacePathForWindows($path)
    {
        return  str_replace('\\', '/', $path);
    }

}

