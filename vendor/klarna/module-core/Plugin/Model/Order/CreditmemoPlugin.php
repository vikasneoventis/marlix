<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Plugin\Model\Order;

class CreditmemoPlugin
{

    public function afterSetInvoice(\Magento\Sales\Model\Order\Creditmemo $subject, $result)
    {
        if (!$subject->getInvoice()) {
            return;
        }
        if ($subject->getInvoice()->getId() != $subject->getInvoiceId()) {
            $subject->setInvoiceId($subject->getInvoice()->getId());
        }
    }
}
