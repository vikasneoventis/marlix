<?php

namespace Trollweb\BringApi;

class Response {
    private $status;
    private $headers;
    private $rawBody;

    public function __construct($status, $rawBody, $headers = null) {
        $this->status = $status;
        $this->rawBody = $rawBody;
        $this->headers = $headers;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getRawBody() {
        return $this->rawBody;
    }

    public function getBody() {
        if ($this->rawBody) {
            return json_decode($this->rawBody, true);
        }

        return null;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function debug() {
        return [
            "status" => $this->status,
            "headers" => $this->headers,
            "rawBody" => $this->rawBody,
        ];
    }
}
