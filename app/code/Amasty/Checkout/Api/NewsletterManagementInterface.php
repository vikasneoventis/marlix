<?php

namespace Amasty\Checkout\Api;

interface NewsletterManagementInterface
{
    /**
     * Set payment information before redirect to payment for customer.
     *
     * @param string $cartId
     * @param mixed|null $amcheckoutData
     * @return void.
     */
    public function subscribe($cartId, $amcheckoutData);
}