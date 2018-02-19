<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionLink\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\Context;
use \MageWorx\OptionBase\Helper\Data as HelperBase;

/**
 * OptionLink Data Helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path linked product attributes by SKU
     */
    const XML_PATH_LINKED_PRODUCT_ATTRIBUTES = 'mageworx_optionlink/optionlink_main/linked_product_attributes';

    /**
     * @var HelperBase
     */
    protected $helperBase;

    /**
     * Data constructor.
     * @param HelperBase $helperBase
     * @param Context $context
     */
    public function __construct(
        HelperBase $helperBase,
        Context $context
    ) {

        $this->helperBase = $helperBase;
        parent::__construct($context);
    }

    /**
     * Retrieve comma-separated linked product attributes
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLinkedProductAttributes($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LINKED_PRODUCT_ATTRIBUTES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve list of linked product attributes
     *
     * @param int|null $storeId
     * @return array
     */
    public function getLinkedProductAttributesAsArray($storeId = null)
    {
        $linkedProductAttributes = $this->getLinkedProductAttributes($storeId);
        if (!$linkedProductAttributes) {
            return [];
        }
        $result = explode(',', $linkedProductAttributes);
        $result = $this->helperBase->prepareLinkedAttributes($result);
        return $result;
    }
}
