<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Traits;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Controller\Klarna\Confirmation;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Webapi\Exception as WebException;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait CommonController
{
    /**
     * JSON Helper
     *
     * @var JsonHelper
     */
    public $jsonHelper;

    /**
     * Config Helper
     *
     * @var ConfigHelper
     */
    public $configHelper;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Send bad address validation response message
     *
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function sendBadAddressRequestResponse($message = null)
    {
        $resultPage = $this->sendBadRequestResponse($message, 400);
        $resultPage->setData(
            [
                'error_type' => 'address_update',
                'error_text' => $message
            ]
        );
        return $resultPage;
    }

    /**
     * Send bad request response header
     *
     * @param array|string $message
     * @param int          $responseCode
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function sendBadRequestResponse($message = null, $responseCode = 400)
    {
        if ($message === null) {
            $message = __('Bad request');
        }

        if (is_array($message)) {
            $message = implode('\n', $message);
        }

        $resultPage = $this->resultJsonFactory->create();
        $resultPage->setHttpResponseCode($responseCode);
        $resultPage->setData(
            [
                'error_type' => $message
            ]
        );
        return $resultPage;
    }

    public function checkIsPost()
    {
        if (!$this->getRequest()->isPost()) {
            throw new WebException(
                __('Nope'),
                WebException::HTTP_METHOD_NOT_ALLOWED,
                WebException::HTTP_METHOD_NOT_ALLOWED
            );
        }
    }

    /**
     * Wrapper around logger
     *
     * @param string $message
     * @param string $level
     * @return null
     */
    public function log($message, $level = LogLevel::INFO, $context = [])
    {
        return $this->logger->log($level, $message, $context);
    }

    /**
     * Verify order totals match with Klarna and Magento
     *
     * @param DataObject    $checkout
     * @param CartInterface $quote
     *
     * @return Confirmation
     *
     * @throws KlarnaException
     */
    public function validateOrderTotal(DataObject $checkout, CartInterface $quote)
    {
        $klarnaTotal = (int)($checkout->getOrderAmount() ?: $checkout->getData('cart/total_price_including_tax'));
        $quoteTotal = (int)$this->configHelper->toApiFloat($quote->getGrandTotal());

        $this->_eventManager->dispatch(
            'kco_confirmation_order_total_validation',
            [
                'checkout' => $checkout,
                'quote'    => $quote
            ]
        );

        if ($klarnaTotal !== $quoteTotal) {
            $exceptionMessage =
                __(
                    'Order total does not match for order #%1. Klarna total is %2 vs Magento total %3',
                    $quote->getReservedOrderId(),
                    $klarnaTotal,
                    $quoteTotal
                );
            throw new KlarnaException($exceptionMessage);
        }

        return $this;
    }
}
