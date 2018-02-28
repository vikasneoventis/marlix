<?php

namespace Trollweb\Bring\Helper;

use \Trollweb\BringApi\Request;
use \Trollweb\BringApi\Exception\ResponseException;

class Api {
    private $bringApi;
    private $logger;

    public function __construct(
        \Trollweb\BringApi\Bring $bringApi,
        \Trollweb\Bring\Logger\Logger $logger
    ) {
        $this->bringApi = $bringApi;
        $this->logger = $logger;
    }

    public function request(Request $req) {
        try {
            $res = $this->bringApi->request($req);
            $this->logger->debug("bring api request", ["request" => $req->debug(), "response" => $res->debug()]);
        } catch (ResponseException $e) {
            $req = $e->getRequest();
            $res = $e->getResponse();
            $msg = $e->getMessage();
            $this->logger->info("bring api request exception", ["message" => $msg, "request" => $req->debug(), "response" => $res->debug()]);
            throw $e;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->info("bring api request exception", ["message" => $msg]);
            throw $e;
        }

        return $res;
    }
}

