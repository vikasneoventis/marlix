<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * XML Sitemap data helper
 *
 */
namespace MageWorx\XmlSitemap\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path show homepage optimization enabled
     */
    const XML_PATH_HOMEPAGE_OPTIMIZE           = 'mageworx_seo/xml_sitemap/homepage_optimize';

    /**
     * XML config path links enabled
     */
    const XML_PATH_SHOW_LINKS                  = 'mageworx_seo/xml_sitemap/enable_additional_links';

    /**
     * XML config path links
     */
    const XML_PATH_ADDITIONAL_LINKS            = 'mageworx_seo/xml_sitemap/additional_links';

    /**
     * XML config setting change frequency
     */
    const XML_PATH_ADDITIONAL_LINK_CHANGEFREQ  = 'mageworx_seo/xml_sitemap/additional_links_changefreq';

    /**
     * XML config setting change priority
     */
    const XML_PATH_ADDITIONAL_LINK_PRIORITY    = 'mageworx_seo/xml_sitemap/additional_links_priority';

    /**
     * XML config path trailing slash for home page URL
     */
    const XML_PATH_TRAILING_SLASH_FOR_HOME     = 'mageworx_seo/common_sitemap/trailing_slash_home_page';

    /**
     * XML config path trailing slash for URL
     */
    const XML_PATH_TRAILING_SLASH              = 'mageworx_seo/common_sitemap/trailing_slash';

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
    ) {
        parent::__construct($context);
        $this->modelDate = $modelDate;
    }

    /**
     * Check if optimization home page URL and priority
     *
     * @param int $storeId
     * @return bool
     */
    public function isOptimizeHomePage($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_OPTIMIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if show additional links
     *
     * @param int $storeId
     * @return bool
     */
    public function isShowLinks($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SHOW_LINKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve additional links
     *
     * @param int $storeId
     * @return array
     */
    public function getAdditionalLinks($storeId = null)
    {
        $linksString = $this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $linksArray = array_filter(preg_split('/\r?\n/', $linksString));
        $linksArray = array_map('trim', $linksArray);
        return array_filter($linksArray);
    }

    /**
     * Retrieve additional links as prepared array of \Magento\Framework\Object objects
     *
     * @param int $storeId
     * @return array
     */
    public function getAdditionalLinkCollection($storeId = null)
    {
        $links = [];
        foreach ($this->getAdditionalLinks($storeId) as $link) {
            $object = new \Magento\Framework\DataObject();
            $object->setUrl($link);
            $object->setUpdatedAt($this->modelDate->gmtDate('Y-m-d'));
            $links[] = $object;
        }
        return $links;
    }

    /**
     * Retrieve home page identifier
     *
     * @param int $storeId
     * @return string
     */
    public function getHomeIdentifier($storeId = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get additional link change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionalLinkChangefreq($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINK_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get additional link priority
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionalLinkPriority($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINK_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks if add or crop trailing slash for URL
     *
     * @param int $storeId
     * @return int
     */
    public function getTrailingSlash($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks if add or crop trailing slash for home page URL
     *
     * @param int $storeId
     * @return int
     */
    public function getTrailingSlashForHomePage($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH_FOR_HOME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
