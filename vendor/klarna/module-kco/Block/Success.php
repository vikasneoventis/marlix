<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Block;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Model\OrderRepository;
use Klarna\Kco\Helper\ApiHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Success extends \Magento\Framework\View\Element\Template
{
    /** @var ApiHelper */
    protected $helper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderRepository
     */
    protected $kcoOrderRepository;

    /**
     * Success constructor.
     *
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param ApiHelper       $apiHelper
     * @param OrderRepository $kcoOrderRepository
     * @param array           $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        ApiHelper $apiHelper,
        OrderRepository $kcoOrderRepository,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->kcoOrderRepository = $kcoOrderRepository;
        $this->helper = $apiHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     */
    protected function prepareBlockData()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $klarnaOrder = $this->kcoOrderRepository->getByOrder($order);
        if ($klarnaOrder->getId()) {
            try {
                $api = $this->helper->getApiInstance($order->getStore());
                $api->initKlarnaCheckout($klarnaOrder->getKlarnaOrderId());
                $html = $api->getKlarnaCheckoutGui();
            } catch (KlarnaException $e) {
                $html = $e->getMessage();
            }

            $this->setKlarnaSuccessHtml($html);
        }
    }
}
