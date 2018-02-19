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
 * Generate shipping order line details
 */
class Shipping extends \Klarna\Core\Model\Checkout\Orderline\Shipping
{
    /**
     * Add order details to checkout request
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getShippingTotalAmount()) {
            $checkout->addOrderLine([
                'type'          => self::ITEM_TYPE_SHIPPING,
                'reference'     => $checkout->getShippingReference(),
                'name'          => $checkout->getShippingTitle(),
                'quantity'      => 1,
                'unit_price'    => $checkout->getShippingTotalAmount(),
                'discount_rate' => 0,
                'tax_rate'      => $checkout->getShippingTaxRate()
            ]);
        }

        return $this;
    }
}
