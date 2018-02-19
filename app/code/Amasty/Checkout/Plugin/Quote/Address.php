<?php

namespace Amasty\Checkout\Plugin\Quote;

class Address
{
    /**
     * @var \Amasty\Checkout\Helper\Address
     */
    protected $addressHelper;

    public function __construct(
        \Amasty\Checkout\Helper\Address $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }

    public function afterAddData(
        \Magento\Quote\Model\Quote\Address $subject,
        $result
    ) {
        $this->addressHelper->fillEmpty($subject);

        return $result;
    }
}
