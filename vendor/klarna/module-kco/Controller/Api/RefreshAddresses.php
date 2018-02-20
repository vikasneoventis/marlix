<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Controller\Api;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;

/**
 * Lookup country id from Klarna country code
 *
 * @package Klarna\Kco\Controller\Api
 */
class RefreshAddresses extends Action
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->readOrderFromAPI();
        $jsonResponse = $this->resultJsonFactory->create();
        $jsonResponse->setData($data);
        return $jsonResponse;
    }

    protected function readOrderFromAPI()
    {
        $klarnaQuote = $this->kco->getKlarnaQuote();
        $klaraOrder = $this->kco->getApiInstance($this->kco->getQuote()->getStore())
                                ->initKlarnaCheckout($klarnaQuote->getKlarnaCheckoutId());
        $this->kco->updateCheckoutAddress(new DataObject($klaraOrder->getBillingAddress()), Address::TYPE_BILLING);
        $this->kco->updateCheckoutAddress(new DataObject($klaraOrder->getShippingAddress()), Address::TYPE_SHIPPING);
        /** @var \Magento\Quote\Model\Quote\Address $billing_address */
        $billing_address = $this->kco->getQuote()->getBillingAddress();
        /** @var \Magento\Quote\Model\Quote\Address $shipping_address */
        $shipping_address = $this->kco->getQuote()->getShippingAddress();
        $data = [
            'billing'  => [
                'email'       => $billing_address->getEmail(),
                'company'     => $billing_address->getCompany(),
                'prefix'      => $billing_address->getPrefix(),
                'firstname'   => $billing_address->getFirstname(),
                'lastname'    => $billing_address->getLastname(),
                'street'      => $billing_address->getStreet(),
                'city'        => $billing_address->getCity(),
                'region'      => $billing_address->getRegionModel($billing_address->getRegionId()),
                'region_id'   => $billing_address->getRegionId(),
                'region_code' => $billing_address->getRegionCode(),
                'postcode'    => $billing_address->getPostcode(),
                'country_id'  => $billing_address->getCountryId(),
                'telephone'   => $billing_address->getTelephone(),
            ],
            'shipping' => [
                'email'       => $shipping_address->getEmail(),
                'company'     => $shipping_address->getCompany(),
                'prefix'      => $shipping_address->getPrefix(),
                'firstname'   => $shipping_address->getFirstname(),
                'lastname'    => $shipping_address->getLastname(),
                'street'      => $shipping_address->getStreet(),
                'city'        => $shipping_address->getCity(),
                'region'      => $shipping_address->getRegionModel($shipping_address->getRegionId()),
                'region_id'   => $shipping_address->getRegionId(),
                'region_code' => $shipping_address->getRegionCode(),
                'postcode'    => $shipping_address->getPostcode(),
                'country_id'  => $shipping_address->getCountryId(),
                'telephone'   => $shipping_address->getTelephone(),
            ]
        ];
        $this->mageQuoteRepository->save($this->kco->getQuote());
        return $data;
    }
}
