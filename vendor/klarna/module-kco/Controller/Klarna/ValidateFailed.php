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
 * Validation of the order failed on order place
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class ValidateFailed extends Action
{
    public function execute()
    {
        $message = $this->getRequest()->getParam('message');
        $this->messageManager->addErrorMessage(
            __('Unable to complete order. Please try again')
        );
        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setStatusHeader(303, null, $message);
        return $redirect->setUrl($this->configHelper->getFailureUrl());
    }
}
