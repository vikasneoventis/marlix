<?php

namespace Trollweb\BringApi;

class Request {
    const METHOD_GET = "get";

    public $method;
    public $url;
    public $query;
    public $body;
    public $headers;
    private $onResponseCallback;

    public function __construct($method, $url, $query = null, $headers = []) {
        $this->method = $method;
        $this->url = $url;
        $this->query = $query;
        $this->headers = $headers;
    }

    public function onResponse($callback) {
        $this->onResponseCallback = $callback;
    }

    public function callOnResponse($response) {
        $callback = $this->onResponseCallback;
        if ($callback) {
            $callback($this, $response);
        }
    }

    public function buildUrl() {
        if (count($this->query) > 0) {
            $query = $this->buildQuery($this->query);
            return "{$this->url}?{$query}";
        }

        return $this->url;
    }

    // Custom build query function to be able to create query with duplicate keys
    // The following: http_build_query(["product" => ["SERVICEPAKKE", "A-POST"]]);
    // Results in: product%5B0%5D=SERVICEPAKKE&product%5B1%5D=A-POST (product[0]=SERVICEPAKKE&product[1]=A-POST)
    // This function returns: product=SERVICEPAKKE&product=A-POST instead
    public function buildQuery($params) {
        $query = http_build_query($params);
        return preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $query);
    }

    public function debug() {
        return [
            "method" => $this->method,
            "url" => $this->url,
            "query" => $this->query,
            "urlWithQuery" => $this->buildUrl(),
            "body" => $this->body,
            "headers" => $this->headers,
        ];
    }
}
