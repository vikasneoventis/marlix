<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source;

use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\Config\Source\QuerySource;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * @return SourceInterface
     */
    public function create()
    {
        $type = $this->config->getValue('crawler/source');

        switch ($type) {
            case QuerySource::SOURCE_TEXT_FILE:
                $className = 'File';
                break;
            case QuerySource::SOURCE_SITE_MAP:
                $className = 'Sitemap';
                break;
            default:
                $className = 'All';
        }

        return $this->objectManager->create('\Amasty\Fpc\Model\Source\\' . $className);
    }
}
