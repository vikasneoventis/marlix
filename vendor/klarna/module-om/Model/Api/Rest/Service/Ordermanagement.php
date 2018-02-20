<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Model\Api\Rest\Service;

use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Helper\VersionInfo;
use Magento\Framework\DataObject;
use Magento\Framework\Module\ResourceInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class Ordermanagement
{
    const API_VERSION = 'v1';

    /**
     * @var ServiceInterface
     */
    protected $service;

    /**
     * @var string
     */
    protected $uri;

    /**
     * Initialize class
     *
     * @param ServiceInterface      $service
     * @param ResourceInterface     $moduleResource
     * @param StoreManagerInterface $store
     * @param VersionInfo           $versionInfo
     */
    public function __construct(
        ServiceInterface $service,
        ResourceInterface $moduleResource,
        StoreManagerInterface $store,
        VersionInfo $versionInfo
    ) {
        $this->service = $service;

        $version = $versionInfo->getVersion('klarna/module-om');
        $mageMode = $versionInfo->getMageMode();
        $mageVersion = $versionInfo->getMageEdition() . ' ' . $versionInfo->getMageVersion();
        $this->service->setUserAgent('Magento2_OM', $version, $mageVersion, $mageMode);
        $this->service->setHeader('Accept', '*/*');
    }

    /**
     * Setup connection based on store config
     *
     * @param string $user
     * @param string $password
     * @param string $url
     */
    public function resetForStore($user, $password, $url)
    {
        $this->service->connect($user, $password, $url);
        $this->uri = $url;
    }

    /**
     * Used by merchants to acknowledge the order.
     *
     * Merchants will receive the order confirmation push until the order has been acknowledged.
     *
     * @param $id
     *
     * @return array
     */
    public function acknowledgeOrder($id)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/acknowledge";
        return $this->service->makeRequest($url, '', ServiceInterface::POST);
    }

    /**
     * Get the current state of an order
     *
     * @param $id
     *
     * @return array
     */
    public function getOrder($id)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}";
        return $this->service->makeRequest($url, '', ServiceInterface::GET);
    }

    /**
     * Update the total order amount of an order, subject to a new customer credit check.
     *
     * The updated amount can optionally be accompanied by a descriptive text and new order lines. Supplied order lines
     * will replace the existing order lines. If no order lines are supplied in the call, the existing order lines will
     * be deleted. The updated 'order_amount' must not be negative, nor less than current 'captured_amount'. Currency
     * is inferred from the original order.
     *
     * @param string $id
     * @param array  $data
     *
     * @return array
     */
    public function updateOrderItems($id, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/authorization";
        return $this->service->makeRequest($url, $data, ServiceInterface::PATCH);
    }

    /**
     * Extend the order's authorization by default period according to merchant contract.
     *
     * @param string $id
     *
     * @return array
     */
    public function extendAuthorization($id)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/extend-authorization-time";
        return $this->service->makeRequest($url, '', ServiceInterface::POST);
    }

    /**
     * Update one or both merchant references. To clear a reference, set its value to "" (empty string).
     *
     * @param string $id
     * @param string $merchantReference1
     * @param string $merchantReference2
     *
     * @return array
     */
    public function updateMerchantReferences($id, $merchantReference1, $merchantReference2 = null)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/merchant-references";

        $data = [
            'merchant_reference1' => $merchantReference1
        ];

        if ($merchantReference2 !== null) {
            $data['merchant_reference2'] = $merchantReference2;
        }
        return $this->service->makeRequest($url, $data, ServiceInterface::PATCH);
    }

    /**
     * Update billing and/or shipping address for an order, subject to customer credit check.
     * Fields can be updated independently. To clear a field, set its value to "" (empty string).
     *
     * Mandatory fields can not be cleared
     *
     * @param string $id
     * @param array  $data
     *
     * @return array
     */
    public function updateAddresses($id, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/customer-details";
        return $this->service->makeRequest($url, $data, ServiceInterface::PATCH);
    }

    /**
     * Cancel an authorized order. For a cancellation to be successful, there must be no captures on the order.
     * The authorized amount will be released and no further updates to the order will be allowed.
     *
     * @param $id
     *
     * @return array
     */
    public function cancelOrder($id)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/cancel";
        return $this->service->makeRequest($url, '', ServiceInterface::POST);
    }

    /**
     * Capture the supplied amount. Use this call when fulfillment is completed, e.g. physical goods are being shipped
     * to the customer.
     * 'captured_amount' must be equal to or less than the order's 'remaining_authorized_amount'.
     * Shipping address is inherited from the order. Use PATCH method below to update the shipping address of an
     * individual capture. The capture amount can optionally be accompanied by a descriptive text and order lines for
     * the captured items.
     *
     * @param $id
     * @param $data
     *
     * @return array
     * @throws \Klarna\Core\Model\Api\Exception
     */
    public function captureOrder($id, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/captures";
        return $this->service->makeRequest($url, $data, ServiceInterface::POST);
    }

    /**
     * Retrieve a capture
     *
     * @param $id
     * @param $captureId
     *
     * @return array
     */
    public function getCapture($id, $captureId)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/captures/{$captureId}";
        return $this->service->makeRequest($url, '', ServiceInterface::GET);
    }

    /**
     * Appends new shipping info to a capture.
     *
     * @param $id
     * @param $captureId
     * @param $data
     *
     * @return array
     */
    public function addShippingDetailsToCapture($id, $captureId, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/captures/{$captureId}/shipping-info";
        return $this->service->makeRequest($url, $data, ServiceInterface::POST);
    }

    /**
     * Update the billing address for a capture. Shipping address can not be updated.
     * Fields can be updated independently. To clear a field, set its value to "" (empty string).
     *
     * Mandatory fields can not be cleared,
     *
     * @param $id
     * @param $captureId
     * @param $data
     *
     * @return array
     */
    public function updateCaptureBillingAddress($id, $captureId, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/captures/{$captureId}/customer-details";
        return $this->service->makeRequest($url, $data, ServiceInterface::PATCH);
    }

    /**
     * Trigger a new send out of customer communication., typically a new invoice, for a capture.
     *
     * @param $id
     * @param $captureId
     *
     * @return array
     */
    public function resendOrderInvoice($id, $captureId)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/captures/{$captureId}/trigger-send-out";
        return $this->service->makeRequest($url, '', ServiceInterface::POST);
    }

    /**
     * Refund an amount of a captured order. The refunded amount will be credited to the customer.
     * The refunded amount must not be higher than 'captured_amount'.
     * The refunded amount can optionally be accompanied by a descriptive text and order lines.
     *
     * @param $id
     * @param $data
     *
     * @return array
     */
    public function refund($id, $data)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/refunds";
        return $this->service->makeRequest($url, $data, ServiceInterface::POST);
    }

    /**
     * Signal that there is no intention to perform further captures.
     *
     * @param $id
     *
     * @return array
     */
    public function releaseAuthorization($id)
    {
        $url = "{$this->uri}/ordermanagement/" . self::API_VERSION . "/orders/{$id}/release-remaining-authorization";
        return $this->service->makeRequest($url, '', ServiceInterface::POST);
    }

    /**
     * Get resource id from Location URL
     *
     * This assumes the ID is the last url path
     *
     * @param string|DataObject $location
     *
     * @return string
     */
    public function getLocationResourceId($location)
    {
        if ($location instanceof DataObject) {
            $location = $location->getResponseObject()->getHeader('Location');
        }
        if (is_array($location)) {
            $location = $location[0];
        }

        $location = rtrim($location, '/');
        $locationArr = explode('/', $location);
        return array_pop($locationArr);
    }
}
