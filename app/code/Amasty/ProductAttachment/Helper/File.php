<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Helper;

use Magento\Framework\UrlInterface;

class File extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Amasty\ProductAttachment\Helper\Config
     */
    protected $configHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        Config $configHelper
    ) {
        parent::__construct($context);

        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );
        $this->configHelper = $configHelper;
    }

    /**
     * Upload file from temporary folder.
     * @param string $tmpPath
     * @param \Magento\MediaStorage\Model\File\Uploader $uploader
     * @return array
     */
    public function uploadFromTmp($tmpPath, \Magento\MediaStorage\Model\File\Uploader $uploader)
    {
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $absoluteTmpPath = $this->getAbsolutePath($tmpPath);
        $result = $uploader->save($absoluteTmpPath);

        return $result;
    }

    /**
     *
     * @param string $fileReference file path
     * @param string $fromPath
     * @param string $toPath
     *
     * @return string
     */
    public function moveFileFromTo($fileReference, $fromPath, $toPath)
    {
        if (strrpos($fileReference, '.tmp') == strlen($fileReference) - 4) {
            $fileReference = substr($fileReference, 0, strlen($fileReference) - 4);
        }

        $destFile = $this->getDestFileName($fileReference);

        $this->mediaDirectory->renameFile(
            $this->getFilePath($fromPath, $fileReference),
            $this->getFilePath($toPath, $destFile)
        );

        return str_replace('\\', '/', $destFile);
    }

    /**
     * @param string $fileName
     * @param string $fromPath
     * @param string $toPath
     *
     * @return string
     */
    public function copyFileFromTo($fileName, $fromPath, $toPath)
    {
        if (strrpos($fileName, '.tmp') == strlen($fileName) - 4) {
            $fileName = substr($fileName, 0, strlen($fileName) - 4);
        }

        $destFile = $this->getDestFileName($fileName);

        $this->mediaDirectory->copyFile(
            $this->getFilePath($fromPath, $fileName),
            $this->getFilePath($toPath, $destFile)
        );

        return str_replace('\\', '/', $destFile);
    }

    protected function getAbsolutePath($relativePath = null)
    {
        return $this->mediaDirectory->getAbsolutePath($relativePath);
    }

    protected function getDestFileName($file)
    {
        $destFilePath = \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->getAbsolutePath($file)
        );
        $zendFilterDir = new \Zend_Filter_Dir();
        return $zendFilterDir->filter($file) . DIRECTORY_SEPARATOR . $destFilePath;
    }

    public function getFileExtension($basePath, $filePath)
    {
        $fileRelativePath = $this->getFilePath($basePath, $filePath);
        $extension = $this->mediaDirectory->isExist($fileRelativePath)
            ? pathinfo($filePath, PATHINFO_EXTENSION) : '';

        return $extension;
    }

    public function getFileSize($basePath, $filePath)
    {
        $fileRelativePath = $this->getFilePath($basePath, $filePath);
        $fileSize = $this->mediaDirectory->isExist($fileRelativePath)
            ? $this->mediaDirectory->stat($fileRelativePath)['size'] : 0;

        return $fileSize;
    }

    public function getBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl();
    }

    public function getIconRelativePathByName($iconFileName)
    {
        return $this->getFilePath($this->getPathToIconFolder(), $iconFileName);
    }

    public function getIconUrl($iconRelativePath)
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $baseUrl = str_replace('index.php', '', $baseUrl);

        $iconUrl = $iconRelativePath != ''
            ? $this->getFilePath($baseUrl, $iconRelativePath) : '';

        return $iconUrl;
    }

    public function getFileUrl($fileRelativePath)
    {
        $baseUrl = $this->getBaseUrl();

        $iconUrl = $fileRelativePath != ''
            ? $this->getFilePath($baseUrl, $fileRelativePath) : '';

        return $iconUrl;

    }

    public function getIconUrlByName($iconName)
    {
        $iconRelativePath = $this->getIconRelativePathByName($iconName);
        return $this->getIconUrl($iconRelativePath);
    }

    public function deleteFile($basePath, $fileName)
    {
        if (!$this->isFile($basePath, $fileName)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The file does not exist.'));
        }
        $filePath = $this->getFilePath($basePath, $fileName);
        $this->mediaDirectory->delete($filePath);
    }

    public function isFile($basePath, $fileName)
    {
        $filePath = $this->getFilePath($basePath, $fileName);
        return $this->mediaDirectory->isFile($filePath);
    }

    public function getRelativePath($absolutePath)
    {
        return $this->mediaDirectory->getRelativePath($absolutePath);
    }

    /**
     * Return full path to file
     *
     * @param string $path
     * @param string $file
     * @return string
     */
    public function getFilePath($path, $file)
    {
        $path = rtrim($path, './');
        $file = ltrim($file, './');

        return $path . '/' . $file;
    }

    public function getPathToCsvFolder()
    {
        return 'amasty/amfile/import/csv';
    }

    public function getPathToFtpFolder()
    {
        return $this->configHelper->getPathToFtpFolder();
    }

    public function getPathToAttachTmpFolder()
    {
        return 'amasty/amfile/tmp/attach';
    }

    public function getPathToAttachFolder()
    {
        return 'amasty/amfile/attach';
    }

    public function getPathToIconFolder()
    {
        return 'amasty/amfile/icon';
    }

    public function getPathToCsvExample()
    {
        $csvFolder = $this->getPathToCsvFolder();

        return $this->getFilePath($csvFolder, 'example.csv');
    }

    public function getAbsolutePathToCsvFolder()
    {
        return $this->mediaDirectory->getAbsolutePath($this->getPathToCsvFolder());
    }

    public function getAbsolutePathToFtpFolder()
    {
        return $this->mediaDirectory->getAbsolutePath($this->getPathToFtpFolder());
    }

    public function getAbsolutePathToAttachTmpFolder()
    {
        return $this->mediaDirectory->getAbsolutePath($this->getPathToAttachTmpFolder());
    }

    public function getAbsolutePathToAttachFolder()
    {
        return $this->mediaDirectory->getAbsolutePath($this->getPathToAttachFolder());
    }

}
