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
 * Save gift message
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class SaveMessage extends Action
{
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        $this->_eventManager->dispatch(
            'kco_controller_save_giftmessage',
            [
                'request' => $this->getRequest(),
                'quote'   => $this->getQuote()
            ]
        );

        return $this->getSummaryResponse();
    }
}
