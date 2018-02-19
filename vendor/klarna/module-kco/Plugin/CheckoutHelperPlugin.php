<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Plugin;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kco\Helper\Checkout;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;

class CheckoutHelperPlugin
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Checkout
     */
    protected $checkoutHelper;

    /**
     * CheckoutHelperPlugin constructor.
     *
     * @param Session         $customerSession
     * @param ConfigHelper    $configHelper
     * @param CheckoutSession $checkoutSession
     * @param Checkout        $checkoutHelper
     */
    public function __construct(
        Session $customerSession,
        ConfigHelper $configHelper,
        CheckoutSession $checkoutSession,
        Checkout $checkoutHelper
    ) {
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    public function afterIsAllowedGuestCheckout(Data $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();

        // if kco not enabled, return $result
        if (!$this->checkoutHelper->kcoEnabled($quote->getStore())) {
            return $result;
        }

        if ($this->customerSession->isLoggedIn()) {
            return true;
        }

        return $this->configHelper->isAllowedGuestCheckout($quote);
    }
}
