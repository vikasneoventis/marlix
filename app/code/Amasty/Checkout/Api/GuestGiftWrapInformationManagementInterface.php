<?php
namespace Amasty\Checkout\Api;

interface GuestGiftWrapInformationManagementInterface
{
    /**
     * Calculate quote totals based on quote and fee
     *
     * @param string $cartId
     * @param bool $checked
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function update($cartId, $checked);
}
