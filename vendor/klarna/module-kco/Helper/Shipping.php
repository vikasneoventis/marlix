<?php
/**
 * Shipping
 *
 * @copyright Copyright © 2017 Klarna Bank AB. All rights reserved.
 * @author    Joe Constant <joe.constant@klarna.com>
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Helper;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class Shipping
{
    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * Shipping constructor.
     *
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     */
    public function __construct(ShippingMethodManagementInterface $shippingMethodManagement)
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * Get default shipping method
     *
     * @param CartInterface|Quote $quote
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface|null
     */
    public function getDefaultShippingMethod(CartInterface $quote)
    {
        /** @var AddressInterface|Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface[] $rates */
        $rates = $this->shippingMethodManagement->getList($quote->getId());
        $shippingMethod = null;
        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $rate */
        foreach ($rates as $rate) {
            if (null === $shippingMethod) {
                $shippingMethod = $rate;
            }
            $method = $rate->getCarrierCode() . '_' . $rate->getMethodCode();
            if ($method === $shippingAddress->getShippingMethod()) {
                $shippingMethod = $rate;
                break;
            }
        }

        return $shippingMethod;
    }
}
