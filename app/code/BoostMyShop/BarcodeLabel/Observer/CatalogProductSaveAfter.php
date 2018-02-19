<?php

namespace BoostMyShop\BarcodeLabel\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfter implements ObserverInterface
{
    protected $_assignment;
    protected $_config;

    public function __construct(
        \BoostMyShop\BarcodeLabel\Model\ConfigFactory $config,
        \BoostMyShop\BarcodeLabel\Model\Assignment $assignment
    )
    {
        $this->_config = $config;
        $this->_assignment = $assignment;
    }

    protected function getConfig()
    {
        return $this->_config->create();
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        if ($this->getConfig()->isEnabled())
        {
            $barcodeAttribute = $this->getConfig()->getBarcodeAttribute();
            $product = $observer->getEvent()->getDataObject();
            if (!$product->getData($barcodeAttribute))
                $this->_assignment->assignForOneProduct($product->getId());
        }
    }

}