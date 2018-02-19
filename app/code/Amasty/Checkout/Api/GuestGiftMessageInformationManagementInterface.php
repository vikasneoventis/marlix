<?php

namespace Amasty\Checkout\Api;

interface GuestGiftMessageInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param mixed $giftMessage
     *
     * @return bool
     */
    public function update($cartId, $giftMessage);
}
