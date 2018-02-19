<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Api\Rest\Service;

use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Kco\Api\KasperInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Checkout implements KasperInterface
{
    const API_VERSION = 'v3';

    /**
     * @var ServiceInterface
     */
    protected $service;

    /**
     * @var string
     */
    protected $uri;

    /**
     * Initialize class
     *
     * @param ServiceInterface      $service
     * @param ConfigHelper          $configHelper
     * @param ResourceInterface     $moduleResource
     * @param StoreManagerInterface $store
     * @param VersionInfo           $versionInfo
     */
    public function __construct(
        ServiceInterface $service,
        ConfigHelper $configHelper,
        ResourceInterface $moduleResource,
        StoreManagerInterface $store,
        VersionInfo $versionInfo
    ) {
        $this->service = $service;
        $versionConfig = $configHelper->getVersionConfig($store->getStore());
        $this->service->connect(
            $configHelper->getApiConfig('merchant_id', $store->getStore()),
            $configHelper->getApiConfig('shared_secret', $store->getStore())
        );
        $this->uri = $versionConfig['production_url'];
        if ($configHelper->getApiConfigFlag('test_mode', $store->getStore())) {
            $this->uri = $versionConfig['testdrive_url'];
        }
        $version = $versionInfo->getVersion('klarna/module-kco');
        $mageMode = $versionInfo->getMageMode();
        $mageVersion = $versionInfo->getMageEdition() . ' ' . $versionInfo->getMageVersion();
        $this->service->setUserAgent('Magento2_KCO', $version, $mageVersion, $mageMode);
        $this->service->setHeader('Accept', '*/*');
    }

    /**
     * Get Klarna order details
     *
     * @param $id
     *
     * @return array
     */
    public function getOrder($id)
    {
        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders/{$id}";
        return $this->service->makeRequest($url, '', ServiceInterface::GET);
    }

    /**
     * Create new order
     *
     * @param array $data
     *
     * @return array
     */
    public function createOrder($data)
    {
        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders";
        return $this->service->makeRequest($url, json_encode($data));
    }

    /**
     * Update Klarna order
     *
     * @param string $id
     * @param array  $data
     * @return array
     * @throws KlarnaApiException
     */
    public function updateOrder($id = null, $data)
    {
        if ($id === null) {
            throw new KlarnaApiException('Klarna order id required for update');
        }

        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders/{$id}";
        return $this->service->makeRequest($url, json_encode($data));
    }
}
