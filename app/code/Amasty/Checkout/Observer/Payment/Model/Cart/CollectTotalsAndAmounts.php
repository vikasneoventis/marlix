<?php
namespace Amasty\Checkout\Observer\Payment\Model\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Checkout\Model\ResourceModel\Fee\CollectionFactory as FeeCollectionFactory;

class CollectTotalsAndAmounts implements ObserverInterface
{
    /**
     * @var FeeCollectionFactory
     */
    protected $feeCollectionFactory;

    public function __construct(
        FeeCollectionFactory $feeCollectionFactory
    ) {
        $this->feeCollectionFactory = $feeCollectionFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Paypal\Model\Cart $cart */
        $cart = $observer->getCart();
        $id = $cart->getSalesModel()->getDataUsingMethod('entity_id');
        if (!$id) {
            $id = $cart->getSalesModel()->getDataUsingMethod('quote_id');
        }

        $feesCollection = $this->feeCollectionFactory->create()
            ->addFieldToFilter('quote_id', $id);

        $baseFeeAmount = 0;

        /** @var \Amasty\Checkout\Model\Fee $fee */
        foreach ($feesCollection as $fee) {
            $baseFeeAmount += $fee->getBaseAmount();
        }

        $cart->addCustomItem(
            (string)__('Gift Wrap'),
            1,
            $baseFeeAmount
        );
    }
}
