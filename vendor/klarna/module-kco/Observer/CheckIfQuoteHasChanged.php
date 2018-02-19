<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Observer;

use Klarna\Kco\Helper\Checkout;
use Klarna\Kco\Model\QuoteRepository;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class CheckIfQuoteHasChanged implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Checkout
     */
    protected $helper;

    /**
     * @var QuoteRepository
     */
    protected $quoteFactory;

    public function __construct(Session $session, Checkout $helper, QuoteRepository $quoteRepository)
    {
        $this->checkoutSession = $session;
        $this->helper = $helper;
        $this->quoteFactory = $quoteRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();

        if ($paymentMethod === 'klarna_kco' &&
            $this->checkoutSession->getCartWasUpdated() &&
            $this->helper->kcoEnabled()
        ) {
            /** @var \Klarna\Kco\Model\Quote $klarnaQuote */
            $klarnaQuote = $this->quoteFactory->getActiveByQuote($quote);
            if ($klarnaQuote->getId() && !$klarnaQuote->getIsChanged()) {
                $klarnaQuote->setIsChanged(1);
                $this->quoteFactory->save($klarnaQuote);
            }
        }
    }
}
