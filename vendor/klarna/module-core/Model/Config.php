<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

/**
 * Configuration paths storage
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Klarna\Core\Model;

use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config
{
    const CONFIG_XML_PATH_KLARNA_DEBUG = 'klarna/api/debug';
    const CONFIG_XML_PATH_KLARNA_TEST_MODE = 'klarna/api/test_mode';
    const CONFIG_XML_PATH_GENERAL_STORE_INFORMATION_COUNTRY = 'general/store_information/country_id';
    const CONFIG_XML_PATH_GENERAL_STATE_OPTIONS = 'general/region/state_required';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Check what taxes should be applied after discount
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function storeAddressSet($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_GENERAL_STORE_INFORMATION_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function debugModeWhileLive($store = null)
    {
        if ($this->testMode($store)) {
            return false;
        }
        return $this->debugMode($store);
    }

    /**
     * Get defined tax calculation algorithm
     *
     * @param   null|string|bool|int|Store $store
     * @return  string
     */
    public function testMode($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_KLARNA_TEST_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function debugMode($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_KLARNA_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return a list of countries that incorrectly have the state/region marked as required
     *
     * @return array
     */
    public function requiredRegions()
    {
        $failed = [];
        $knownCountriesWithOptionalRegion = [
            'at',
            'de',
            'fi',
        ];
        $countries = $this->_scopeConfig->getValue(self::CONFIG_XML_PATH_GENERAL_STATE_OPTIONS);
        $countries = explode(',', $countries);
        foreach ($knownCountriesWithOptionalRegion as $country) {
            if (in_array($country, $countries)) {
                $failed[] = $country;
            }
        }
        return $failed;
    }
}
