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

use Psr\Log\LogLevel;

/**
 * Reload checkout details container
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class ReloadSummary extends Action
{
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        try {
            $this->kco->updateKlarnaTotals();

            return $this->getSummaryResponse();
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            return $this->getSummaryResponse([
                'error' => $e->getMessage()
            ]);
        }
    }
}
