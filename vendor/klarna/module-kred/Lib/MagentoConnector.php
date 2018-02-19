<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Lib;

use Klarna_Checkout_Connector;
use Klarna_Checkout_Digest;
use Klarna_Checkout_HTTP_TransportInterface;
use Klarna_Checkout_UserAgent;

class MagentoConnector extends \Klarna_Checkout_BasicConnector
{
    /**
     * UserAgent string builder
     *
     * @var Klarna_Checkout_UserAgent
     */
    protected $userAgent;

    /**
     * Create the user agent identifier to use
     *
     * @return Klarna_Checkout_UserAgent
     */
    protected function userAgent()
    {
        return $this->userAgent;
    }

    /**
     * Create a new Checkout Connector
     *
     * @param Klarna_Checkout_HTTP_TransportInterface $http      Transport
     * @param Klarna_Checkout_Digest                  $digester  Digest Generator
     * @param string                                  $secret    Shared secret
     * @param string                                  $domain    Domain of the request
     * @param Klarna_Checkout_UserAgent               $userAgent User Agent
     */
    public function __construct(
        Klarna_Checkout_HTTP_TransportInterface $http,
        Klarna_Checkout_Digest $digester,
        $secret,
        $domain = Klarna_Checkout_Connector::BASE_URL,
        $userAgent = null
    ) {
        parent::__construct($http, $digester, $secret, $domain);
        if (null === $userAgent) {
            $userAgent = new Klarna_Checkout_UserAgent();
        }
        $this->userAgent = $userAgent;
    }
}
