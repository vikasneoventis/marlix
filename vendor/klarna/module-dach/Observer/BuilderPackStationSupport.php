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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BuilderPackStationSupport implements ObserverInterface
{
    protected $helper;

    /**
     * BuilderPackStationSupport constructor.
     *
     * @param ConfigHelper $helper
     */
    public function __construct(ConfigHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Klarna\Core\Api\BuilderInterface $builder */
        $builder = $observer->getBuilder();
        $create = $builder->getRequest();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $builder->getObject();
        $store = $quote->getStore();

        if ($this->helper->getPackstationEnabled($store)
            && $this->helper->getPackstationSupport($store)
        ) {
            $create['options']['packstation_enabled'] = true;
            $create['options']['allow_separate_shipping_address'] = true;
            $observer->getBuilder()->setRequest($create);
        }
    }
}
