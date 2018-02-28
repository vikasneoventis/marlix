<?php

namespace Trollweb\Bring\Helper;

use \Magento\Quote\Model\Quote\Address\RateRequest;

class Carrier extends \Magento\Framework\App\Helper\AbstractHelper {
    private $config;
    private $measurement;
    private $priceHelper;
    private $shippingGuideApi;
    private $logger;
    private $store;

    public function __construct(
        \Trollweb\Bring\Helper\Config $config,
        \Trollweb\Bring\Helper\Measurement $measurement,
        \Trollweb\Bring\Helper\Price $priceHelper,
        \Trollweb\Bring\Helper\Api\ShippingGuide $shippingGuideApi,
        \Trollweb\Bring\Logger\Logger $logger,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->measurement = $measurement;
        $this->priceHelper = $priceHelper;
        $this->shippingGuideApi = $shippingGuideApi;
        $this->logger = $logger;
        $this->store = $store;
    }

    public function getAllowedMethods($allMethods, $activeMethods)
    {
        $allowedMethods = [];

        foreach ($activeMethods as $method) {
            $methodId = $method["method_id"];
            if (!isset($allMethods[$methodId])) {
                continue;
            }

            $methodName = $allMethods[$methodId];
            $allowedMethods[$methodId] = $methodName;
        }

        return $allowedMethods;
    }

    public function prepareRequestData(RateRequest $request, $activeMethods) {
        $items = $this->getItems($request);

        return [
            "origCountry" => $this->config->getOriginCountryId(),
            "destCountry" => $request->getDestCountryId(),
            "origPostcode" => $this->formatPostcode($this->config->getOriginPostcode()),
            "destPostcode" => $this->formatPostcode($request->getDestPostcode()),
            "destStreet" => $request->getDestStreet(),
            "totalWeightInGrams" => $this->getTotalWeightInGrams($items),
            "bringProductIds" => $this->getBringProductIds($activeMethods),
        ];
    }

    public function requestShippingGuideMethods($requestData, $additional = []) {
        // Api requires DA instead of DK for danish language
        $language = str_replace("DK", "DA", $requestData["origCountry"]);

        $shippingGuideParams = [
            "fromCountry" => $requestData["origCountry"],
            "toCountry" => $requestData["destCountry"],
            "from" => $requestData["origPostcode"],
            "to" => $requestData["destPostcode"],
            "weightInGrams" => $requestData["totalWeightInGrams"],
            "product" => $requestData["bringProductIds"],
            "clientUrl" => $this->store->getBaseUrl(),
            "edi" => "true",
            "language" => $language,
            "postingAtPostOffice" => $this->config->getPostingAtPostoffice() ? "true" : "false",
            "additional" => array_merge(["EVARSLING"], $additional),
        ];

        return $this->shippingGuideApi->all($shippingGuideParams);
    }

    public function prepareActiveMethods($methods) {
        $activeMethods = [];

        // Make sure that each method has all fields
        foreach ($methods as $id => $data) {
            $fields = [
                "method_id" => "",
                "custom_price" => "",
                "min_weight" => "",
                "max_weight" => "",
                "allow_free_shipping" => false,
            ];

            $activeMethods[] = array_merge($fields, $data);
        }

        return $activeMethods;
    }

    public function prepareMethods($request, $activeMethods, $bringData) {
        $methods = [];

        // Response does not have a Product field when no products are available
        if (!isset($bringData["Product"])) {
            return $methods;
        }

        // Make sure that we always have an array of products
        // The api only returns an array if there are more than one result
        $bringProducts = isset($bringData["Product"]["ProductId"]) ? [$bringData["Product"]] : $bringData["Product"];

        foreach ($bringProducts as $bringProduct) {
            $methodId = $bringProduct["ProductId"];
            $activeMethod = $this->getActiveMethod($activeMethods, $methodId);
            if (!$activeMethod) {
                continue;
            }

            $price = $this->getPrice($request, $activeMethod, $bringProduct);
            $transitTimeText = $this->getTransitTimeText($bringProduct);

            $methods[] = [
                "methodId" => $methodId,
                "methodTitle" => $bringProduct["GuiInformation"]["DisplayName"],
                "price" => $price,
                "transitTimeText" => $transitTimeText,
            ];
        }

        return $methods;
    }

    private function getItems(RateRequest $request) {
        return array_filter($request->getAllItems(), function($item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                return false;
            } else {
                return true;
            }
        });
    }

    private function getBringProductIds($activeMethods) {
        $ids = [];

        foreach ($activeMethods as $activeMethod) {
            $ids[] = $activeMethod["method_id"];
        }

        return array_unique($ids);
    }

    private function getTotalWeightInGrams($items) {
        $totalWeight = 0.0;

        foreach ($items as $item) {
            $weight = (float)$item->getWeight();
            if (!$weight) {
                $weight = (float)$this->config->getDefaultProductWeight();
            }
            $totalWeight += $item->getQty() * $weight;
        }

        return $this->measurement->getWeightInGrams($totalWeight);
    }

    private function formatPostcode($code) {
        // Remove spaces from postal code
        return preg_replace("/\s+/", "", $code);
    }

    private function getActiveMethod($activeMethods, $methodId) {
        foreach ($activeMethods as $method) {
            if ($method["method_id"] === $methodId) {
                return $method;
            }
        }

        return null;
    }

    private function getPrice($request, $activeMethod, $bringProduct) {
        if ($request->getFreeShipping() && $activeMethod["allow_free_shipping"]) {
            return 0;
        }

        $customPrice = (int)$activeMethod["custom_price"];
        if ($customPrice > 0) {
            return $customPrice;
        }

        $price = $this->getBringProductPrice($bringProduct);
        return $this->priceHelper->round($price);
    }

    private function getBringProductPrice($bringProduct) {
        if ($this->config->shippingIncludesTax()) {
            return $bringProduct["Price"]["PackagePriceWithAdditionalServices"]["AmountWithVAT"];
        } else {
            return $bringProduct["Price"]["PackagePriceWithAdditionalServices"]["AmountWithoutVAT"];
        }
    }

    // Note: Sometimes the api returns FormattedExpectedDeliveryDate, and some other fields
    // {"WorkingDays":"0","UserMessage":null,"AlternativeDeliveryDates":null}
    // {"WorkingDays":"1","UserMessage":null,"FormattedExpectedDeliveryDate":"16.03.2017","ExpectedDeliveryDate":{"Year":"2017","Month":"3","Day":"16"},"AlternativeDeliveryDates":null,"FormattedEarliestPickupDate":"15.03.2017 10:08","EarliestPickupDate":{"Year":"2017","Month":"3","Day":"15","Hour":"10","Minute":"8"}}
    private function getTransitTimeText($bringProduct) {
        if (!$this->config->showTransitTime()) {
            return "";
        }

        if (!isset($bringProduct["ExpectedDelivery"]["WorkingDays"])) {
            return "";
        }

        $days = $bringProduct["ExpectedDelivery"]["WorkingDays"];

        if ($days == 0) {
            return __(" | Expected transit time: same day");
        } else if ($days == 1) {
            return __(" | Expected transit time: %1 day", $days);
        } else {
            return __(" | Expected transit time: %1 days", $days);
        }
    }
}

