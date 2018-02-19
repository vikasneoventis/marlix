<?php

namespace Amasty\Checkout\Plugin;

class CheckoutHelperPlugin
{
    /**
     * Return true to equality duplicating billing and shipping address in placed order. If they were different.
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param $result
     * @return bool
     */
    public function afterIsDisplayBillingOnPaymentMethodAvailable(\Magento\Checkout\Helper\Data $subject, $result)
    {
        return true;
    }
}
