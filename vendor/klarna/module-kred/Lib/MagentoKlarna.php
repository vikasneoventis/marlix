<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Lib;

use Klarna\Core\Helper\VersionInfo;

class MagentoKlarna extends \Klarna\XMLRPC\Klarna
{

    /**
     * MagentoKlarna constructor.
     *
     * @param VersionInfo $versionInfo
     * @param array       $data
     */
    public function __construct(VersionInfo $versionInfo, $data = [])
    {
        parent::__construct();
        $this->version .= ':Magento:' . $versionInfo->getMageEdition() . ':' . $versionInfo->getMageVersion();
        $this->version .= ':Mode:' . $versionInfo->getMageMode();

        $this->version .= ':KcoModule:' . $versionInfo->getVersion('klarna/module-kco');
        $this->version .= ':KredModule:' . $versionInfo->getVersion('klarna/module-kred');
    }
}
