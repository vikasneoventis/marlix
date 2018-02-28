<?php

namespace Trollweb\Bring\Helper\Api;

class PostalCode {
    private $postalCodeApi;
    private $api;
    private $logger;

    public function __construct(
        \Trollweb\BringApi\PostalCode $postalCodeApi,
        \Trollweb\Bring\Helper\Api $api,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->postalCodeApi = $postalCodeApi;
        $this->api = $api;
        $this->logger = $logger;
    }

    public function lookup($postalCode, $countryCode, $clientUrl) {
        $request = $this->postalCodeApi->lookup($postalCode, $countryCode, $clientUrl);
        $res = $this->api->request($request);
        return $res->getBody();
    }
}


