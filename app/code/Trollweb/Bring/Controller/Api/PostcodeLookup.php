<?php

namespace Trollweb\Bring\Controller\Api;

class PostcodeLookup extends \Magento\Framework\App\Action\Action
{
    private $jsonFactory;
    private $formKeyValidator;
    private $store;
    private $postalCodeApi;
    private $config;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Trollweb\Bring\Helper\Api\PostalCode $postalCodeApi,
        \Trollweb\Bring\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->store = $store;
        $this->postalCodeApi = $postalCodeApi;
        $this->config = $config;
    }

    public function execute() {
        $req = $this->getRequest();
        $result = $this->jsonFactory->create();
        $postcode = $req->getParam("postcode");
        $countryCode = $req->getParam("country");

        if (!$this->config->postcodeLookupEnabled()) {
            return $result
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN)
                ->setData(["message" => __("Postcode lookup is not enabled")]);
        }

        if (!$this->formKeyValidator->validate($req)) {
            return $result
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN)
                ->setData(["message" => __("Invalid/missing form key")]);
        }

        $clientUrl = $this->store->getBaseUrl();
        $data = $this->postalCodeApi->lookup($postcode, $countryCode, $clientUrl);

        return $result->setData($data);
    }
}
