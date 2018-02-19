<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Observer;

use Klarna\Kco\Api\QuoteRepositoryInterface;
use Klarna\Kco\Model\Quote;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreFillNoticeCheckAccept implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $kcoQuoteRepository;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * PreFillNoticeCheckAccept constructor.
     *
     * @param Session                  $checkoutSession
     * @param ActionFlag               $actionFlag
     * @param RedirectInterface        $redirect
     * @param QuoteRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        ActionFlag $actionFlag,
        RedirectInterface $redirect,
        QuoteRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->kcoQuoteRepository = $quoteRepository;
    }

    /**
     * Check if the pre-fill notice has been accepted
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Action $controller */
        $controller = $observer->getControllerAction();
        $termsParam = $controller->getRequest()->getParam('terms');

        if ($termsParam) {
            $this->checkoutSession->setKlarnaFillNoticeTerms($termsParam);
        }

        if ('accept' === $termsParam) {
            $quote = $this->checkoutSession->getQuote();
            /** @var Quote $klarnaQuote */
            $klarnaQuote = $this->kcoQuoteRepository->getActiveByQuote($quote);

            if ($klarnaQuote->getId()) {
                $klarnaQuote->setIsActive(0);
                $this->kcoQuoteRepository->save($klarnaQuote);
            }

            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), 'checkout/klarna');
        }
    }
}
