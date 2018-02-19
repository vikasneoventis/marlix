<?php

namespace Amasty\Checkout\Api;

interface DeliveryInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param string $date
     * @param int    $time
     *
     * @return bool
     */
    public function update($cartId, $date, $time);
}
