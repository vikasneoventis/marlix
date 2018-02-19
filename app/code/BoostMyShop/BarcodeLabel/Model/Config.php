<?php

namespace BoostMyShop\BarcodeLabel\Model;

class Config
{
    protected $_scopeConfig;
    protected $_currency;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Directory\Model\Currency $currency){
        $this->_scopeConfig = $scopeConfig;
        $this->_currency = $currency;
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('barcodelabel/'.$path, 'store', $storeId);
    }

    public function isEnabled()
    {
        return $this->getSetting('general/enable');
    }

    public function getBarcodeAttribute()
    {
        $value = $this->getSetting('general/barcode_attribute');
        if (!$value)
            throw new \Exception('Barcode attribute is not configured in stores > configuration > boostmyshop > barcode label');
        return $value;
    }

    public function getCurrencySymbol()
    {
        $currencyCode = $this->_scopeConfig->getValue('currency/options/base', 'store', 0);
        $currency = $this->_currency->load($currencyCode);
        return $currency->getCurrencySymbol();
    }
}