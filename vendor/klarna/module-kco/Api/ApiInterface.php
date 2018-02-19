<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Api;

use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\DataObject;

/**
 * Klarna api integration interface
 */
interface ApiInterface
{
    /**
     * Order statuses
     */
    const ORDER_STATUS_AUTHORIZED    = 'AUTHORIZED';
    const ORDER_STATUS_PART_CAPTURED = 'PART_CAPTURED';
    const ORDER_STATUS_CAPTURED      = 'CAPTURED';
    const ORDER_STATUS_CANCELLED     = 'CANCELLED';
    const ORDER_STATUS_EXPIRED       = 'EXPIRED';
    const ORDER_STATUS_CLOSED        = 'CLOSED';

    /**
     * Create or update an order in the checkout API
     *
     * @param string     $checkoutId
     * @param bool|false $createIfNotExists
     * @param bool|false $updateItems
     *
     * @return DataObject
     */
    public function initKlarnaCheckout($checkoutId = null, $createIfNotExists = false, $updateItems = false);

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId();

    /**
     * Get Klarna checkout order details
     *
     * @return DataObject
     */
    public function getKlarnaOrder();

    /**
     * Set Klarna checkout order details
     *
     * @param DataObject $klarnaOrder
     *
     * @return $this
     */
    public function setKlarnaOrder(DataObject $klarnaOrder);

    /**
     * Get Klarna checkout html snippets
     *
     * @return string
     */
    public function getKlarnaCheckoutGui();

    /**
     * Get generated update request
     *
     * @return array
     * @throws KlarnaApiException
     */
    public function getGeneratedUpdateRequest();
}
