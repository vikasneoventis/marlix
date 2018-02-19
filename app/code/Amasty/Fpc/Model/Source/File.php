<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source;

use Amasty\Fpc\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class File implements SourceInterface
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Config $config,
        Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    public function getPages()
    {
        $result = [];

        $queueLimit = $this->config->getValue('crawler/max_queue_size');

        $filePath = $this->config->getValue('crawler/file_path');
        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        if (!$directoryRead->isExist($filePath)) {
            throw new \Exception(
                'File is selected as source, but file do not exists with specified path: ' . $filePath
            );
        }

        $fileContent = $directoryRead->readFile($filePath);
        $urls = preg_split('/[,\s]+/', $fileContent, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($urls as $i => $url) {
            $result [] = [
                'rate' => 1,
                'url'  => $url,
            ];

            if ($i > $queueLimit) {
                break;
            }
        }

        return $result;
    }
}
