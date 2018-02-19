<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class CheckProductMaxInputVars implements ObserverInterface
{
    /**
     * @param BaseHelper $baseHelper
     */
    public function __construct(BaseHelper $baseHelper)
    {
        $this->baseHelper = $baseHelper;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->baseHelper->checkMaxInputVars();
    }
}
