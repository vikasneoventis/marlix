<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

/**
 * @method string getPath()
 * @method string getName()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FtpFile extends \Magento\Framework\DataObject
{

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @param \Amasty\ProductAttachment\Helper\File $fileHelper
     * @param array $data
     */
    public function __construct(
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        $data = []
    ) {
        parent::__construct($data);

        $this->fileHelper = $fileHelper;
    }

    /**
     *
     * @param string $fileName
     * @param string $filePath
     * @return $this
     */
    public function load($fileName, $filePath)
    {

        $this->addData(
            [
                'id' => $fileName,
                'path' => $filePath,
                'name' => $fileName,
            ]
        );
        $this->addData([
            'size' => $this->getSize(),
            'full_size' => $this->getFullSize(),
        ]);

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getSize()
    {

        if ($this->getData('size') !== null) {
            return $this->getData('size');
        }

        $fileSize = $this->fileHelper->getFileSize($this->getRelativePath(), $this->getName());
        $this->setData('size', $fileSize);
        return $this->getData('size');
    }

    public function getFullSize()
    {
        if ($this->getData('full_size') !== null) {
            return $this->getData('full_size');
        }

        $fileSize = $this->getSize();
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

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteFile()
    {
        $this->fileHelper->deleteFile($this->getRelativePath(), $this->getName());
        return $this;
    }

    public function getRelativePath()
    {
        return $this->fileHelper->getRelativePath($this->getPath());
    }

    protected function getFilePath()
    {
        return $this->fileHelper->getPath($this->getRelativePath(), $this->getName());
    }

    public function loadByFileName($fileName)
    {
        return $this->load(
            $fileName, $this->fileHelper->getAbsolutePathToFtpFolder()
        );
    }

    public function exists()
    {
        return $this->fileHelper->isFile($this->getRelativePath(), $this->getName());
    }
}