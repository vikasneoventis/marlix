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
 * Generate order line details for customer balance
 */
class Customerbalance extends \Klarna\Core\Model\Checkout\Orderline\Customerbalance
{
    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getCustomerbalanceTotalAmount()) {
            $checkout->addOrderLine([
                'type'       => Discount::ITEM_TYPE_DISCOUNT,
                'reference'  => $checkout->getCustomerbalanceReference(),
                'name'       => $checkout->getCustomerbalanceTitle(),
                'quantity'   => 1,
                'unit_price' => $checkout->getCustomerbalanceUnitPrice(),
                'tax_rate'   => $checkout->getCustomerbalanceTaxRate(),
            ]);
        }

        return $this;
    }
}
