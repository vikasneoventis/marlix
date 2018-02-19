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

use Magento\Framework\Api\AbstractSimpleObject;
use Amasty\ProductAttachment\Api\Data\FileContentInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileContent extends AbstractSimpleObject implements FileContentInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Amasty\ProductAttachment\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Downloadable\Helper\Download
     */
    protected $downloadHelper;


    public function __construct(
        Filesystem $fileSystem,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        \Amasty\ProductAttachment\Helper\Config $configHelper,
        \Magento\Downloadable\Helper\Download $downloadHelper,
        array $data = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->fileHelper = $fileHelper;
        $this->configHelper = $configHelper;
        $this->downloadHelper = $downloadHelper;
        parent::__construct($data);
    }


    public function loadFile($fileName)
    {
        $mediaReader = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToFile = $this->fileHelper->getFilePath($this->fileHelper->getPathToAttachFolder(), $fileName);
        $absoluteFilePath = $mediaReader->getAbsolutePath($pathToFile);
        if(!\is_file($absoluteFilePath) || !is_readable($absoluteFilePath)) {
            return;
        }
        $this->setBase64EncodedData(base64_encode(\file_get_contents($absoluteFilePath)));
        $contentType = 'application/octet-stream';
        $this->downloadHelper->setResource($pathToFile);
        if($this->configHelper->getDetectMime()) {
            $contentType = $this->downloadHelper->getContentType();
        }
        $this->setType($contentType);
        $this->setName($this->downloadHelper->getFilename());
    }

    public function saveFile()
    {
        // safe for XSS
        $fileName = pathinfo($this->getName(), PATHINFO_BASENAME);
        $mediaWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $filePath = $this->fileHelper->getFilePath($this->fileHelper->getPathToAttachFolder(), $fileName);
        $newFileName = \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $mediaWriter->getAbsolutePath($filePath)
        );
        $this->setName($newFileName);
        $filePath = $this->fileHelper->getFilePath($this->fileHelper->getPathToAttachFolder(), $newFileName);
        $mediaWriter->writeFile($filePath, $this->getBase64DecodedData());
    }

    public function getBase64EncodedData()
    {
        return $this->_get(self::BASE64_ENCODED_DATA);
    }

    protected function getBase64DecodedData()
    {
        return base64_decode($this->getBase64EncodedData());
    }

    public function setBase64EncodedData($base64EncodedData)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $base64EncodedData);
    }

    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getName()
    {
        return $this->_get(self::NAME);
    }

    public function setName($name)
    {
        // safe for XSS
        $name = pathinfo($name, PATHINFO_BASENAME);
        return $this->setData(self::NAME, $name);
    }
}
