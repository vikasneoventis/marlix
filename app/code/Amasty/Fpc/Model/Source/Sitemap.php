<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Sitemap implements SourceInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    public function getPages()
    {
        $result = [];

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        if (!$directoryRead->isExist('sitemap.xml')) {
            throw new \Exception(
                'Sitemap is selected as source, but sitemap.xml file do not exists in the root directory'
            );
        }

        $xml = simplexml_load_file($directoryRead->getAbsolutePath('sitemap.xml'));

        foreach ($xml->url as $url) {
            $result [] = [
                //convert float 0.5 into percent value 50%
                'rate' => isset($url->priority) ? round(trim($url->priority) * 100) : 0,
                'url'  => trim($url->loc),
            ];
        }

        return $result;
    }
}
