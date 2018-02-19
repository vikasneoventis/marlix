<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kred\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class DisablePartialPaymentsForOrdersWithEnterpriseItems implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();

        if (abs($order->getCustomerBalanceAmount())
            || abs($order->getBaseGiftCardsAmount())
            || abs($order->getBaseRewardCurrencyAmount())
        ) {
            $observer->getFlagObject()->setCanPartial(false);
        }
    }
}
