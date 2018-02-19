<?php

namespace Trollweb\Bring\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
 
class Delivered extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'bringdelivered';
    private $rateResultFactory;
    private $rateMethodFactory;
    private $logger;
    private $config;
    private $carrier;
     
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Trollweb\Bring\Logger\Logger $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Trollweb\Bring\Helper\Config $config,
        \Trollweb\Bring\Helper\Carrier $carrier,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->carrier = $carrier;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        $this->logger->debug("{$this->_code}: Collect rates");

        $activeMethods = $this->getActiveMethods();

        if (count($activeMethods) === 0) {
            $this->logger->debug("{$this->_code}: No active methods");
            return false;
        }

        $requestData = $this->carrier->prepareRequestData($request, $activeMethods);

        try {
            $shippingGuideResult = $this->carrier->requestShippingGuideMethods($requestData);
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

        $result = $this->rateResultFactory->create();

        foreach ($methods as $data) {
            $carrierTitle = $this->config->getBringDeliveredCarrierTitle();
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
            'A-POST'            => 'A-post',
            'B-POST'            => 'B-post',
            'SMAAPAKKER_A-POST' => 'Småpakke A-Post',
            'SMAAPAKKER_B-POST' => 'Småpakke B-Post',
            'MINIPAKKE'         => 'Minipakke',
            'PA_DOREN'          => 'På døren',
            'BPAKKE_DOR-DOR'    => 'Bedriftspakke Dør-Dør',
            'EKSPRESS07'        => 'Ekspress 07',
            'EKSPRESS09'        => 'Ekspress 09',
            'QUICKPACK'         => 'Quickpack',
            'BUSINESS_PARCEL'   => 'Business Parcel',
            'COURIER_VIP'       => 'Bud VIP',
            'COURIER_1H'        => 'Bud 1 time',
            'COURIER_2H'        => 'Bud 2 timer',
            'COURIER_4H'        => 'Bud 4 timer',
            'COURIER_6H'        => 'Bud 6 timer',
            'CARGO_GROUPAGE'    => 'Cargo',
        ];
    }

    public function getActiveMethods() {
        $data = $this->config->getActiveDeliveredMethods();
        return $this->carrier->prepareActiveMethods($data);
    }
}
