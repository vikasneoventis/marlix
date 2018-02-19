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
 * Generate order lines for discounts
 */
class Discount extends \Klarna\Core\Model\Checkout\Orderline\Discount
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
        if ($checkout->getDiscountReference()) {
            $checkout->addOrderLine([
                'type'          => self::ITEM_TYPE_DISCOUNT,
                'reference'     => $checkout->getDiscountReference(),
                'name'          => $checkout->getDiscountTitle(),
                'quantity'      => 1,
                'unit_price'    => $checkout->getDiscountTotalAmount(),
                'discount_rate' => 0,
                'tax_rate'      => $checkout->getDiscountTaxRate()
            ]);
        }

        return $this;
    }
}
