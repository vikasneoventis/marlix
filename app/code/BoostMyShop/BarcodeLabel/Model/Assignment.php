<?php

namespace BoostMyShop\BarcodeLabel\Model;

class Assignment
{
    protected $_config;
    protected $_productCollectionFactory;
    protected $_productAction;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\BarcodeLabel\Model\ConfigFactory $config,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ){
        $this->_config = $config;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productAction = $productAction;
    }

    /**
     * Create a barcode value for every products without barcode
     */
    public function assignForAllProducts()
    {
        $barcodeAttribute = $this->_config->create()->getBarcodeAttribute();

        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect($barcodeAttribute);
        foreach($collection as $product)
        {
            if (!$product->getData($barcodeAttribute))
                $this->assignForOneProduct($product->getId());
        }
    }

    /**
     * @param $productId
     */
    public function assignForOneProduct($productId)
    {
        $barcodeAttribute = $this->_config->create()->getBarcodeAttribute();
        $barcode = $this->generateBarcodeForProduct($productId);
        $this->_productAction->updateAttributes([$productId], [$barcodeAttribute => $barcode], 0);
    }

    public function generateBarcodeForProduct($productId)
    {
        $prefix = '99';
        $core = str_pad($productId, 10, '0', 0);
        $controlKey = $this->getControlKey($prefix.$core);
        $barcode = $prefix.$core.$controlKey;
        return $barcode;
    }

    /**
     * Return EAN13 control key
     *
     * @param $core
     * @return int
     */
    protected function getControlKey($core)
    {
        $sum = 0;

        for ($index = 0; $index < strlen($core); $index++) {
            $number = (int) $core[$index];
            if (($index % 2) != 0)
                $number *= 3;
            $sum += $number;
        }

        $resteDivision = $sum % 10;

        if ($resteDivision == 0)
            return 0;
        else
            return 10 - $resteDivision;
    }
}