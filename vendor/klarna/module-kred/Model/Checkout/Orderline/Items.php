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
use Magento\Framework\DataObject;

/**
 * Generate order lines for order items
 */
class Items extends \Klarna\Kco\Model\Checkout\Orderline\Items
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
        if ($checkout->getItems()) {
            foreach ($checkout->getItems() as $item) {
                $item = new DataObject($item);
                $checkout->addOrderLine([
                    'reference'  => $this->convertEncoding($item->getReference()),
                    'name'       => $this->convertEncoding($item->getName()),
                    'quantity'   => (int)$item->getQuantity(),
                    'unit_price' => (int)$item->getUnitPrice(),
                    'tax_rate'   => (int)$item->getTaxRate()
                ]);
            }
        }

        return $this;
    }

    /**
     * Converts non-UTF8 characters to a question mark (?)
     *
     * @param string $data
     * @return string
     */
    public function convertEncoding($data)
    {
        if (function_exists('mb_ereg_replace')) {
            return mb_ereg_replace('[^\x0A\x20-\x7E]', '?', $data);
        }
        return preg_replace('/[^\x0A\x20-\x7E]/', '?', $data);
    }
}
