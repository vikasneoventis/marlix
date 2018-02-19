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

use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LogLevel;

/**
 * Shipping method save action
 *
 * This method is used when backend shipping method callbacks are not supported in the Klarna market
 *
 * @package Klarna\Kco\Controller\Klarna
 */
class SaveShippingMethod extends Action
{
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParam('shipping_method');
            $result = [];

            try {
                $this->getKco()->saveShippingMethod($data);
                $this->getKco()->updateKlarnaTotals();
                $this->quoteRepository->save($this->getQuote());
            } catch (KlarnaApiException $e) {
                $this->_eventManager->dispatch(
                    'checkout_controller_onepage_save_shipping_method',
                    [
                        'request' => $this->getRequest(),
                        'quote'   => $this->getQuote()
                    ]
                );
                $this->getQuote()->collectTotals();

                $result = [
                    'error' => $e->getMessage()
                ];
            } catch (\Exception $e) {
                $this->log($e, LogLevel::ERROR);

                $result = [
                    'error' => __('Unable to select shipping method. Please try again.')
                ];
            }

            return $this->getSummaryResponse($result);
        }
    }
}
