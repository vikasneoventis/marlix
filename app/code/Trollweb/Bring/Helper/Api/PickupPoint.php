<?php

namespace Trollweb\Bring\Helper\Api;

class PickupPoint {
    private $pickupPointApi;
    private $api;
    private $logger;

    public function __construct(
        \Trollweb\BringApi\PickupPoint $pickupPointApi,
        \Trollweb\Bring\Helper\Api $api,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->pickupPointApi = $pickupPointApi;
        $this->api = $api;
        $this->logger = $logger;
    }

    public function listForPostalCode($toCountryCode, $toPostalCode, $params = []) {
        $request = $this->pickupPointApi->listForPostalCode($toCountryCode, $toPostalCode, $params);
        $res = $this->api->request($request);
        return $res->getBody();
    }
}


