<?php
namespace Amasty\Checkout\Api;

interface GiftWrapInformationManagementInterface
{
    /**
     * Calculate quote totals based on quote and fee
     *
     * @param int $cartId
     * @param bool $checked
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function update($cartId, $checked);
}
