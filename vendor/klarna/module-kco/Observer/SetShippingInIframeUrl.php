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

use Klarna\Core\Helper\ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Url;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetShippingInIframeUrl implements ObserverInterface
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * SetShippingInIframeUrl constructor.
     *
     * @param ConfigHelper          $configHelper
     * @param Url                   $url
     * @param StoreManagerInterface $store
     */
    public function __construct(ConfigHelper $configHelper, Url $url, StoreManagerInterface $store)
    {

        $this->configHelper = $configHelper;
        $this->url = $url;
        $this->store = $store->getStore();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configHelper->getShippingInIframe($this->store)) {
            return;
        }

        $urls = $observer->getUrls();
        $urlParams = $observer->getUrlParams()->toArray();

        $urls->setShippingOptionUpdate($this->url->getDirectUrl(
            'kco/api/shippingMethodUpdate/id/{checkout.order.id}',
            $urlParams
        ));
    }
}
