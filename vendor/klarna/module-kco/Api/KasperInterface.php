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

interface KasperInterface
{
    /**
     * Get Klarna order details
     *
     * @param $id
     *
     * @return array
     */
    public function getOrder($id);

    /**
     * Create new order
     *
     * @param array $data
     *
     * @return array
     */
    public function createOrder($data);

    /**
     * Update Klarna order
     *
     * @param string $id
     * @param array  $data
     * @return array
     * @throws KlarnaApiException
     */
    public function updateOrder($id = null, $data);
}
