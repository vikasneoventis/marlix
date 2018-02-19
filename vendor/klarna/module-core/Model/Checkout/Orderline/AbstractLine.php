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
use Klarna\Core\Api\OrderLine;
use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Model\Calculation;

/**
 * Klarna order line abstract
 */
abstract class AbstractLine implements OrderLine
{
    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * Order line code name
     *
     * @var string
     */
    protected $_code;

    /**
     * Order line is used to calculate a total
     *
     * For example, shipping total, order total, or discount total
     *
     * This should be set to false for items like order items
     *
     * @var bool
     */
    protected $_isTotalCollector = true;

    /**
     * @var BuilderInterface
     */
    protected $_object = null;

    /**
     * @var Calculation
     */
    protected $calculator;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * AbstractLine constructor.
     *
     * @param ConfigHelper         $configHelper
     * @param Calculation          $calculator
     * @param ScopeConfigInterface $config
     */
    public function __construct(ConfigHelper $configHelper, Calculation $calculator, ScopeConfigInterface $config)
    {
        $this->helper = $configHelper;
        $this->calculator = $calculator;
        $this->config = $config;
    }

    /**
     * Check if the order line is for an order item or a total collector
     *
     * @return boolean
     */
    public function isIsTotalCollector()
    {
        return $this->_isTotalCollector;
    }

    /**
     * Retrieve code name
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Set code name
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->_code = $code;

        return $this;
    }

    /**
     * Get tax amount for discount
     *
     * @param Item[] $items
     * @param array  $total
     * @param float  $taxRate
     * @return float
     */
    public function getDiscountTaxAmount($items, $total, $taxRate)
    {
        $taxAmount = 0;
        foreach ($items as $item) {
            if ($item->getBaseDiscountAmount() == 0) {
                continue;
            }
            $taxAmount += $item->getDiscountTaxCompensationAmount();
        }
        if ($taxAmount === 0) {
            if ($taxRate > 1) {
                $taxRate = $taxRate / 100;
            }
            $taxAmount = -($total->getValue() - ($total->getValue() / (1 + $taxRate)));
        }
        return $taxAmount;
    }


    /**
     * Get the tax rate for the discount order line
     *
     * @param array $checkout
     * @param Item[] $items
     *
     * @return float
     */
    public function getDiscountTaxRate($checkout, $items = [])
    {
        $discountTaxRate = false;

        if (count($items)) {
            $itemTaxRates = [];
            $totalsIncludingTax = [];
            $totalsExcludingTax = [];
            foreach ($items as $item) {
                if ($item->getBaseDiscountAmount() == 0) {
                    continue;
                }
                $totalsIncludingTax[] = $item->getRowTotalInclTax();
                $totalsExcludingTax[] = $item->getRowTotalInclTax() - $item->getTaxAmount();
                $itemTaxRates[] = $item->getTaxPercent();
            }
            $itemTaxRates = array_unique($itemTaxRates);
            $taxRateCount = count($itemTaxRates);

            if (count($items) === $taxRateCount) {
                $discountTaxRate = false; // Every item has a discount, so fall through to secondary logic
            } elseif (1 < $taxRateCount) {
                $discountTaxRate = ((array_sum($totalsIncludingTax) / array_sum($totalsExcludingTax)) - 1);
                $discountTaxRate = $this->helper->toApiFloat($discountTaxRate);
            } elseif (1 === $taxRateCount) {
                $discountTaxRate = $this->helper->toApiFloat(reset($itemTaxRates) / 100);
            }
        }

        if ($discountTaxRate === false && $checkout->getItems()) {
            $itemTaxRates = [];
            $totalsIncludingTax = [];
            $totalsExcludingTax = [];
            foreach ($checkout->getItems() as $item) {
                $totalsIncludingTax[] = $item['total_amount'];
                $totalsExcludingTax[] = $item['total_amount'] - $item['total_tax_amount'];
                $itemTaxRates[] = isset($item['tax_rate']) ? ($item['tax_rate'] * 1) : 0;
            }

            $itemTaxRates = array_unique($itemTaxRates);
            $taxRateCount = count($itemTaxRates);

            if (1 < $taxRateCount) {
                $discountTaxRate = ((array_sum($totalsIncludingTax) / array_sum($totalsExcludingTax)) - 1);
                $discountTaxRate = $this->helper->toApiFloat($discountTaxRate);
            } elseif (1 === $taxRateCount) {
                $discountTaxRate = reset($itemTaxRates);
            }
        }
        return $discountTaxRate === false ? $checkout->getDiscountTaxRate() : $discountTaxRate;
    }

    /**
     * Get object
     *
     * @return BuilderInterface
     */
    protected function _getObject()
    {
        if ($this->_object === null) {
            throw new KlarnaException(
                __('Object model is not defined.')
            );
        }

        return $this->_object;
    }

    /**
     * Set the object which can be used inside totals calculation
     *
     * @param BuilderInterface $object
     *
     * @return $this
     */
    protected function _setObject(BuilderInterface $object)
    {
        $this->_object = $object;

        return $this;
    }
}
