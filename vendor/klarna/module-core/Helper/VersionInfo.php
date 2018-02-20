<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Helper;

use Magento\Composer\InfoCommand;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Module\ResourceInterface;

class VersionInfo
{

    public static $PACKAGE_MAP = [
        'klarna/module-core'       => 'Klarna_Core',
        'klarna/module-kco'        => 'Klarna_Kco',
        'klarna/module-kred'       => 'Klarna_Kred',
        'klarna/module-dach'       => 'Klarna_Dach',
        'klarna/module-kp'         => 'Klarna_Kp',
        'klarna/module-om'         => 'Klarna_Ordermanagement',
        'klarna/module-enterprise' => 'Klarna_Enterprise',
    ];
    /**
     * @var string
     */
    protected $mageMode;
    /**
     * @var string
     */
    protected $mageVersion;
    /**
     * @var ComposerInformation
     */
    protected $composerInformation;
    /**
     * @var InfoCommand
     */
    protected $infoCommand;
    /**
     * @var string
     */
    protected $mageEdition;
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * VersionInfo constructor.
     *
     * @param ProductMetadataInterface          $productMetadata
     * @param State                             $appState
     * @param ComposerInformation               $composerInformation
     * @param MagentoComposerApplicationFactory $magentoComposerApplicationFactory
     * @param ResourceInterface                 $resource
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        State $appState,
        ComposerInformation $composerInformation,
        MagentoComposerApplicationFactory $magentoComposerApplicationFactory,
        ResourceInterface $resource
    ) {
        $this->composerInformation = $composerInformation;
        $this->infoCommand = $magentoComposerApplicationFactory->createInfoCommand();
        $this->mageMode = $appState->getMode();
        $this->mageEdition = $productMetadata->getEdition();
        $this->mageVersion = $productMetadata->getVersion();
        $this->resource = $resource;
    }

    /**
     * Get composer version for given package
     *
     * @param string $packageName
     * @return array|bool
     */
    public function getVersion($packageName)
    {
        $moduleName = self::$PACKAGE_MAP[$packageName];
        return $this->resource->getDataVersion($moduleName);
    }

    /**
     * Get composer package info for given package name
     *
     * @param string $packageName
     * @return array|bool
     * @deprecated
     */
    public function getPackage($packageName)
    {
        return $this->infoCommand->run($packageName);
    }

    /**
     * Gets the current MAGE_MODE setting
     *
     * @return string
     */
    public function getMageMode()
    {
        return $this->mageMode;
    }

    /**
     * Gets the current Magento version
     *
     * @return string
     */
    public function getMageVersion()
    {
        return $this->mageVersion;
    }

    /**
     * Gets the current Magento Edition
     *
     * @return string
     */
    public function getMageEdition()
    {
        return $this->mageEdition;
    }
}
