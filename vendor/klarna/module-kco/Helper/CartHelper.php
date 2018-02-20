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

use Magento\Directory\Model\Country;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class CartHelper extends AbstractHelper
{
    /**
     * @var CountryCollection
     */
    protected $countryCollection;

    public function __construct(Context $context, CountryCollection $countryCollection)
    {
        parent::__construct($context);
        $this->countryCollection = $countryCollection;
    }

    /**
     * Get an object of default destination details
     *
     * @param   \Magento\Store\Model\Store $store
     *
     * @return  DataObject
     */
    public function getDefaultDestinationAddress($store = null)
    {
        $shippingDestinationObject = new DataObject(
            [
                'country_id' => $this->getStoreConfig('general/store_information/country_id', $store),
                'region_id'  => null,
                'postcode'   => null,
                'scope'      => $store
            ]
        );

        $this->_eventManager->dispatch(
            'kco_get_default_destination_address',
            [
                'shipping_destination' => $shippingDestinationObject
            ]
        );

        return $shippingDestinationObject;
    }

    /**
     * Get config based on key and store
     *
     * @param $key
     * @param $store
     * @return mixed
     */
    public function getStoreConfig($key, $store)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Lookup country ID from iso2/iso3 name
     *
     * @param string $country
     * @return string
     */
    public function getCountry($country)
    {
        $country = strtoupper($country);
        $this->countryCollection->clear();
        $this->countryCollection->addCountryCodeFilter($country);
        /** @var Country $c */
        foreach ($this->countryCollection as $c) {
            if ($c->getIso2Code() === $country || $c->getIso3Code() === $country) {
                return $c->getId();
            }
        }
        return $country;
    }
}
