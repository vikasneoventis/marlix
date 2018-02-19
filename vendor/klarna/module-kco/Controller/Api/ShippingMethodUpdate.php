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

use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\DataObject;
use Psr\Log\LogLevel;

/**
 * API call to set shipping method on a customers quote via callback from Klarna
 *
 * @package Klarna\Kco\Controller\Api
 */
class ShippingMethodUpdate extends Action
{
    public function execute()
    {
        $this->checkIsPost();

        try {
            $klarnaOrderId = $this->getRequest()->getParam('id');
            $kcoQuote = $this->quoteRepository->getByCheckoutId($klarnaOrderId);

            if (!$kcoQuote->getId()) {
                $result = $this->resultJsonFactory->create(['error' => 'Order not found']);
                $result->setHttpResponseCode(404);
                return $result;
            }

            $quote = $this->mageQuoteRepository->get($kcoQuote->getQuoteId());

            $this->kco->setQuote($quote);
            $this->kco->setKlarnaQuote($kcoQuote);

            $body = $this->getRequest()->getContent();

            $checkout = $this->jsonHelper->jsonDecode($body);
            $checkout = new DataObject($checkout);

            if (($selectedOption = $checkout->getSelectedShippingOption()) && isset($selectedOption['id'])) {
                try {
                    $this->kco->saveShippingMethod($selectedOption['id']);
                } catch (\Exception $e) {
                    $this->kco->checkShippingMethod($quote);
                }
                $this->mageQuoteRepository->save($this->kco->getQuote());
            }

            $response = $this->kco->getApiInstance($quote->getStore())->getGeneratedUpdateRequest();

            $jsonResponse = $this->resultJsonFactory->create();
            $jsonResponse->setData($response);
            return $jsonResponse;
        } catch (KlarnaApiException $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse('Unknown error', 503);
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->sendBadRequestResponse($e->getMessage(), 500);
        }
    }
}
