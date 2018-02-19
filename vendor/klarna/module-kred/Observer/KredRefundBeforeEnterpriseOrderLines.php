<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kred\Observer;

use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class KredRefundBeforeEnterpriseOrderLines implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * KredRefundBeforeEnterpriseOrderLines constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getObject();

        /** @var \Klarna\Kco\Api\ApiInterface $api */
        $api = $observer->getApi();

        try {
            $this->_refundCustomerbalance($creditmemo, $api)
                 ->_refundGiftcard($creditmemo, $api)
                 ->_refundReward($creditmemo, $api);
        } catch (KlarnaApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            throw $e;
        }
    }

    /**
     * Refund enterprise order line reward
     *
     * @param Creditmemo   $creditmemo
     * @param ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _refundReward(Creditmemo $creditmemo, $api)
    {
        if (0 <= $creditmemo->getBaseRewardCurrencyAmount()) {
            return $this;
        }
        // Round numbers to deal with floating point math issues
        $creditAmount = round($creditmemo->getBaseRewardCurrencyAmount(), 2);
        $orderAmt = round($creditmemo->getOrder()->getBaseRewardCurrencyAmount(), 2);
        if ($creditAmount != $orderAmt) {
            throw new KlarnaApiException(__('Cannot refund partial reward amount for order.'));
        }

        $api->addArtNo(1, 'reward');

        return $this;
    }

    /**
     * Refund enterprise order line giftcard
     *
     * @param Creditmemo   $creditmemo
     * @param ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _refundGiftcard(Creditmemo $creditmemo, $api)
    {
        if (0 <= $creditmemo->getBaseGiftCardsAmount()) {
            return $this;
        }
        // Round numbers to deal with floating point math issues
        $creditAmount = round($creditmemo->getBaseGiftCardsAmount(), 2);
        $orderAmt = round($creditmemo->getOrder()->getBaseGiftCardsAmount(), 2);
        if ($creditAmount != $orderAmt) {
            throw new KlarnaApiException(__('Cannot refund partial giftcard amount for order.'));
        }

        $api->addArtNo(1, 'giftcardaccount');

        return $this;
    }

    /**
     * Refund enterprise order line customer balance
     *
     * @param Creditmemo   $creditmemo
     * @param ApiInterface $api
     *
     * @return $this
     * @throws KlarnaApiException
     */
    protected function _refundCustomerbalance(Creditmemo $creditmemo, $api)
    {
        if (0 <= $creditmemo->getCustomerBalanceAmount()) {
            return $this;
        }
        // Round numbers to deal with floating point math issues
        $creditAmount = round($creditmemo->getCustomerBalanceAmount(), 2);
        $orderAmt = round($creditmemo->getOrder()->getCustomerBalanceAmount(), 2);
        if ($creditAmount != $orderAmt) {
            throw new KlarnaApiException(__('Cannot refund partial customer balance amount for order.'));
        }

        $api->addArtNo(1, 'customerbalance');

        return $this;
    }
}
