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
use Klarna\Kco\Model\Payment\Kco;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Generate shipping order line details
 *
 * @author  Joe Constant <joe.constant@klarna.com>
 * @author  Jason Grim <jason.grim@klarna.com>
 */
class Shipping extends AbstractLine
{

    /**
     * Checkout item types
     */
    const ITEM_TYPE_SHIPPING = 'shipping_fee';

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
        $store = null;

        if ($object instanceof Quote) {
            $store = $object->getStore();
            $totals = $object->getTotals();
            if (isset($totals['shipping'])) {
                /** @var \Magento\Quote\Model\Quote\Address $total */
                $total = $totals['shipping'];
                $address = $object->getShippingAddress();
                $amount = $address->getBaseShippingAmount();

                if ($this->helper->getSeparateTaxLine($store)) {
                    $unitPrice = $amount;
                    $taxRate = 0;
                    $taxAmount = 0;
                } else {
                    $taxRate = $this->_calculateShippingTax($object);
                    $taxAmount = $address->getShippingTaxAmount() + $address->getShippingHiddenTaxAmount();
                    $unitPrice = $address->getShippingInclTax();
                }

                $checkout->addData(
                    [
                        'shipping_unit_price'   => $this->helper->toApiFloat($unitPrice),
                        'shipping_tax_rate'     => $this->helper->toApiFloat($taxRate),
                        'shipping_total_amount' => $this->helper->toApiFloat($unitPrice),
                        'shipping_tax_amount'   => $this->helper->toApiFloat($taxAmount),
                        'shipping_title'        => (string)$total->getTitle(),
                        'shipping_reference'    => (string)$object->getShippingAddress()->getShippingMethod()

                    ]
                );
            }
        }

        if ($object instanceof Invoice || $object instanceof Creditmemo) {
            $unitPrice = $object->getBaseShippingInclTax();
            $taxRate = $this->_calculateShippingTax($object);
            $taxAmount = $object->getShippingTaxAmount() + $object->getShippingHiddenTaxAmount();

            $checkout->addData(
                [
                    'shipping_unit_price'   => $this->helper->toApiFloat($unitPrice),
                    'shipping_tax_rate'     => $this->helper->toApiFloat($taxRate),
                    'shipping_total_amount' => $this->helper->toApiFloat($unitPrice),
                    'shipping_tax_amount'   => $this->helper->toApiFloat($taxAmount),
                    'shipping_title'        => 'Shipping',
                    'shipping_reference'    => 'shipping'

                ]
            );
        }

        return $this;
    }

    /**
     * Calculate shipping tax rate for an object
     *
     * @param Mage_Sales_Model_Quote|Invoice|Creditmemo $object
     *
     * @return float
     */
    protected function _calculateShippingTax($object)
    {
        $store = $object->getStore();
        $request = $this->calculator->getRateRequest(null, null, null, $store);
        $taxRateId = $this->config->getValue(
            TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            ScopeInterface::SCOPE_STORES,
            $store
        );

        return $this->calculator->getRate($request->setProductClassId($taxRateId));
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
        if ($checkout->getShippingTotalAmount()) {
            $checkout->addOrderLine(
                [
                    'type'             => self::ITEM_TYPE_SHIPPING,
                    'reference'        => $checkout->getShippingReference(),
                    'name'             => $checkout->getShippingTitle(),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getShippingUnitPrice(),
                    'tax_rate'         => $checkout->getShippingTaxRate(),
                    'total_amount'     => $checkout->getShippingTotalAmount(),
                    'total_tax_amount' => $checkout->getShippingTaxAmount(),
                ]
            );
        }

        return $this;
    }
}
