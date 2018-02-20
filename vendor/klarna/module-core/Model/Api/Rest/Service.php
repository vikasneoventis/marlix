<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Psr\Log\LoggerInterface;

class Service implements ServiceInterface
{
    const LOG_FORMAT = ">>>>>>>> REQUEST:\n{request}\n<<<<<<<< RESPONSE:\n{response}\n-------- ERRORS:\n{error}\n++++++++\n";

    /**
     * Holds headers to be sent in HTTP request
     *
     * @var array
     */
    protected $headers = [];

    /**
     * The base URL to interact with
     *
     * @var string
     */
    protected $uri = '';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var LoggerInterface $log
     */
    protected $log;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Initialize class
     *
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $this->log,
                new MessageFormatter(self::LOG_FORMAT)
            )
        );
        $this->client = new Client(
            [
                'handler' => $stack,
            ]
        );

    }

    /**
     * @inheritdoc
     */
    public function setUserAgent($product, $version, $mageVersion, $mageMode, $keepCurrent = true)
    {
        if (!isset($this->headers['User-Agent'])) {
            $this->headers['User-Agent'] = '';
        }
        $current = ' ' . $this->headers['User-Agent'];
        if (!$keepCurrent) {
            $current = '';
        }
        $this->setHeader(
            'User-Agent',
            $product . '/' . $version . ' (Magento ' . $mageVersion . ' ' . $mageMode . ' mode' . $current . ')'
        );
    }

    /**
     * @inheritdoc
     */
    public function setHeader($header, $value = null)
    {
        if (!$value) {
            unset($this->headers[$header]);
            return;
        }
        $this->headers[$header] = $value;
    }

    /**
     * @inheritdoc
     */
    public function makeRequest($url, $body = '', $method = ServiceInterface::POST)
    {
        $response = [
            'is_successful' => false
        ];
        try {
            $data = [
                'headers' => $this->headers,
                'json'    => $body
            ];
            $data = $this->getAuth($data);

            /** @var ResponseInterface $response */
            $response = $this->client->$method($url, $data);
            $response = $this->processResponse($response);
            $response['is_successful'] = true;
        } catch (BadResponseException $e) {
            $this->log->error('Bad Response: ' . $e->getMessage());
            $this->log->error((string)$e->getRequest()->getBody());
            $response['response_status_code'] = $e->getCode();
            $response['response_status_message'] = $e->getMessage();
            $response = $this->processResponse($response);
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $this->log->error($errorResponse->getStatusCode() . ' ' . $errorResponse->getReasonPhrase());
                $body = $this->processResponse($errorResponse);
                $response = array_merge($response, $body);
            }
            $response['exception_code'] = $e->getCode();
        } catch (\Exception $e) {
            $this->log->error('Exception: ' . $e->getMessage());
            $response['exception_code'] = $e->getCode();
        }
        return $response;
    }

    /**
     * Set auth data if username or password has been provided
     *
     * @param $data
     * @return mixed
     */
    protected function getAuth($data)
    {
        if ($this->username || $this->password) {
            $data['auth'] = [$this->username, $this->password];
        }
        return $data;
    }

    /**
     * Process the response and return an array
     *
     * @param ResponseInterface|array $response
     * @return array
     * @throws \Klarna\Core\Model\Api\Exception
     */
    protected function processResponse($response)
    {
        if (is_array($response)) {
            return $response;
        }
        try {
            $data = json_decode((string)$response->getBody(), true);
        } catch (\Exception $e) {
            $data = [
                'exception' => $e->getMessage()
            ];
        }
        if ($response->getStatusCode() === 401) {
            throw new KlarnaApiException(__($response->getReasonPhrase()));
        }
        $data['response_object'] = $response;
        $data['response_status_code'] = $response->getStatusCode();
        $data['response_status_message'] = $response->getReasonPhrase();
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function connect($username, $password, $connectUrl = null)
    {
        $this->username = $username;
        $this->password = $password;
        if ($connectUrl) {
            $this->uri = $connectUrl;
        }
        return true;
    }
}
