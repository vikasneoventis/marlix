<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Controller\Klarna;

/**
 * Failure action
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class Failure extends Action
{
    public function execute()
    {
        $lastQuoteId = $this->getKco()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getKco()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            return $this->resultRedirectFactory->create()->setUrl($this->configHelper->getFailureUrl());
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Klarna Checkout'));
        return $resultPage;
    }
}
