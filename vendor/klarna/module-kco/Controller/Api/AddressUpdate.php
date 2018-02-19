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
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Kco\Helper\ApiHelper;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Klarna\Kco\Model\QuoteRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * API call to update address details on a customers quote via callback from Klarna
 *
 * @package Klarna\Kco\Controller\Api
 */
class AddressUpdate extends Validate
{
    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * AddressUpdate constructor.
     *
     * @param Context             $context
     * @param LoggerInterface     $logger
     * @param PageFactory         $resultPageFactory
     * @param JsonFactory         $resultJsonFactory
     * @param JsonHelper          $jsonHelper
     * @param QuoteRepository     $quoteRepository
     * @param MageQuoteRepository $mageQuoteRepository
     * @param ConfigHelper        $configHelper
     * @param Kco                 $kco
     * @param ApiHelper           $apiHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        JsonHelper $jsonHelper,
        QuoteRepository $quoteRepository,
        MageQuoteRepository $mageQuoteRepository,
        ConfigHelper $configHelper,
        Kco $kco,
        ApiHelper $apiHelper
    ) {
        parent::__construct(
            $context,
            $logger,
            $resultPageFactory,
            $resultJsonFactory,
            $jsonHelper,
            $quoteRepository,
            $mageQuoteRepository,
            $configHelper,
            $kco
        );
        $this->apiHelper = $apiHelper;
    }

    public function execute()
    {
        $this->checkIsPost();

        try {
            $klarnaOrderId = $this->getRequest()->getParam('id');
            $kcoQuote = $this->quoteRepository->getByCheckoutId($klarnaOrderId);

            if (!$kcoQuote->getId()) {
                $redirect = $this->resultRedirectFactory->create();
                $redirect->setHeader('Content-type', 'application/json');
                $redirect->setHttpResponseCode(302);
                $redirect->setPath(
                    'checkout/klarna/validateFailed',
                    [
                        '_nosid'  => true,
                        '_escape' => false
                    ]
                );
                return $redirect;
            }

            $quote = $this->mageQuoteRepository->get($kcoQuote->getQuoteId());

            $this->kco->setQuote($quote);
            $this->kco->setKlarnaQuote($kcoQuote);

            $body = $this->getRequest()->getContent();

            $checkout = $this->jsonHelper->jsonDecode($body);
            $checkout = new DataObject($checkout);

            $this->_updateOrderAddresses($checkout);
            $this->mageQuoteRepository->save($quote);

            try {
                $response = $this->apiHelper->getApiInstance($quote->getStore())->getGeneratedUpdateRequest();
            } catch (\Exception $e) {
                $this->log($e, LogLevel::ERROR);
                return $this->sendBadRequestResponse('Unknown error');
            }

            $jsonResponse = $this->resultJsonFactory->create();
            $jsonResponse->setData($response);
            return $jsonResponse;
        } catch (KlarnaApiException $e) {
            $this->log($e, LogLevel::ERROR);
            $resultPage = $this->sendBadRequestResponse($e->getMessage(), 503);
            return $resultPage;
        } catch (KlarnaException $e) {
            $resultPage = $this->sendBadRequestResponse($e->getMessage(), 500);
            return $resultPage;
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            $resultPage = $this->sendBadRequestResponse($e->getMessage(), 500);
            return $resultPage;
        }
    }
}
