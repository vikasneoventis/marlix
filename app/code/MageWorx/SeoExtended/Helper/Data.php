<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoExtended\Helper;

use Magento\Store\Model\ScopeInterface;
use MageWorx\SeoExtended\Model\Source\AddPageNum;

/**
 * SEO Extended config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * XML config paths
     */
    const XML_PATH_USE_SEO_FILTERS                     = 'mageworx_seo/extended/seo_filters/use_seo_for_category_filters';
    const XML_PATH_USE_SEO_ON_SINGLE_FILTER            = 'mageworx_seo/extended/seo_filters/use_on_single_filter';

    const XML_PATH_ADD_PAGER_NUM_IN_TITLE              = 'mageworx_seo/extended/meta/pager_in_title';
    const XML_PATH_ADD_PAGER_NUM_IN_DESCRIPTION        = 'mageworx_seo/extended/meta/pager_in_description';
    const XML_PATH_CUT_MAGENTO_PREFIX_SUFFIX           = 'mageworx_seo/extended/meta/cut_title_prefix_suffix';
    const XML_PATH_CUT_MAGENTO_PREFIX_SUFFIX_PAGES     = 'mageworx_seo/extended/meta/cut_prefix_suffix_pages';
    const XML_PATH_USE_LAYERED_FILTERS_IN_TITLE        = 'mageworx_seo/extended/meta/layered_filters_in_title';
    const XML_PATH_USE_LAYERED_FILTERS_IN_DESCRIPTION  = 'mageworx_seo/extended/meta/layered_filters_in_description';
    /**#@- */

    /**
     * @param null|int $store
     * @return bool
     */
    public function isUseSeoForCategoryFilters($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_SEO_FILTERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isUseOnSingleFilterOnly($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_SEO_ON_SINGLE_FILTER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     *
     * @param int|null $storeId
     * @return string
     */
    public function getAddPageNumToMetaTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADD_PAGER_NUM_IN_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAddPageNumToMetaTitleDisable($storeId = null)
    {
        return AddPageNum::PAGE_NUM_NO_ADD == $this->getAddPageNumToMetaTitle($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddPageNumToBeginningMetaTitle($storeId = null)
    {
        return AddPageNum::PAGE_NUM_ADD_TO_BEINNING == $this->getAddPageNumToMetaTitle($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddPageNumToEndMetaTitle($storeId = null)
    {
        return AddPageNum::PAGE_NUM_ADD_TO_END == $this->getAddPageNumToMetaTitle($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddPageNumToMetaDescriptionDisable($storeId = null)
    {
        return AddPageNum::PAGE_NUM_NO_ADD == $this->getAddPageNumToMetaDescription($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return string
     */
    public function getAddPageNumToMetaDescription($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADD_PAGER_NUM_IN_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddPageNumToBeginningMetaDescription($storeId = null)
    {
        return AddPageNum::PAGE_NUM_ADD_TO_BEINNING == $this->getAddPageNumToMetaDescription($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddPageNumToEndMetaDescription($storeId = null)
    {
        return AddPageNum::PAGE_NUM_ADD_TO_END == $this->getAddPageNumToMetaDescription($storeId);
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isCutMagentoPrefixSuffix($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CUT_MAGENTO_PREFIX_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int|null $storeId
     * @return array
     */
    public function getPagesForCutPrefixSuffix($storeId = null)
    {
        if ($this->isCutMagentoPrefixSuffix($storeId)) {
            $pagesString = $this->scopeConfig->getValue(
                self::XML_PATH_CUT_MAGENTO_PREFIX_SUFFIX_PAGES,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $pagesArray = array_filter(preg_split('/\r?\n/', $pagesString));
            $pagesArray = array_map('trim', $pagesArray);
            return array_filter($pagesArray);
        }
        return [];
    }

    /**
     * @param string $fullActionName
     * @param int|null $storeId
     * @return boolean
     */
    public function isCutMagentoPrefixSuffixByPage($fullActionName, $storeId = null)
    {
        if ($this->isCutMagentoPrefixSuffix($storeId)) {
            return in_array($fullActionName, $this->getPagesForCutPrefixSuffix($storeId));
        }
        return false;
    }


    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddLayeredFiltersToMetaTitle($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_USE_LAYERED_FILTERS_IN_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isAddLayeredFiltersToMetaDescription($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_USE_LAYERED_FILTERS_IN_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
