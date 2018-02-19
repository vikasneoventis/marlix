<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Helper;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Api\ApiInterface;
use Klarna\Kco\Model\Api\Factory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\Config\Processor\Placeholder;
use Magento\Store\Model\Store;

/**
 * Klarna KCO helper
 */
class ApiHelper extends ConfigHelper
{
    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    /**
     * ApiHelper constructor.
     *
     * @param Context       $context
     * @param DataInterface $config
     * @param Resolver      $resolver
     * @param Placeholder   $placeholder
     * @param Factory       $apiFactory
     * @param string        $code
     * @param string        $eventPrefix
     */
    public function __construct(
        Context $context,
        DataInterface $config,
        Resolver $resolver,
        Placeholder $placeholder,
        Factory $apiFactory,
        $code = 'klarna_kco',
        $eventPrefix = 'kco'
    ) {
        parent::__construct($context, $config, $resolver, $placeholder, $code, $eventPrefix);
        $this->apiFactory = $apiFactory;
    }

    /**
     * Get Api instance
     *
     * @param Store $store
     *
     * @return ApiInterface
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    public function getApiInstance(Store $store = null)
    {
        $versionConfig = $this->getVersionConfig($store);

        /** @var ApiInterface $instance */
        $instance = $this->_getApiTypeInstance($versionConfig->getType());

        $instance->setStore($store);
        $instance->setConfig($versionConfig);

        return $instance;
    }

    /**
     * Load api type instance
     *
     * @param string $code
     *
     * @return ApiInterface
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    protected function _getApiTypeInstance($code)
    {
        $typeConfig = $this->_getApiTypeConfig($code);
        return $this->apiFactory->create($typeConfig->getClass());
    }
}
