<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Helper;

use Klarna\Kco\Model\Payment\Kco;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

class ConfigHelper extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Klarna\Core\Helper\ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * ConfigHelper constructor.
     *
     * @param Context                          $context
     * @param \Klarna\Core\Helper\ConfigHelper $configHelper
     * @param Session                          $session
     * @param Resolver                         $resolver
     */
    public function __construct(
        Context $context,
        \Klarna\Core\Helper\ConfigHelper $configHelper,
        Session $session,
        Resolver $resolver
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->configHelper = $configHelper;
        $this->resolver = $resolver;
    }

    /**
     * Determine if current store supports the use of pack station
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return bool
     */
    public function getPackstationSupport($store = null)
    {
        return (bool)$this->configHelper->getVersionConfig($store)->getPackstationSupport();
    }

    /**
     * Determine if the pre-fill notice is enabled
     *
     * @var \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return bool
     */
    public function isPrefillNoticeEnabled($store = null)
    {
        if (!$this->session->isLoggedIn()) {
            return false;
        }
        if (!$this->configHelper->getCheckoutConfigFlag('merchant_prefill', $store, Kco::METHOD_CODE)) {
            return false;
        }
        if (!$this->configHelper->getCheckoutConfigFlag('prefill_notice', $store, Kco::METHOD_CODE)) {
            return false;
        }
        return true;
    }

    /**
     * Determine if pack station config setting has been enabled.
     *
     * @param $store
     * @return bool
     */
    public function getPackstationEnabled($store)
    {
        return $this->configHelper->getCheckoutConfigFlag('packstation_enabled', $store, Kco::METHOD_CODE);
    }

    /**
     * Get payment config value
     *
     * @param string $config
     * @param Store  $store
     *
     * @return mixed
     */
    public function getPaymentConfig($config, $store = null)
    {
        return $this->configHelper->getPaymentConfig($config, $store, Kco::METHOD_CODE);
    }

    /**
     * Get Klarna terms url
     *
     * @param StoreInterface $store
     * @return string
     */
    public function getUserTermsUrl($store = null)
    {
        $merchantId = $this->getApiConfig('merchant_id', $store);
        $locale = strtolower($this->resolver->getLocale());

        return sprintf('https://cdn.klarna.com/1.0/shared/content/legal/terms/%s/%s/checkout', $merchantId, $locale);
    }

    /**
     * Get API config value
     *
     * @param string $config
     * @param Store  $store
     *
     * @return mixed
     */
    public function getApiConfig($config, $store = null)
    {
        return $this->configHelper->getApiConfig($config, $store);
    }
}
