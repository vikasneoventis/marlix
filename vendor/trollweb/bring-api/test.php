<?php


include "vendor/autoload.php";

class tester
{
    
    public function shippingGuide() {
        $bring = new \Trollweb\BringApi\Bring();
        $shippingGuide = new \Trollweb\BringApi\ShippingGuide();
        $request = $shippingGuide->all([
            "from" => 4314,
            "to" => 1337,
            "weightInGrams" => 100,
            "product" => "SERVICEPAKKE",
            "clientUrl" => "http://trollweb.no"
        ]);
        var_dump($request);
        $response = $bring->request($request);
        var_dump($response);
    }

    public function pickupPoints() {
        $bring = new \Trollweb\BringApi\Bring();
        $pickupPoint = new \Trollweb\BringApi\PickupPoint();
        $request = $pickupPoint->listForPostalCode("no", "4329");
        $response = $bring->request($request);
        var_dump($response);
    }
}


$tester = new tester();
//$tester->shippingGuide();
$tester->pickupPoints();
