<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Observer;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Api\QuoteInterface;
use Klarna\Kco\Model\Payment\Kco;
use Klarna\Kco\Model\QuoteRepository as KlarnaQuoteRepository;
use Klarna\Kred\Api\PushqueueInterface;
use Klarna\Kred\Model\Api\Ordermanagement;
use Klarna\Kred\Model\PushqueueRepository;
use Klarna\Ordermanagement\Api\ApiInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;

class LogOrderPushNotification implements ObserverInterface
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var PushqueueRepository
     */
    protected $pushqueueRepository;

    /**
     * @var MageQuoteRepository
     */
    protected $mageQuoteRepository;

    /**
     * @var KlarnaQuoteRepository
     */
    protected $klarnaQuoteRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Ordermanagement
     */
    protected $om;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var QuoteInterface
     */
    protected $klarnaQuote;

    /**
     * @var PushqueueInterface
     */
    protected $pushQueue;

    /**
     * LogOrderPushNotification constructor.
     *
     * @param ConfigHelper          $configHelper
     * @param PushqueueRepository   $pushqueueRepository
     * @param MageQuoteRepository   $mageQuoteRepository
     * @param KlarnaQuoteRepository $klarnaQuoteRepository
     * @param Ordermanagement       $om
     * @param LoggerInterface       $logger
     * @param ManagerInterface      $eventManager
     */
    public function __construct(
        ConfigHelper $configHelper,
        PushqueueRepository $pushqueueRepository,
        MageQuoteRepository $mageQuoteRepository,
        KlarnaQuoteRepository $klarnaQuoteRepository,
        Ordermanagement $om,
        LoggerInterface $logger,
        ManagerInterface $eventManager
    ) {
        $this->configHelper = $configHelper;
        $this->pushqueueRepository = $pushqueueRepository;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->klarnaQuoteRepository = $klarnaQuoteRepository;
        $this->logger = $logger;
        $this->om = $om;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->klarnaQuote = $this->klarnaQuoteRepository->getByCheckoutId($observer->getKlarnaOrderId());
            if (!$this->klarnaQuote->getId()) {
                return;
            }
            $quote = $this->mageQuoteRepository->get($this->klarnaQuote->getQuoteId());
            if ($this->configHelper->getDelayedPushNotification($quote->getStore())) {
                return;
            }
            $this->pushQueue = $this->pushqueueRepository->getByCheckoutId($this->klarnaQuote->getKlarnaCheckoutId());
            if (!$this->pushQueue->getId()) {
                $this->pushQueue->setKlarnaCheckoutId($observer->getKlarnaOrderId());
            }
            $this->pushQueue->setCount((int)$this->pushQueue->getCount() + 1);
            $this->pushqueueRepository->save($this->pushQueue);

            $this->checkForRetryLimit($quote, $observer->getKlarnaOrderId());

            if ($observer->hasResponseCodeObject()) {
                $observer->getResponseCodeObject()->setResponseCode(200);
            }
        } catch (NoSuchEntityException $e) {
            // We don't really care about this exception. It just means we have no work to do
            $this->logger->debug("An error we likely don't care about happened:");
            $this->logger->error($e);
        }
    }

    protected function checkForRetryLimit($quote, $klarnaOrderId)
    {
        $cancelCountObject = new DataObject([
            'cancel_ceiling' => 2
        ]);

        $this->eventManager->dispatch('kco_add_to_push_queue', [
            'klarna_quote'        => $this->klarnaQuote,
            'quote'               => $quote,
            'push_queue'          => $this->pushQueue,
            'cancel_count_object' => $cancelCountObject
        ]);

        $cancelCeiling = $cancelCountObject->getCancelCeiling();
        if (false !== $cancelCeiling && $this->pushQueue->getCount() >= $cancelCeiling) {
            try {
                $om = $this->getOmApi($quote->getStore(), Kco::METHOD_CODE);
                $order = $om->getPlacedKlarnaOrder($klarnaOrderId);
                $klarnaId = $order->getReservation();
                if (!$klarnaId) {
                    $klarnaId = $klarnaOrderId;
                }
                $om->cancel($klarnaId);
                $this->logger->debug('Canceling order as it is over the retry limit');
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * Get api class
     *
     * @param StoreInterface $store
     * @param string         $methodCode
     * @return ApiInterface
     */
    protected function getOmApi(StoreInterface $store, $methodCode)
    {
        $this->om->resetForStore($store, $methodCode);
        return $this->om;
    }
}
