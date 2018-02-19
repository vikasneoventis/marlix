<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\PageCache\Model\Config as VarnishConfig;
use Magento\Framework\App\Request\Http as HttpRequest;

class Config extends DataObject
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var HttpRequest
     */
    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HttpRequest $request,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($data);
        $this->request = $request;
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag('amasty_fpc/general/enabled');
    }

    public function getValue($path)
    {
        return $this->scopeConfig->getValue('amasty_fpc/' . $path);
    }

    public function isSetFlag($path)
    {
        return $this->scopeConfig->isSetFlag('amasty_fpc/' . $path);
    }

    public function getStores()
    {
        return $this->getCombinations('switch_stores', 'stores');
    }

    public function getCurrencies()
    {
        return $this->getCombinations('switch_currencies', 'currencies');
    }

    public function getCustomerGroups()
    {
        return $this->getCombinations('switch_customer_groups', 'customer_groups');
    }

    public function isVarnishEnabled()
    {
        return $this->scopeConfig->getValue(VarnishConfig::XML_PAGECACHE_TYPE) == VarnishConfig::VARNISH;
    }

    /**
     * @return array
     */
    public function getDebugIps()
    {
        $ips = $this->scopeConfig->getValue('amasty_fpc/debug/ips');
        $ips = preg_split('/\s*,\s*/', trim($ips), -1, PREG_SPLIT_NO_EMPTY);

        return $ips;
    }

    public function canDisplayStatus()
    {
        if (!$this->scopeConfig->isSetFlag('amasty_fpc/debug/show_status')) {
            return false;
        }

        if ($allowedIps = $this->getDebugIps()) {
            $clientIp = $this->request->getClientIp(true);
            if (!in_array($clientIp, $allowedIps)) {
                return false;
            }
        }

        return true;
    }

    public function getPagesConfig()
    {
        $config = $this->getValue('crawler/page_types');

        return unserialize($config);
    }

    protected function getCombinations($enabledSetting, $combinationsSetting)
    {
        if (!$this->scopeConfig->isSetFlag('amasty_fpc/combinations/' . $enabledSetting)) {
            return [];
        }

        $values = $this->scopeConfig->getValue('amasty_fpc/combinations/' . $combinationsSetting);

        return $this->split($values);
    }

    /**
     * Convert comma separated string to array
     *
     * @param $string
     *
     * @return array
     */
    protected function split($string)
    {
        $string = trim($string);

        if (!$string) {
            return [];
        } else {
            return explode(',', $string);
        }
    }
}
