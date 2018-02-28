<?php

namespace Trollweb\Bring\Helper\Api;

class ShippingGuide {
    private $storeManager;
    private $shippingGuideApi;
    private $api;
    private $config;
    private $logger;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Trollweb\BringApi\ShippingGuide $shippingGuideApi,
        \Trollweb\Bring\Helper\Api $api,
        \Trollweb\Bring\Helper\Config $config,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->shippingGuideApi = $shippingGuideApi;
        $this->api = $api;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function all($params) {
        $store = $this->storeManager->getStore();
        $credentials = $this->config->getMybringCredentials($store);
        $request = $this->shippingGuideApi->all($params, $credentials);
        $res = $this->api->request($request);
        return $res->getBody();
    }
}
