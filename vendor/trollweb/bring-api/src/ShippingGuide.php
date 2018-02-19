<?php

namespace Trollweb\BringApi;

use Trollweb\BringApi\Request;
use Trollweb\BringApi\Exception\ResponseException;

class ShippingGuide {
    private $baseUrl = "https://api.bring.com/shippingguide";

    // See available params here: http://developer.bring.com/api/shipping-guide/#get-shipment-prices-estimated-delivery-and-more
    public function all($params, $mybringCredentials = null) {
        $url = "{$this->baseUrl}/products/all.json";

        $headers = null;
        if ($mybringCredentials) {
            // Add auth headers
            $headers = [
                sprintf("X-MyBring-API-Uid: %s", $mybringCredentials["api_user_id"]),
                sprintf("X-MyBring-API-Key: %s", $mybringCredentials["api_key"]),
            ];

            // Add customer number
            $params["customerNumber"] = $mybringCredentials["customer_number"];
        }

        $request = new Request(Request::METHOD_GET, $url, $params, $headers);

        $request->onResponse(function($req, $res) {
            if ($res->getStatus() !== 200) {
                throw new ResponseException("Expected status code 200 got {$res->getStatus()}", $req, $res);
            }
        });

        return $request;
    }
}
