<?php

namespace Trollweb\Bring\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
 
class Pickup extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'bringpickup';
    private $rateResultFactory;
    private $rateMethodFactory;
    private $logger;
    private $config;
    private $pickupPointApi;
    private $carrier;
     
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Trollweb\Bring\Logger\Logger $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Trollweb\Bring\Helper\Config $config,
        \Trollweb\Bring\Helper\Api\PickupPoint $pickupPointApi,
        \Trollweb\Bring\Helper\Carrier $carrier,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->pickupPointApi = $pickupPointApi;
        $this->carrier = $carrier;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        $this->logger->debug("{$this->_code}: Collect rates");

        $activeMethods = $this->getActiveMethods();

        if (count($activeMethods) === 0) {
            $this->logger->debug("{$this->_code}: No active methods");
            return;
        }

        $requestData = $this->carrier->prepareRequestData($request, $activeMethods);

        try {
            $shippingGuideResult = $this->carrier->requestShippingGuideMethods($requestData, ["PICKUP_POINT"]);
        } catch (\Exception $e) {
            $this->logger->debug("{$this->_code}: Exception while requesting shipping guide methods: {$e->getMessage()}");
            return false;
        }

        try {
            $methods = $this->carrier->prepareMethods($request, $activeMethods, $shippingGuideResult);
        } catch (\Exception $e) {
            $this->logger->debug("{$this->_code}: Exception while preparing methods: {$e->getMessage()}");
            return false;
        }

        if (count($methods) === 0) {
            $this->logger->debug("{$this->_code}: No available methods");
            return false;
        }

        // Replace methods with pickup points if enabled
        if ($this->config->pickupPointsEnabled()) {
            $methods = $this->replaceWithPickupPoints($methods, $requestData);
        }

        $result = $this->rateResultFactory->create();

        foreach ($methods as $data) {
            $carrierTitle = $this->config->getBringPickupCarrierTitle();
            if ($data["transitTimeText"]) {
                $carrierTitle .= $data["transitTimeText"];
            }

            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($carrierTitle);
            $method->setMethod($data["methodId"]);
            $method->setMethodTitle($data["methodTitle"]);
            $method->setPrice($data["price"]);
            $method->setCost($data["price"]);
            $result->append($method);
        }

        $methodCount = count($methods);
        $this->logger->debug("{$this->_code}: Returning {$methodCount} methods");
        return $result;
    }

    public function getAllowedMethods()
    {
        $allMethods = $this->getAllMethods();
        $activeMethods = $this->getActiveMethods();
        return $this->carrier->getAllowedMethods($allMethods, $activeMethods);
    }

    public function getAllMethods()
    {
        return [
            'SERVICEPAKKE'  => 'Servicepakke',
            'PICKUP_PARCEL' => 'Pickup Parcel',
        ];
    }

    private function getActiveMethods() {
        $data = $this->config->getActivePickupMethods();
        return $this->carrier->prepareActiveMethods($data);
    }

    private function replaceWithPickupPoints($methods, $requestData) {
        $pickupPointsParams = [
            "requestCountryCode" => $requestData["origCountry"],
            "street" => $requestData["destStreet"],
            "numberOfResponses" => $this->config->numberOfPickupPointsToShow(),
        ];

        try {
            $pickupPointsResult = $this->pickupPointApi->listForPostalCode($requestData["destCountry"], $requestData["destPostcode"], $pickupPointsParams);
        } catch (\Exception $e) {
            $this->logger->debug("{$this->_code}: Exception while requesting pickup points: {$e->getMessage()}");
            return $methods;
        }

        try {
            $methods = $this->preparePickupPointMethods($pickupPointsResult, $methods);
        } catch (\Exception $e) {
            $this->logger->debug("{$this->_code}: Exception while preparing pickup points methods: {$e->getMessage()}");
        }

        return $methods;
    }

    private function preparePickupPointMethods($pickupPointsResult, $methods) {
        $pickupPoints = $pickupPointsResult["pickupPoint"];
        
        // Return orginal methods if we dont have any pickup points
        if (count($pickupPoints) === 0) {
            return $methods;
        }

        $pickupPointMethods = [];

        foreach ($pickupPoints as $pickupPoint) {
            // There should only be one method as SERVICEPAKKE is only
            // available in norway and PICKUP_PARCEL is not
            $method = $methods[0];

            // Use pickup point name as method title
            $method["methodTitle"] = $pickupPoint["name"];

            // Append pickup point id to method id, i.e. SERVICEPAKKE_12345
            $method["methodId"] .= "_{$pickupPoint["id"]}";

            $pickupPointMethods[] = $method;
        }

        // Reverse pickup points to get the closest one at the top in checkout
        return array_reverse($pickupPointMethods);
    }
}
