<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Observer;

use Klarna\Kco\Helper\Checkout;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Url;

class LoadKlarnaCheckout implements ObserverInterface
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Checkout
     */
    protected $helper;

    public function __construct(Manager $manager, Url $urlModel, Session $session, Checkout $helper)
    {
        $this->helper = $helper;
        $this->url = $urlModel;
        $this->manager = $manager;
        $this->checkoutSession = $session;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $overrideObject = new DataObject();
        $overrideObject->setData(
            [
                'force_disabled' => false,
                'force_enabled'  => false,
                'redirect_url'   => $this->url->getRouteUrl('checkout/klarna')
            ]
        );

        $this->manager->dispatch(
            'kco_override_load_checkout',
            [
                'override_object' => $overrideObject,
                'parent_observer' => $observer
            ]
        );

        if ($overrideObject->getForceEnabled()
            || (!$overrideObject->getForceDisabled()
                && !$this->checkoutSession
                    ->getKlarnaOverride()
                && $this->helper->kcoEnabled())
        ) {
            $observer->getControllerAction()->getResponse()
                     ->setRedirect($overrideObject->getRedirectUrl())
                     ->sendResponse();
        }
    }
}
