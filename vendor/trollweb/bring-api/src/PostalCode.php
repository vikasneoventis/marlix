<?php

namespace Trollweb\BringApi;

use Trollweb\BringApi\Request;
use Trollweb\BringApi\Exception\ResponseException;

class PostalCode {
    private $baseUrl = "https://api.bring.com/shippingguide/api/postalCode.json";

    public function lookup($postalCode, $countryCode, $clientUrl) {
        $params = [
            "pnr" => $postalCode,
            "country" => $countryCode,
            "clientUrl" => $clientUrl,
        ];

        $request = new Request(Request::METHOD_GET, $this->baseUrl, $params);

        $request->onResponse(function($req, $res) {
            if ($res->getStatus() !== 200) {
                throw new ResponseException("Expected status code 200 got {$res->getStatus()}", $req, $res);
            }
        });

        return $request;
    }
}
