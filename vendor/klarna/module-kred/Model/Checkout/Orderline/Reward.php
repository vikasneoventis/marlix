<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kred\Model\Checkout\Orderline;

use Klarna\Core\Api\BuilderInterface;

/**
 * Generate order line details for reward
 */
class Reward extends \Klarna\Core\Model\Checkout\Orderline\Reward
{
    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getRewardTotalAmount()) {
            $checkout->addOrderLine([
                'type'       => Discount::ITEM_TYPE_DISCOUNT,
                'reference'  => $checkout->getRewardReference(),
                'name'       => $checkout->getRewardTitle(),
                'quantity'   => 1,
                'unit_price' => $checkout->getRewardUnitPrice(),
                'tax_rate'   => $checkout->getRewardTaxRate(),
            ]);
        }

        return $this;
    }
}
