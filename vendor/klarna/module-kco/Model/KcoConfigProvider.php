<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Model;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Helper\Shipping;
use Klarna\Kco\Model\Payment\Kco;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;

class KcoConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var Shipping
     */
    protected $shippingHelper;

    /**
     * @param ConfigHelper $helper
     * @param UrlInterface $urlBuilder
     * @param Session      $checkoutSession
     * @param Shipping     $shippingHelper
     */
    public function __construct(
        ConfigHelper $helper,
        UrlInterface $urlBuilder,
        Session $checkoutSession,
        Shipping $shippingHelper
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->quote = $checkoutSession->getQuote();
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $store = $this->quote->getStore();
        return [
            'klarna' => [
                'saveUrl'           => $this->getUrl('checkout/klarna/saveCustomer'),
                'failureUrl'        => $this->helper->getFailureUrl($store),
                'reloadUrl'         => $this->getUrl('checkout/klarna/reloadSummary'),
                'messageId'         => 'klarna_msg',
                'frontEndAddress'   => (bool)$this->helper->getShippingCallbackSupport($store),
                'addressUrl'        => $this->getUrl('checkout/klarna/saveShippingAddress'),
                'methodUrl'         => $this->getUrl('checkout/klarna/saveShippingMethod'),
                'regionUrl'         => $this->getUrl('kco/api/retrieveAddress'),
                'countryUrl'        => $this->getUrl('kco/api/countryLookup'),
                'refreshAddressUrl' => $this->getUrl('kco/api/refreshAddresses'),
                'frontEndShipping'  => (bool)$this->helper->getShippingInIframe($store),
                'paymentMethod'     => Kco::METHOD_CODE,
                'shippingMethod'    => $this->getShippingMethod(),
            ]
        ];
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array  $params
     * @return  string
     */
    protected function getUrl($route = '', array $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    private function getShippingMethod()
    {
        try {
            if ($this->quote->getShippingAddress()->getShippingMethod()) {
                return $this->quote->getShippingAddress()->getShippingMethod();
            }
            $shippingMethod = $this->shippingHelper->getDefaultShippingMethod($this->quote);
            if (!$shippingMethod) {
                return null;
            }
            return [
                'method_code'    => $shippingMethod->getMethodCode(),
                'method_title'   => $shippingMethod->getMethodTitle(),
                'carrier_code'   => $shippingMethod->getCarrierCode(),
                'carrier_title'  => $shippingMethod->getCarrierTitle(),
                'amount'         => $shippingMethod->getAmount(),
                'available'      => $shippingMethod->getAvailable(),
                'base_amount'    => $shippingMethod->getBaseAmount(),
                'price_excl_tax' => $shippingMethod->getPriceExclTax(),
                'price_incl_tax' => $shippingMethod->getPriceInclTax(),
                'error_message'  => $shippingMethod->getErrorMessage(),
            ];
        } catch (\Magento\Framework\Exception\StateException $e) {
            // It's likely we got called on the cart page
            return null;
        }
    }
}
