<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\FtpFile;

use Magento\Framework\App\Filesystem\DirectoryList;

class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * Backup model
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Amasty\ProductAttachment\Helper\File $fileHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {

        $this->fileHelper = $fileHelper;
        parent::__construct($entityFactory);

        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $this->hideFilesForApache();

        $this->varDirectory->create($this->getPathToFtpFolder());
        $path = rtrim($this->varDirectory->getAbsolutePath($this->getPathToFtpFolder()), '/') . '/';

        $this->setOrder('time', self::SORT_ORDER_DESC)
             ->addTargetDir($path)
             ->setCollectRecursively(false);
    }

    /**
     * Create .htaccess file and deny backups directory access from web
     *
     * @return void
     */
    protected function hideFilesForApache()
    {
        $filename = '.htaccess';
        if (!$this->varDirectory->isFile($filename)) {
            $this->varDirectory->writeFile($filename, 'deny from all');
            $this->varDirectory->changePermissions($filename, 0640);
        }
    }

    /**
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        $ftpFile = $this->objectManager->create('Amasty\ProductAttachment\Model\FtpFile');
        foreach ($ftpFile->load(
            $row['basename'],
            $this->varDirectory->getAbsolutePath($this->getPathToFtpFolder())
        )->getData() as $key => $value) {
            $row[$key] = $value;
        }
            return $row;
    }

    public function getPathToFtpFolder()
    {
        return $this->fileHelper->getPathToFtpFolder();
    }

    protected function getFileName($fileName)
    {
        $partOfPath = explode(DIRECTORY_SEPARATOR, $fileName);
        return array_pop($partOfPath);
    }

    public function getAllIds()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $this->_getItemId($item);
        }
        return $ids;
    }

}
