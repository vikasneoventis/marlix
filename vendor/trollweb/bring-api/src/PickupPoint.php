<?php

namespace Trollweb\BringApi;

use Trollweb\BringApi\Request;
use Trollweb\BringApi\Exception\ResponseException;

class PickupPoint {
    private $baseUrl = "https://api.bring.com/pickuppoint/api/pickuppoint";

    // See available params here: http://developer.bring.com/api/pickup-point/#pickup-points-for-postal-code
    public function listForPostalCode($countryCode, $postalCode, $params = []) {
        $url = "{$this->baseUrl}/{$countryCode}/postalCode/{$postalCode}.json";
        $request = new Request(Request::METHOD_GET, $url, $params);

        $request->onResponse(function($req, $res) {
            if ($res->getStatus() !== 200) {
                throw new ResponseException("Expected status code 200 got {$res->getStatus()}", $req, $res);
            }
        });

        return $request;
    }
}
