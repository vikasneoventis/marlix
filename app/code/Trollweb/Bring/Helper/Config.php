<?php

namespace Trollweb\Bring\Helper;

use \Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper {
    public function getActivePickupMethods($store = null) {
        $data = $this->getConfigValue('carriers/bringpickup/active_methods', $store);
        if (!$data) {
            return [];
        }

        return json_decode($data, true);
    }

    public function getActiveDeliveredMethods($store = null) {
        $data = $this->getConfigValue('carriers/bringdelivered/active_methods', $store);
        if (!$data) {
            return [];
        }

        return json_decode($data, true);
    }

    public function getBringPickupCarrierTitle($store = null) {
        return $this->getConfigValue('carriers/bringpickup/carrier_title', $store);
    }

    public function getBringDeliveredCarrierTitle($store = null) {
        return $this->getConfigValue('carriers/bringdelivered/carrier_title', $store);
    }

    public function pickupPointsEnabled($store = null) {
        return $this->getConfigValue('carriers/bringpickup/pickup_points_enabled', $store) === "1";
    }

    public function numberOfPickupPointsToShow($store = null) {
        return (int)$this->getConfigValue('carriers/bringpickup/number_of_pickup_points', $store);
    }

    public function mybringIsEnabled($store = null) {
        return $this->getConfigValue('trollweb_bring/mybring/enable_mybring', $store) === "1";
    }

    public function getMybringApiUserId($store = null) {
        return $this->getConfigValue('trollweb_bring/mybring/api_user_id', $store);
    }

    public function getMybringApiKey($store = null) {
        return $this->getConfigValue('trollweb_bring/mybring/api_key', $store);
    }

    public function getMybringCustomerNumber($store = null) {
        return $this->getConfigValue('trollweb_bring/mybring/customer_number', $store);
    }

    public function getMybringCredentials($store = null) {
        if (!$this->mybringIsEnabled($store)) {
            return null;
        }

        $userId = $this->getMybringApiUserId($store);
        $apiKey = $this->getMybringApiKey($store);
        $customerNumber = $this->getMybringCustomerNumber($store);

        if (!$userId || !$apiKey || !$customerNumber) {
            return null;
        }

        return [
            "api_user_id" => $userId,
            "api_key" => $apiKey,
            "customer_number" => $customerNumber,
        ];
    }

    public function getPostingAtPostoffice($store = null) {
        return $this->getConfigValue('trollweb_bring/general/posting_at_postoffice', $store);
    }

    public function showTransitTime($store = null) {
        return $this->getConfigValue('trollweb_bring/general/show_transit_time', $store) === "1";
    }

    public function getPriceRoundingStrategy($store = null) {
        return $this->getConfigValue('trollweb_bring/general/price_rounding_strategy', $store);
    }

    public function postcodeLookupEnabled($store = null) {
        return $this->getConfigValue('trollweb_bring/general/enable_postcode_lookup', $store) === "1";
    }

    public function getDefaultProductWeight($store = null) {
        return $this->getConfigValue('trollweb_bring/measurements/default_product_weight', $store);
    }

    public function getOriginCountryId($store = null) {
        return $this->getConfigValue('shipping/origin/country_id', $store);
    }

    public function getOriginPostcode($store = null) {
        return $this->getConfigValue('shipping/origin/postcode', $store);
    }

    public function getWeightUnit($store = null) {
        return $this->getConfigValue('trollweb_bring/measurements/weight_unit', $store);
    }

    public function debugLoggingEnabled($store = null) {
        return $this->getConfigValue('trollweb_bring/logging/debug_enabled', $store) === "1";
    }

    public function shippingIncludesTax($store = null) {
        return $this->getConfigValue('tax/calculation/shipping_includes_tax', $store) === "1";
    }

    private function getConfigValue($path, $store) {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }
}
