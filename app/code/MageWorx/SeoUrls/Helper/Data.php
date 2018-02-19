<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoUrls\Helper;

use Magento\Store\Model\ScopeInterface;
use MageWorx\SeoUrls\Model\Source\PagerMask;

/**
 * SEO Urls data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_SEO_URL_IDENTIFIER = 'l';

    const LAYER_FILTERS_SEPARATOR    = ':';

    const CONFIG_PATH_IS_ENABLED          = 'mageworx_seo/urls/category/use_in_pager';

    const CONFIG_PATH_PAGER_URL_FORMAT    = 'mageworx_seo/urls/category/pager_url_format';

    const CONFIG_PATH_PAGER_VARIABLE_NAME = 'mageworx_seo/urls/category/pager_var_name';

    const CONFIG_PATH_ENABLED_FOR_FILTERS = 'mageworx_seo/urls/category/use_in_attribute';

    const CONFIG_PATH_SEO_IDENTIFIER      = 'mageworx_seo/urls/category/seofilters_identifier';

    const CONFIG_PATH_USE_INVERT_REDIRECT = 'mageworx_seo/urls/category/use_invert_redirect';

    /**
     * Check if enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIsSeoPagerEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSeoUrlIdentifier($storeId = null)
    {
        $value = trim($this->scopeConfig->getValue(
            self::CONFIG_PATH_SEO_IDENTIFIER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));

        if (!$value) {
            $value = self::DEFAULT_SEO_URL_IDENTIFIER;
        }

        return $value;
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getPagerUrlFormat($storeId = null)
    {
        return str_replace(
            PagerMask::PAGER_VAR_MASK,
            $this->getSeoPagerVariableName($storeId),
            $this->getRawPagerFormat($storeId)
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSeoPagerVariableName($storeId = null)
    {
        $varName = trim($this->scopeConfig->getValue(
            self::CONFIG_PATH_PAGER_VARIABLE_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));

        return $varName ? $varName : $this->getPagerVariableName();
    }

    /**
     * @return string
     */
    public function getPagerVariableName()
    {
        return \Magento\Catalog\Model\Product\ProductList\Toolbar::PAGE_PARM_NAME;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getRawPagerFormat($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PAGER_URL_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIsSeoFiltersEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ENABLED_FOR_FILTERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIsInvertRedirectEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_USE_INVERT_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getCategorySuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function getIsHideAttributes($storeId = null)
    {
        return false;
    }
}
