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
use Klarna\Kco\Helper\Checkout as CheckoutHelper;
use Magento\Checkout\Block\Onepage\Success as MageSuccess;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config;

class Success extends MageSuccess
{
    /** @var CheckoutHelper */
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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Session                                          $checkoutSession
     * @param Config                                           $orderConfig
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param ApiHelper                                        $apiHelper
     * @param OrderRepository                                  $kcoOrderRepository
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        ApiHelper $apiHelper,
        OrderRepository $kcoOrderRepository,
        array $data
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->checkoutSession = $checkoutSession;
        $this->kcoOrderRepository = $kcoOrderRepository;
        $this->helper = $apiHelper;
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     */
    protected function prepareBlockData()
    {
        parent::prepareBlockData();
        $order = $this->_checkoutSession->getLastRealOrder();
        $this->addData([
            'order' => $order
        ]);

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
