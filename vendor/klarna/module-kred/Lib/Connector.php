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

use Klarna_Checkout_ConnectorInterface;
use Klarna_Checkout_Digest;
use Klarna_Checkout_HTTP_Transport;
use Klarna_Checkout_UserAgent;

class Connector extends \Klarna_Checkout_Connector
{
    /**
     * Create a new Checkout Connector
     *
     * @param string                    $secret    string used to sign requests
     * @param string                    $domain    the domain used for requests
     * @param Klarna_Checkout_UserAgent $userAgent User Agent
     *
     * @return Klarna_Checkout_ConnectorInterface
     */
    public static function create($secret, $domain = self::BASE_URL, $userAgent = null)
    {
        return new MagentoConnector(
            Klarna_Checkout_HTTP_Transport::create(),
            new Klarna_Checkout_Digest,
            $secret,
            $domain,
            $userAgent
        );
    }
}
