<?php

namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\DeliveryInformationManagementInterface;
use Amasty\Checkout\Api\GuestDeliveryInformationManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestDeliveryInformationManagement implements GuestDeliveryInformationManagementInterface
{
    /** @var QuoteIdMaskFactory */
    protected $quoteIdMaskFactory;
    /**
     * @var DeliveryInformationManagementInterface
     */
    protected $deliveryInformationManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        DeliveryInformationManagementInterface $deliveryInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;

        $this->deliveryInformationManagement = $deliveryInformationManagement;
    }

    public function update($cartId, $date, $time)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->deliveryInformationManagement->update(
            $quoteIdMask->getQuoteId(),
            $date, $time
        );
    }
}
