<?php

namespace Amasty\Checkout\Api;

interface GiftMessageInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param mixed $giftMessage
     *
     * @return bool
     */
    public function update($cartId, $giftMessage);
}
