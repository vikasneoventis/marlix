<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/9/17
 * Time: 4:40 PM
 */

namespace SM\Product\Helper;


class ProductHelper {

    protected $_productAdditionAttribute;
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $configLoader;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    public function __construct(
        \Magento\Config\Model\Config\Loader $loader,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        $this->configLoader = $loader;
    }

    public function getProductAdditionAttribute() {
        if (is_null($this->_productAdditionAttribute)) {
            $configData       = $this->configLoader->getConfigByPath('xretail/' . 'pos', 'default', 0);
            $productAttribute = array_filter(
                $configData,
                function ($key) {
                    return $key === 'xretail/pos/addition_field_search';
                },
                ARRAY_FILTER_USE_KEY);

            $this->_productAdditionAttribute = count($productAttribute) > 0 && is_array(json_decode(current($productAttribute)['value'], true)) ?
                json_decode(
                    current($productAttribute)['value'],
                    true) : [];
        }

        return $this->_productAdditionAttribute;
    }


    /**
     * @return array
     */
    public function getProductAttributes() {
        $attributes     = $this->getProductModel()->getAttributes();
        $attributeArray = [];

        foreach ($attributes as $attribute) {
            $attributeArray[] = [
                'label' => $attribute->getFrontend()->getLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $attributeArray;
    }

    /**
     * @return  \Magento\Catalog\Model\Product
     */
    protected function getProductModel() {
        return $this->productFactory->create();
    }
}