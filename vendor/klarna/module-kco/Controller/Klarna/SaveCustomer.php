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

use Magento\Framework\DataObject;
use Psr\Log\LogLevel;

/**
 * Save customer details before address entry
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class SaveCustomer extends Action
{
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        $result = [];

        try {
            $customerDetails = new DataObject($this->getRequest()->getParams());
            $quote = $this->getQuote();

            $this->updateCustomerOnQuote($quote, $customerDetails);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
        }

        return $this->getSummaryResponse($result);
    }
}
