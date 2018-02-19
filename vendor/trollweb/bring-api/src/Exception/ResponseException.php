<?php

namespace Trollweb\BringApi\Exception;


class ResponseException extends \Exception {
    private $request;
    private $response;

    public function __construct($msg, $request, $response) {
        $this->request = $request;
        $this->response = $response;
        parent::__construct($msg, 0, null);
    }

    public function getRequest() {
        return $this->request;
    }

    public function getResponse() {
        return $this->response;
    }
}
