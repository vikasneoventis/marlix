<?php

namespace Amasty\Checkout\Api;

use Magento\Quote\Api\Data\AddressInterface;

interface GuestItemManagementInterface
{
    /**
     * @param string $cartId
     * @param int    $itemId
     *
     * @return \Amasty\Checkout\Api\Data\TotalsInterface|boolean
     */
    public function remove($cartId, $itemId, AddressInterface $address);

    /**
     * @param string $cartId
     * @param int    $itemId
     * @param string $formData
     *
     * @return \Amasty\Checkout\Api\Data\TotalsInterface|boolean
     */
    public function update($cartId, $itemId, $formData, AddressInterface $address);
}
