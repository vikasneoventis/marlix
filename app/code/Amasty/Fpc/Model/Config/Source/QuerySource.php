<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;

class QuerySource implements \Magento\Framework\Option\ArrayInterface
{
    const SOURCE_ALL_PAGES  = 0;
    const SOURCE_SITE_MAP   = 1;
    const SOURCE_TEXT_FILE  = 2;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    public function toOptionArray()
    {
        $options = [];

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        $options[] = [
            'label' => __('Pages Types'),
            'value' => self::SOURCE_ALL_PAGES
        ];

        if ($directoryRead->isReadable('sitemap.xml')) {
            $options[] = [
                'label' => __('Sitemap XML'),
                'value' => self::SOURCE_SITE_MAP
            ];
        }

        $options[] = [
            'label' => __('Text file with one link per line'),
            'value' => self::SOURCE_TEXT_FILE
        ];

        return $options;
    }
}
