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
 * Generate order line details for gift card
 */
class Giftcard extends \Klarna\Core\Model\Checkout\Orderline\Giftcard
{
    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getGiftcardaccountTotalAmount()) {
            $checkout->addOrderLine([
                'type'       => Discount::ITEM_TYPE_DISCOUNT,
                'reference'  => $checkout->getGiftcardaccountReference(),
                'name'       => $checkout->getGiftcardaccountTitle(),
                'quantity'   => 1,
                'unit_price' => $checkout->getGiftcardaccountUnitPrice(),
                'tax_rate'   => $checkout->getGiftcardaccountTaxRate(),
            ]);
        }

        return $this;
    }
}
