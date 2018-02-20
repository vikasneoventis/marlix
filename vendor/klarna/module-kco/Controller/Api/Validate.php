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

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Psr\Log\LogLevel;

class Validate extends Action
{

    public function execute()
    {
        $this->checkIsPost();

        $checkoutId = $this->getRequest()->getParam('id');

        try {
            $klarnaQuote = $this->quoteRepository->getByCheckoutId($checkoutId);
            if (!$klarnaQuote->getId() || $klarnaQuote->getIsChanged()) {
                return $this->_setValidateFailedResponse($checkoutId);
            }

            $quote = $this->mageQuoteRepository->get($klarnaQuote->getQuoteId());
            if (!$quote->getId() || !$quote->hasItems() || $quote->getHasError()) {
                return $this->_setValidateFailedResponse($checkoutId);
            }

            $body = $this->getRequest()->getContent();

            try {
                $checkout = $this->jsonHelper->jsonDecode($body);
                $checkout = new DataObject($checkout);
            } catch (\Exception $e) {
                return $this->sendBadRequestResponse($e->getMessage(), 500);
            }

            // Set address is if it's not set
            if (($quote->isVirtual() && !$quote->getShippingAddress()->validate())
                || !$quote->getBillingAddress()->validate()
            ) {
                $this->kco->setQuote($quote);
                $this->_updateOrderAddresses($checkout);
            }

            $checkoutType = $this->configHelper->getCheckoutType($quote->getStore());

            $this->_eventManager->dispatch(
                "kco_validate_before_order_place_type_{$checkoutType}",
                [
                    'quote'    => $quote,
                    'checkout' => $checkout,
                    'response' => $this->getResponse()
                ]
            );

            $this->_eventManager->dispatch(
                'kco_validate_before_order_place',
                [
                    'quote'    => $quote,
                    'checkout' => $checkout,
                    'response' => $this->getResponse()
                ]
            );

            // Reserve Order ID
            $quote->reserveOrderId();
            $this->mageQuoteRepository->save($quote);

            try {
                $this->validateOrderTotal($checkout, $quote);
            } catch (KlarnaException $e) {
                $this->messageManager->addErrorMessage(__('Order total does not match for order'));
                return $this->_setValidateFailedResponse($checkoutId, (string)__('Order total does not match for order'));
            }

            return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(200);
        } catch (KlarnaApiException $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse($e->getMessage(), 503);
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse($e->getMessage(), 500);
        }
    }

    /**
     * Set the response that validation has failed
     *
     * @param string $checkoutId
     * @return Redirect
     */
    protected function _setValidateFailedResponse($checkoutId, $message = null)
    {
        return $this->resultRedirectFactory->create()
            ->setHttpResponseCode(303)
            ->setStatusHeader(303, null, $message)
            ->setPath(
                'checkout/klarna/validateFailed',
                [
                    '_nosid'  => true,
                    '_escape' => false,
                    '_query'  => ['id' => $checkoutId, 'message' => $message]
                ]
            );
    }

    /**
     * Update addresses on an order using api data
     *
     * @param DataObject $checkout
     * @return $this
     * @throws \Exception
     */
    protected function _updateOrderAddresses($checkout)
    {
        if ($checkout->hasBillingAddress() || $checkout->hasShippingAddress()) {
            try {
                $updateErrors = [];
                $billingAddress = new DataObject($checkout->getBillingAddress());
                $shippingAddress = new DataObject($checkout->getShippingAddress());
                $sameAsOther = false;

                if ($checkout->hasBillingAddress() && $checkout->hasShippingAddress()) {
                    $sameAsOther = $checkout->getShippingAddress() == $checkout->getBillingAddress();
                }

                $quote = $this->kco->getQuote();
                if ($checkout->hasBillingAddress()) {
                    $billingAddress->setSameAsOther($sameAsOther);

                    // Update quote details
                    $this->updateCustomerOnQuote($quote, $billingAddress);

                    // Update billing address
                    try {
                        $this->_updateOrderAddress($billingAddress, Address::TYPE_BILLING);
                    } catch (KlarnaException $e) {
                        $updateErrors[] = $e->getMessage();
                    }
                }

                if ($checkout->hasShippingAddress() && !$sameAsOther) {
                    $quote->setTotalsCollectedFlag(false);

                    // Update shipping address
                    try {
                        $this->_updateOrderAddress($shippingAddress, Address::TYPE_SHIPPING);
                    } catch (KlarnaException $e) {
                        $updateErrors[] = $e->getMessage();
                    }
                }

                if (!empty($updateErrors)) {
                    $prettyErrors = implode("\n", $updateErrors);
                    $prettyJson = json_encode($checkout->toArray(), JSON_PRETTY_PRINT);

                    $this->log("$prettyErrors\n$prettyJson\n", LogLevel::ALERT);

                    return $this->sendBadAddressRequestResponse($updateErrors);
                }

                $this->kco->checkShippingMethod($quote);

                $quote->collectTotals();
            } catch (KlarnaException $e) {
                throw $e;
            } catch (\Exception $e) {
                $this->log($e, LogLevel::ERROR);
            }
        }

        return $this;
    }

    /**
     * Update quote address using address details from api call
     *
     * @param DataObject $klarnaAddressData
     * @param string     $type
     *
     * @throws KlarnaException
     */
    protected function _updateOrderAddress($klarnaAddressData, $type = Address::TYPE_BILLING)
    {
        $this->kco->updateCheckoutAddress($klarnaAddressData, $type);
    }

    /**
     * Update customer info on quote for guest users
     *
     * @param $quote
     * @param $billingAddress
     */
    protected function updateCustomerOnQuote($quote, $billingAddress)
    {
        if ($quote->getCustomerId()) {
            // Don't update if customer is logged in
            return;
        }
        $quote->setCustomerEmail($billingAddress->getEmail());
        $quote->setCustomerFirstname($billingAddress->getGivenName());
        $quote->setCustomerLastname($billingAddress->getFamilyName());
    }
}
