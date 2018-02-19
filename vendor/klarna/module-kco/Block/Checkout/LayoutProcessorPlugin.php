<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Block\Checkout;

use Klarna\Core\Helper\VersionInfo;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Message\ManagerInterface;

class LayoutProcessorPlugin
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var Kco
     */
    protected $kco;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var VersionInfo
     */
    protected $info;

    /**
     * LayoutProcessorPlugin constructor.
     *
     * @param Session          $session
     * @param ManagerInterface $manager
     * @param Kco              $kco
     * @param State            $appState
     * @param VersionInfo      $info
     */
    public function __construct(
        Session $session,
        ManagerInterface $manager,
        Kco $kco,
        State $appState,
        VersionInfo $info
    ) {
        $this->quote = $session->getQuote();
        $this->customer = $this->quote->getCustomer();
        $this->manager = $manager;
        $this->kco = $kco;
        $this->appState = $appState;
        $this->info = $info;
    }

    /**
     * Runs after the standard LayoutProcessor to conditionally enable Klarna checkout
     *
     * @param LayoutProcessor $subject
     * @param                 $result
     * @return mixed
     */
    public function afterProcess(LayoutProcessor $subject, $result)
    {
        if (isset($result['components']['checkout']['children']['steps']['children']["klarna_kco"])) {
            $iframe = $this->generateKlarnaIframe();
            $result['components']['checkout']['children']['steps']['children']['klarna_kco']['klarna_iframe'] = $iframe;
        }
        return $this->checkForEnterprise($result);
    }

    /**
     * Returns iframe snippet of checkout form
     *
     * @return string
     */
    protected function generateKlarnaIframe()
    {
        try {
            $this->kco->setQuote($this->quote);
            $this->kco->initCheckout();
        } catch (\Exception $e) {
            if ($this->appState->getMode() === State::MODE_DEVELOPER) {
                throw $e;
            }
            $this->manager->addErrorMessage($e->getMessage());
            return __(
                'Klarna Checkout has failed to load. Please <a href="javascript:;" onclick="location.reload(true)">reload checkout.</a>'
            );
        }
        return $this->kco->getApiInstance($this->quote->getStore())->getKlarnaCheckoutGui();
    }

    protected function checkForEnterprise($result)
    {
        if ($this->info->getMageEdition() !== 'Enterprise') {
            return $result;
        }
        $result['components']['checkout']['children']['sidebar']['children']['klarna_sidebar']['children']['storeCredit'] = [
            'component'   => 'Magento_CustomerBalance/js/view/payment/customer-balance',
            'displayArea' => 'klarna-summary',
            'sortOrder'   => '20',
        ];
        $result['components']['checkout']['children']['sidebar']['children']['klarna_sidebar']['children']['giftCardAccount'] = [
            'component'   => 'Magento_GiftCardAccount/js/view/payment/gift-card-account',
            'displayArea' => 'klarna-summary',
            'sortOrder'   => '30',
            'children'    => [
                'errors' => [
                    'sortOrder'   => '0',
                    'component'   => 'Magento_GiftCardAccount/js/view/payment/gift-card-messages',
                    'displayArea' => 'messages',
                ],
            ],
        ];
        $result['components']['checkout']['children']['sidebar']['children']['klarna_sidebar']['children']['reward'] = [
            'component'   => 'Magento_Reward/js/view/payment/reward',
            'displayArea' => 'klarna-summary',
            'sortOrder'   => '40',
        ];
        return $result;
    }
}
