<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Observer;

use Klarna\Dach\Helper\ConfigHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreFillNotice implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * PreFillNotice constructor.
     *
     * @param ConfigHelper $helper
     * @param Session      $checkoutSession
     */
    public function __construct(ConfigHelper $helper, Session $checkoutSession)
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Handle if a user accepts pre-fill terms
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Klarna\Core\Api\BuilderInterface $builder */
        $builder = $observer->getBuilder();
        $create = $builder->getRequest();

        if ('accept' !== $this->checkoutSession->getKlarnaFillNoticeTerms()
            && $this->helper->isPrefillNoticeEnabled($builder->getObject()->getStore())
        ) {
            unset($create['customer']);
            unset($create['shipping_address']);
            unset($create['billing_address']);
            $observer->getBuilder()->setRequest($create);
        }
    }
}
