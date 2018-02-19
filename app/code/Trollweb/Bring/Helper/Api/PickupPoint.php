<?php

namespace Trollweb\Bring\Helper\Api;

class PickupPoint {
    private $api;
    private $logger;

    public function __construct(
        \Trollweb\Bring\Helper\Api $api,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->api = $api;
        $this->logger = $logger;
    }

    public function listForPostalCode($toCountryCode, $toPostalCode, $params = []) {
        $pickupPointApi = new \Trollweb\BringApi\PickupPoint();
        $request = $pickupPointApi->listForPostalCode($toCountryCode, $toPostalCode, $params);
        $res = $this->api->request($request);
        return $res->getBody();
    }
}


