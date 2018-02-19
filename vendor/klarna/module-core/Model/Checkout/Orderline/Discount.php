<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Checkout\Orderline;

use Klarna\Core\Api\BuilderInterface;

/**
 * Generate order lines for discounts
 *
 * @author  Joe Constant <joe.constant@klarna.com>
 * @author  Jason Grim <jason.grim@klarna.com>
 */
class Discount extends AbstractLine
{
    /**
     * Checkout item type
     */
    const ITEM_TYPE_DISCOUNT = 'discount';

    /**
     * Collect totals process.
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     */
    public function collect(BuilderInterface $checkout)
    {
        $object = $checkout->getObject();
        $address = $object->getShippingAddress();
        if (!$address) {
            $address = $object->getBillingAddress();
        }
        $store = $object->getStore();
        if (!$store && $address->getQuote()) {
            $store = $address->getQuote()->getStore();
        }
        $totals = $address->getTotals();

        if (is_array($totals) && isset($totals['discount'])) {
            /** @var \Magento\Quote\Model\Quote\Address\Total\Discount $total */
            $total = $totals['discount'];

            $taxRate = $this->getDiscountTaxRate($checkout, $object->getAllItems());
            $taxAmount = $this->getDiscountTaxAmount($object->getAllItems(), $total, $taxRate);

            $amount = -$total->getValue();
            $taxRate = ($taxAmount / ($amount - $taxAmount)) * 100;

            if ($this->helper->getSeparateTaxLine($store) || $this->helper->getTaxBeforeDiscount($store)) {
                $unitPrice = $amount;
                $totalAmount = $amount;
                $taxRate = 0;
                $taxAmount = 0;
            } else {
                $unitPrice = $amount;
                $totalAmount = $amount;
                if ($this->helper->getPriceExcludesVat($store)) {
                    $unitPrice += $taxAmount;
                    $totalAmount += $taxAmount;
                }
            }

            $checkout->addData(
                [
                    'discount_unit_price'   => -$this->helper->toApiFloat($unitPrice),
                    'discount_tax_rate'     => $this->helper->toApiFloat($taxRate),
                    'discount_total_amount' => -$this->helper->toApiFloat($totalAmount),
                    'discount_tax_amount'   => -$this->helper->toApiFloat($taxAmount),
                    'discount_title'        => (string)$total->getTitle(),
                    'discount_reference'    => $total->getCode()

                ]
            );
        } elseif (((float)$object->getDiscountAmount()) != 0) {
            if ($object->getDiscountDescription()) {
                $discountLabel = (string)__('Discount (%1)', $object->getDiscountDescription());
            } else {
                $discountLabel = (string)__('Discount');
            }

            $taxAmount = $object->getBaseHiddenTaxAmount();
            $amount = -$object->getBaseDiscountAmount() - $taxAmount;

            if ($this->helper->getSeparateTaxLine($store)) {
                $unitPrice = $amount;
                $totalAmount = $amount;
                $taxRate = 0;
                $taxAmount = 0;
            } else {
                $taxRate = $this->getDiscountTaxRate($checkout, $object->getAllVisibleItems());
                $unitPrice = $amount + $taxAmount;
                $totalAmount = $amount + $taxAmount;
            }

            $checkout->addData(
                [
                    'discount_unit_price'   => -$this->helper->toApiFloat($unitPrice),
                    'discount_tax_rate'     => $taxRate,
                    'discount_total_amount' => -$this->helper->toApiFloat($totalAmount),
                    'discount_tax_amount'   => -$this->helper->toApiFloat($taxAmount),
                    'discount_title'        => $discountLabel,
                    'discount_reference'    => self::ITEM_TYPE_DISCOUNT

                ]
            );
        }

        return $this;
    }

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
            $checkout->addOrderLine(
                [
                    'type'             => self::ITEM_TYPE_DISCOUNT,
                    'reference'        => $checkout->getDiscountReference(),
                    'name'             => $checkout->getDiscountTitle(),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getDiscountUnitPrice(),
                    'tax_rate'         => $checkout->getDiscountTaxRate(),
                    'total_amount'     => $checkout->getDiscountTotalAmount(),
                    'total_tax_amount' => $checkout->getDiscountTaxAmount(),
                ]
            );
        }

        return $this;
    }
}
