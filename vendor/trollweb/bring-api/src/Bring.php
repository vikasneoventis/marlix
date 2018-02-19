<?php

namespace Trollweb\BringApi;

use Trollweb\BringApi\Request;
use Trollweb\BringApi\Response;
use Trollweb\BringApi\Result;

class Bring
{
    public function request(Request $request)
    {
        $curl = curl_init();
        $url = $request->buildUrl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        if ($request->headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request->headers);
        }

        $res = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Extract header and body
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = rtrim(substr($res, 0, $headerSize));
        $rawBody = substr($res, $headerSize);

        curl_close($curl);

        $response = new Response($status, $rawBody, $headers);
        $request->callOnResponse($response);
        return $response;
    }
}
