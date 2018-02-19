<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Checkout\Orderline;

use Klarna\Core\Api\OrderLine;
use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Klarna total collector
 */
class Collector
{
    /**
     * Corresponding store object
     *
     * @var Store
     */
    protected $_store;

    /**
     * Sorted models
     *
     * @var array
     */
    protected $_collectors = [];

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var DataInterface
     */
    protected $klarnaConfig;

    /**
     * @var ObjectManager
     */
    protected $objManager;

    /**
     * Init corresponding models
     *
     * @param StoreManagerInterface $store
     * @param ConfigHelper          $configHelper
     * @param DataInterface         $klarnaConfig
     * @param ObjectManager         $objManager
     */
    public function __construct(
        StoreManagerInterface $store,
        ConfigHelper $configHelper,
        DataInterface $klarnaConfig,
        ObjectManager $objManager
    ) {
        $this->_store = $store->getStore();
        $this->klarnaConfig = $klarnaConfig;
        $this->configHelper = $configHelper;
        $this->objManager = $objManager;
        $this->_initCollectors($this->_store);
    }

    /**
     * Initialize models configuration and objects
     *
     * @return $this
     */
    protected function _initCollectors($store)
    {
        if (!$this->configHelper->getPaymentConfigFlag('active', $store, 'klarna_kco') && !$this->configHelper->getPaymentConfigFlag('active', $store, 'klarna_kp')) {
            return $this; // No Klarna methods enabled
        }
        $checkoutType = $this->configHelper->getCheckoutType($store);
        $totalsConfig = $this->configHelper->getOrderlines($checkoutType);

        if (!$totalsConfig) {
            return $this;
        }

        foreach ($totalsConfig as $totalCode => $totalConfig) {
            $class = $totalConfig['class'];
            if (!empty($class)) {
                $this->_collectors[$totalCode] = $this->_initModelInstance($class, $totalCode);
            }
        }

        return $this;
    }

    /**
     * Init model class by configuration
     *
     * @param string $class
     * @param string $totalCode
     * @return $this
     * @throws \RuntimeException
     * @throws KlarnaException
     */
    protected function _initModelInstance($class, $totalCode)
    {
        $model = $this->objManager->get($class);
        if (!$model instanceof OrderLine) {
            throw new KlarnaException(
                __('The order item model should be extended from %1.', AbstractLine::class)
            );
        }

        $model->setCode($totalCode);

        return $model;
    }

    /**
     * Get models for calculation logic
     *
     * @return array
     */
    public function getCollectors($store = null)
    {
        if ($store !== null && $this->_store !== $store) {
            $this->_initCollectors($store);
        }
        return $this->_collectors;
    }
}
