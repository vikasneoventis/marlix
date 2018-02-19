<?php

namespace Trollweb\Bring\Helper\Api;

class ShippingGuide {
    private $api;
    private $logger;

    public function __construct(
        \Trollweb\Bring\Helper\Api $api,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->api = $api;
        $this->logger = $logger;
    }

    public function all($params) {
        $shippingGuideApi = new \Trollweb\BringApi\ShippingGuide();
        $request = $shippingGuideApi->all($params);
        $res = $this->api->request($request);
        return $res->getBody();
    }
}
