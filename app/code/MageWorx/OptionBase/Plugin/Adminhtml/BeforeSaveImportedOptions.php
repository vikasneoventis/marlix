<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Adminhtml;

use MageWorx\OptionBase\Model\Entity\Base as BaseEntityModel;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use \Magento\Framework\App\Request\Http as HttpRequest;

class BeforeSaveImportedOptions
{
    /**
     * @var BaseEntityModel
     */
    protected $baseEntityModel;

    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var HttpRequest
     */
    protected $request;

    public function __construct(
        BaseEntityModel $baseEntityModel,
        OptionBaseHelper $helper,
        HttpRequest $request
    ) {
    
        $this->baseEntityModel = $baseEntityModel;
        $this->helper = $helper;
        $this->request = $request;
    }

    public function beforeSave($object, $product)
    {
        $currentProductId = $this->helper->isEnterprise() ?
            $product->getRowId() :
            $product->getId();
            
        if (isset($this->request->getParam('product')['options'])) {
            $postOptions = $this->request->getParam('product')['options'];
            $options = $this->getImportedOptions($postOptions, $currentProductId);
        } else {
            return [$product];
        }

        $options = $this->prepareImportedOptions($options, $product);
        $options = $this->helper->convertDependentMageworxIdToRecordId($options);
        $options = $this->helper->clearMageworxId($options);

        $this->updateProductOptions($product, $options);

        return [$product];
    }

    /**
     * Retrieve only imported custom options.
     *
     * @param array $options
     * @param int $currentProductId
     * @return array
     */
    private function getImportedOptions($options, $currentProductId)
    {
        foreach ($options as $index => $option) {
            $optionProductId = isset($option['product_id']) ? $option['product_id'] : null;

            if (!$optionProductId || $optionProductId == $currentProductId) {
                unset($options[$index]);
            }
        }

        return $options;
    }

    /**
     * Preapre imported options with not existing data.
     *
     * @param array $options
     * @param int $product
     * @return array
     */
    private function prepareImportedOptions($options, $product)
    {
        foreach ($options as $index => $option) {
            $options[$index]['product_sku'] = $product->getSku();
        }

        return $options;
    }

    /**
     * Update product options to imported options.
     *
     * @param \Magento\Framework\Model\AbstractModel $product
     * @param array $options
     */
    private function updateProductOptions($product, $options)
    {
        $productOptions = $product->getOptions();
        $existingOptions = array_keys($options);

        foreach ($productOptions as $key => $option) {
            if (in_array($key, $existingOptions)) {
                $option->setData($options[$key]);
            }
        }
    }
}
