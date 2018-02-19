<?php

namespace Amasty\Checkout\Api;

interface GuestDeliveryInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param string $date
     * @param int    $time
     *
     * @return bool
     */
    public function update($cartId, $date, $time);
}
